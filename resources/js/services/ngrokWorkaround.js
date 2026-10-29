/**
 * ==========================================================================
 *  WORKAROUND SEMENTARA UNTUK NGROK (akun gratis)
 * ==========================================================================
 *
 * Masalah: ngrok gratis menyisipkan halaman peringatan "You are about to visit..."
 * untuk permintaan yang dibuka langsung oleh browser/WebView. Header
 * `ngrok-skip-browser-warning` (lihat api.js) hanya terkirim lewat axios, tidak lewat:
 *   - <img src="...">                  (thumbnail template)
 *   - window.open(urlPdf)              (buka / unduh PDF)
 *   - pdfjsLib.getDocument({ url })    (pratinjau halaman PDF)
 *
 * Solusi: saat alamat server mengandung "ngrok", file diambil lewat axios sebagai
 * blob (header ikut terkirim), lalu ditampilkan dari blob tersebut.
 * Di server selain ngrok (hosting berbayar), SEMUA fungsi di sini otomatis
 * berperilaku seperti kode biasa (<img src> dan window.open langsung).
 *
 * CARA MENGHAPUS SAAT SUDAH TIDAK PAKAI NGROK:
 *   1. Hapus file ini.
 *   2. TemplateThumb.vue   -> pakai rawSrc langsung sebagai :src
 *   3. ReportPreview.vue   -> getDocument({ url, withCredentials: false }),
 *                             openPdf/downloadPdf kembali ke window.open(url)
 *   4. ReportHistory.vue   -> openPdf kembali ke window.open(url)
 *   5. api.js              -> hapus header 'ngrok-skip-browser-warning'
 * ==========================================================================
 */
import { Capacitor } from '@capacitor/core';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { Share } from '@capacitor/share';
import api from './api.js';
import { blobToBase64 } from './reportShare.js';

/** True jika alamat API yang sedang dipakai adalah domain ngrok. Dicek setiap dipanggil
 *  karena alamat server bisa diganti dari menu Pengaturan tanpa build ulang. */
export const isNgrokActive = () => {
  try {
    const abs = new URL(api.defaults.baseURL || '/api', window.location.origin);
    return /ngrok/i.test(abs.hostname);
  } catch {
    return false;
  }
};

/* -------------------------------------------------------------------------- */
/*  Gambar (thumbnail template)                                               */
/* -------------------------------------------------------------------------- */

// Cache promise per URL supaya gambar yang sama tidak diunduh berulang
// (daftar template dan halaman pilih template memakai gambar yang sama).
const imageCache = new Map();

/**
 * Mengembalikan nilai untuk atribut <img src>.
 * Bukan ngrok  -> URL aslinya (tanpa permintaan tambahan).
 * Ngrok        -> blob: URL hasil unduhan lewat axios.
 */
export const loadImageSrc = async (url) => {
  if (!url || !isNgrokActive()) return url;

  if (!imageCache.has(url)) {
    const job = api
      .get(url, { baseURL: '', responseType: 'blob' })
      .then((res) => {
        if (!res.data?.type?.startsWith('image/')) {
          throw new Error('Respons bukan gambar');
        }
        return URL.createObjectURL(res.data);
      })
      .catch((err) => {
        imageCache.delete(url); // jangan simpan kegagalan, boleh dicoba lagi
        throw err;
      });
    imageCache.set(url, job);
  }
  return imageCache.get(url);
};

/* -------------------------------------------------------------------------- */
/*  PDF                                                                       */
/* -------------------------------------------------------------------------- */

/** Sumber untuk pdfjsLib.getDocument(). */
export const getPdfJsSource = async (url) => {
  if (!isNgrokActive()) return { url, withCredentials: false };
  const res = await api.get(url, { baseURL: '', responseType: 'arraybuffer' });
  return { data: new Uint8Array(res.data) };
};

const pdfFileName = (reportNumber) => {
  const clean = String(reportNumber || 'BPSDM').replace(/[^A-Za-z0-9_\-]/g, '_');
  return `Laporan_${clean}.pdf`;
};

const fetchPdfBlob = async (reportId, params) => {
  try {
    const res = await api.get(`/reports/${reportId}/pdf`, { params, responseType: 'blob' });
    if (!res.data || res.data.size === 0) throw new Error('File PDF kosong.');
    return res.data;
  } catch (err) {
    // Saat responseType = blob, pesan error JSON dari Laravel juga berbentuk blob.
    const body = err?.response?.data;
    if (body instanceof Blob) {
      let message = '';
      try {
        message = JSON.parse(await body.text())?.message || '';
      } catch {
        // bukan JSON, abaikan
      }
      if (message) throw new Error(message);
    }
    throw err;
  }
};

/** Android/iOS: WebView tidak bisa membuka blob, jadi simpan ke cache lalu buka lembar Bagikan. */
const shareBlobNative = async (blob, fileName, dialogTitle) => {
  await Filesystem.writeFile({
    path: fileName,
    data: await blobToBase64(blob),
    directory: Directory.Cache,
  });
  const { uri } = await Filesystem.getUri({ path: fileName, directory: Directory.Cache });
  await Share.share({ title: fileName, files: [uri], dialogTitle });
};

const isCancelled = (err) => {
  const msg = (err?.message || '').toLowerCase();
  return err?.name === 'AbortError' || msg.includes('cancel') || msg.includes('dismiss');
};

/**
 * Buka PDF.
 * Bukan ngrok -> window.open(plainUrl) seperti biasa.
 * Ngrok       -> unduh lewat axios, lalu tampilkan di tab baru (web) atau lembar Bagikan (native).
 * Mengembalikan { success, error? } agar pemanggil bisa menampilkan pesan.
 */
export const openReportPdf = async (reportId, reportNumber, plainUrl) => {
  if (!isNgrokActive()) {
    window.open(plainUrl, '_blank');
    return { success: true };
  }

  const fileName = pdfFileName(reportNumber);
  const native = Capacitor.isNativePlatform();

  // Buka tab kosong SEKARANG (masih dalam aksi klik) agar tidak diblokir popup blocker,
  // lalu isi setelah PDF selesai diunduh.
  const tab = native ? null : window.open('', '_blank');

  try {
    const blob = await fetchPdfBlob(reportId, { inline: 1 });
    if (native) {
      await shareBlobNative(blob, fileName, 'Buka Laporan PDF');
    } else {
      const blobUrl = URL.createObjectURL(blob);
      if (tab) tab.location.href = blobUrl;
      else window.open(blobUrl, '_blank');
      setTimeout(() => URL.revokeObjectURL(blobUrl), 60000);
    }
    return { success: true };
  } catch (err) {
    if (tab) tab.close();
    if (isCancelled(err)) return { success: false, cancelled: true };
    return { success: false, error: err?.message || 'Gagal membuka PDF.' };
  }
};

/**
 * Unduh PDF.
 * Bukan ngrok -> window.open(plainUrl) seperti biasa (server mengirim Content-Disposition: attachment).
 * Ngrok       -> unduh lewat axios, lalu simpan sebagai file (web) atau lembar Bagikan (native).
 */
export const downloadReportPdf = async (reportId, reportNumber, plainUrl) => {
  if (!isNgrokActive()) {
    window.open(plainUrl, '_blank');
    return { success: true };
  }

  const fileName = pdfFileName(reportNumber);

  try {
    const blob = await fetchPdfBlob(reportId);
    if (Capacitor.isNativePlatform()) {
      await shareBlobNative(blob, fileName, 'Simpan / Bagikan Laporan PDF');
    } else {
      const blobUrl = URL.createObjectURL(blob);
      const a = document.createElement('a');
      a.href = blobUrl;
      a.download = fileName;
      document.body.appendChild(a);
      a.click();
      document.body.removeChild(a);
      setTimeout(() => URL.revokeObjectURL(blobUrl), 2000);
    }
    return { success: true };
  } catch (err) {
    if (isCancelled(err)) return { success: false, cancelled: true };
    return { success: false, error: err?.message || 'Gagal mengunduh PDF.' };
  }
};
