import { Capacitor } from '@capacitor/core';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { Share } from '@capacitor/share';
import api from './api.js';

/**
 * Convert Blob to Base64 string for Capacitor Filesystem
 */
export function blobToBase64(blob) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = reject;
    reader.onload = () => {
      const dataUrl = reader.result;
      const base64 = dataUrl.split(',')[1];
      resolve(base64);
    };
    reader.readAsDataURL(blob);
  });
}

/**
 * URL PDF yang bisa dibuka oleh PENERIMA pesan.
 * Hanya dikembalikan jika alamat server bersifat publik. Path relatif ("/api/..."), localhost,
 * 10.0.2.2 (emulator Android) dan IP jaringan lokal TIDAK bisa dibuka dari perangkat lain,
 * jadi untuk kasus itu dikembalikan null (jangan kirim tautan yang pasti rusak).
 */
export function getPublicPdfUrl(report) {
  try {
    const abs = new URL(`${api.defaults.baseURL}/reports/${report.id}/pdf`, window.location.origin);
    const h = abs.hostname;
    const isPrivateHost =
      h === 'localhost' || h === '127.0.0.1' || h === '10.0.2.2' || h === '[::1]' ||
      /^10\./.test(h) || /^192\.168\./.test(h) || /^172\.(1[6-9]|2\d|3[01])\./.test(h) ||
      h.endsWith('.local') || h.endsWith('.test');
    if (isPrivateHost || !/^https?:$/.test(abs.protocol)) return null;
    return abs.href;
  } catch {
    return null;
  }
}

/**
 * Generate formatted WhatsApp message text.
 * Sengaja tanpa emoji 4-byte (U+1F4CB dst.): sebagian perangkat/aplikasi menampilkannya sebagai "�".
 */
export function formatWhatsAppMessage(report) {
  const locations = report.report_locations || [];
  const attentionLocations = locations.filter(l => l.condition === 'needs_attention');
  const cleanCount = locations.filter(l => l.condition === 'clean').length;
  const totalCount = locations.length || 5;

  let msg = `*LAPORAN PENGAWASAN LINGKUNGAN KANTOR BPSDM*\n`;
  msg += `----------------------------------\n`;
  msg += `*No. Laporan:* ${report.report_number}\n`;
  msg += `*Tanggal:* ${report.formatted_date || report.inspection_date}\n`;
  msg += `*Petugas Pengawas:* ${report.officer_name}\n`;
  msg += `*Ringkasan:* `;

  if (attentionLocations.length === 0) {
    msg += `Bersih & Rapi (${cleanCount}/${totalCount} titik)\n`;
    msg += `Semua titik pemantauan dalam kondisi bersih, aman, dan rapi.\n`;
  } else {
    msg += `${attentionLocations.length} titik perlu penanganan\n\n`;
    msg += `*Rincian Temuan & Solusi:*\n`;
    attentionLocations.forEach((loc, idx) => {
      const locName = loc.display_name || loc.name || loc.location?.name || `Lokasi ${idx + 1}`;
      msg += `${idx + 1}. *${locName}*\n`;
      if (loc.problem) msg += `   - *Masalah:* ${loc.problem}\n`;
      if (loc.solution) msg += `   - *Solusi:* ${loc.solution}\n`;
    });
  }

  const pdfUrl = getPublicPdfUrl(report);
  msg += `\n*Dokumen Laporan (PDF 16:9):*\n`;
  msg += pdfUrl ? `${pdfUrl}\n` : `File PDF dikirim terpisah sebagai lampiran.\n`;
  msg += `----------------------------------\n`;
  msg += `_Sistem Pelaporan Pengawasan Lingkungan BPSDM_`;

  return msg;
}

/**
 * Share PDF file natively via Capacitor Share or Web Share API
 */
function isCancellation(err) {
  const msg = (err?.message || '').toLowerCase();
  return (
    err?.name === 'AbortError' ||
    msg.includes('cancel') ||
    msg.includes('dismiss') ||
    // Capacitor Share plugin on Android returns this when the user backs out
    // of the native chooser sheet without picking a target app.
    msg.includes('share canceled') ||
    msg.includes('share_canceled')
  );
}

export async function sharePdfDocument(report, opts = {}) {
  const fileName = 'laporan ring lingkungan BPSDM.pdf';
  const title = `Laporan ${report.report_number}`;
  const text = opts.text || `${title} • ${report.formatted_date || report.inspection_date} • Petugas: ${report.officer_name}`;

  // 1. Native Android / iOS via Capacitor
  if (Capacitor.isNativePlatform()) {
    let fileWriteFailed = false;
    try {
      // Fetch binary PDF blob
      const res = await api.get(`/reports/${report.id}/pdf`, { responseType: 'blob' });
      if (!res.data || res.data.size === 0) {
        return { success: false, method: 'native-file', error: 'File PDF kosong, tidak bisa dibagikan.' };
      }
      const base64Data = await blobToBase64(res.data);

      // Save to temporary cache directory
      await Filesystem.writeFile({
        path: fileName,
        data: base64Data,
        directory: Directory.Cache,
      });

      // Retrieve URI for sharing
      const fileResult = await Filesystem.getUri({
        path: fileName,
        directory: Directory.Cache,
      });

      // Share via native intent sheet (WhatsApp, Gmail, Telegram, Bluetooth, etc.)
      await Share.share({
        title,
        text,
        files: [fileResult.uri],
        dialogTitle: 'Bagikan Laporan (PDF 16:9)',
      });
      // Share.share() resolving means the user picked a target app and the OS
      // handed the file off to it — this is the real success signal.
      return { success: true, method: 'native-file' };
    } catch (err) {
      if (isCancellation(err)) {
        // User backing out of the share sheet is NOT a successful share.
        return { success: false, cancelled: true, method: 'cancelled' };
      }
      fileWriteFailed = true;
      // Fall through to URL-based fallback below, in its own try/catch.
    }

    if (fileWriteFailed) {
      try {
        await Share.share({
          title,
          text,
          url: pdfUrl,
          dialogTitle: 'Bagikan Tautan Laporan',
        });
        return { success: true, method: 'native-url' };
      } catch (err2) {
        if (isCancellation(err2)) {
          return { success: false, cancelled: true, method: 'cancelled' };
        }
        return { success: false, method: 'native-url', error: err2?.message || 'Gagal membagikan file.' };
      }
    }
  }

  // 2. Web Browser environment
  try {
    const res = await api.get(`/reports/${report.id}/pdf`, { responseType: 'blob' });
    if (!res.data || res.data.size === 0) {
      return { success: false, method: 'web-file', error: 'File PDF kosong, tidak bisa dibagikan.' };
    }
    const file = new File([res.data], fileName, { type: 'application/pdf' });

    // Check if Web Share API supports file sharing
    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      await navigator.share({
        title,
        text,
        files: [file],
      });
      return { success: true, method: 'web-file' };
    } else if (navigator.share) {
      // Fallback to text & URL share
      await navigator.share({
        title,
        text,
        url: pdfUrl,
      });
      return { success: true, method: 'web-url' };
    }

    // No Web Share API available at all: open the PDF in a new tab so the
    // user can save/share it manually. This is a real, visible action, so
    // it is reported as a distinct method rather than a generic "success".
    window.open(pdfUrl, '_blank');
    return { success: true, method: 'web-open' };
  } catch (err) {
    if (isCancellation(err)) {
      return { success: false, cancelled: true, method: 'cancelled' };
    }
    // A genuine failure (network error, share API rejection, etc.) must be
    // surfaced as a failure — we no longer silently swallow it as success.
    return { success: false, method: 'web-share-failed', error: err?.message || 'Gagal membagikan file.' };
  }
}

/**
 * Open WhatsApp with prefilled formatted message.
 * Mengembalikan { linkIncluded }: false bila tautan PDF tidak disertakan karena server bukan alamat publik
 * (pemanggil sebaiknya memberi tahu pengguna agar mengirim file lewat tombol Bagikan).
 */
export function openWhatsApp(report) {
  const text = formatWhatsAppMessage(report);
  const waUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
  window.open(waUrl, '_blank');
  return { linkIncluded: getPublicPdfUrl(report) !== null };
}
