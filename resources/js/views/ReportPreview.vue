<template>
  <div class="px-4 py-4 space-y-4 pb-28">

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-4">
      <div class="bg-[#F5F7FA] rounded-2xl p-5 border border-[#D7DBE2] animate-pulse space-y-3">
        <div class="h-5 bg-[#DDE0E7] rounded w-1/3"/>
        <div class="h-3 bg-[#DDE0E7] rounded w-2/3"/>
        <div class="aspect-video bg-[#DDE0E7] rounded-xl w-full"/>
      </div>
      <div class="bg-[#F5F7FA] rounded-2xl p-4 border border-[#D7DBE2] animate-pulse space-y-3">
        <div class="h-4 bg-[#DDE0E7] rounded w-1/4"/>
        <div class="h-12 bg-[#DDE0E7] rounded-xl"/>
        <div class="h-12 bg-[#DDE0E7] rounded-xl"/>
      </div>
    </div>

    <!-- ── ERROR STATE ── -->
    <div v-else-if="errorMessage" class="bg-[#FDF0EE] border border-[#F5C7C1] rounded-2xl p-6 text-center space-y-3">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-[#FBF1EF] border border-[#F3D5CF] text-[#A6382C] flex items-center justify-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <p class="text-sm font-bold text-[#14213D]">{{ errorMessage }}</p>
      <router-link to="/history"
        class="inline-flex items-center px-4 py-2 rounded-xl bg-[#14213D] text-[#F5F7FA] text-xs font-semibold active-press transition">
        ← Kembali ke Riwayat
      </router-link>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div v-else-if="report" class="space-y-4">

      <!-- ── HEADER METADATA ── -->
      <div class="bg-[#F5F7FA] rounded-2xl px-4 py-3.5 border border-[#D7DBE2] shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="font-mono text-xs font-bold text-[#14213D] bg-[#DEE2EB] px-2.5 py-1 rounded-md border border-[#CBD1DB] tabular-nums">
            {{ report.report_number }}
          </span>
          <span
            :class="[
              'text-[10px] font-bold px-2.5 py-0.5 rounded-full border',
              report.status === 'completed'
                ? 'bg-[#EAF3EC] text-[#1B2C4D] border-[#C8E0D0]'
                : 'bg-[#FAF2DF] text-[#8C6D1F] border-[#EADBBD]'
            ]"
          >
            {{ report.status === 'completed' ? 'Tersimpan Resmi' : 'Draft' }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
          <div>
            <span class="text-[#788191] text-[10px] uppercase font-bold tracking-wider block">Tanggal Pemeriksaan</span>
            <span class="font-semibold text-[#14213D] font-mono tabular-nums">{{ report.formatted_date || report.inspection_date }}</span>
          </div>
          <div>
            <span class="text-[#788191] text-[10px] uppercase font-bold tracking-wider block">Petugas Pengawas</span>
            <span class="font-semibold text-[#14213D]">{{ report.officer_name }}</span>
          </div>
          <div class="col-span-2 pt-1 border-t border-[#DEE2EB] flex items-center justify-between">
            <span class="text-[#788191] text-[10px] uppercase font-bold tracking-wider">Format Dokumen</span>
            <span class="font-semibold text-[#14213D] text-xs bg-[#DEE2EB] px-2 py-0.5 rounded border border-[#CBD1DB]">
              {{ report.template?.name || 'Standar BPSDM' }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── SLIDE VIEWER (16:9 CAROUSEL) ── -->
      <div class="bg-[#F5F7FA] rounded-2xl p-3 border border-[#D7DBE2] shadow-sm space-y-3">

        <!-- Top navigator -->
        <div class="flex items-center justify-between px-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#C8B88A] animate-pulse"/>
            <h3 class="text-xs font-bold text-[#14213D] uppercase tracking-wider font-mono">
              Slide {{ activeSlideIndex + 1 }}/{{ totalSlides }}
            </h3>
            <span class="text-[10px] text-[#676F7F] font-medium">• {{ activeSlideTitle }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <button type="button" @click="prevSlide" :disabled="activeSlideIndex === 0"
              class="w-8 h-8 rounded-xl bg-white border border-[#CBD1DB] text-[#14213D] hover:bg-[#E9ECF2] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active-press">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button type="button" @click="nextSlide" :disabled="activeSlideIndex === totalSlides - 1"
              class="w-8 h-8 rounded-xl bg-white border border-[#CBD1DB] text-[#14213D] hover:bg-[#E9ECF2] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active-press">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 16:9 Slide Canvas — desain template yang SAMA dengan PDF (touch swipe didukung) -->
        <div
          class="relative w-full rounded-xl overflow-hidden border border-slate-200 shadow-inner select-none touch-pan-y"
          @touchstart="onTouchStart"
          @touchend="onTouchEnd"
        >
          <Transition name="slide-fade" mode="out-in">
            <img
              v-if="pageImages[activeSlideIndex]"
              :key="activeSlideIndex"
              :src="pageImages[activeSlideIndex]"
              :alt="'Halaman ' + (activeSlideIndex + 1)"
              draggable="false"
              class="w-full block bg-white"
              style="aspect-ratio: 16 / 9; object-fit: contain;"
            />
            <div v-else :key="'ph'" class="w-full aspect-video bg-[#DEE2EB] flex items-center justify-center p-4 text-center">
              <div v-if="pagesError" class="space-y-2">
                <p class="text-xs font-semibold text-[#A6382C]">{{ pagesError }}</p>
                <button type="button" @click="renderPdfPages"
                  class="px-3 py-1.5 rounded-lg bg-[#14213D] text-[#F5F7FA] text-[11px] font-bold">Coba lagi</button>
              </div>
              <p v-else class="text-xs font-semibold text-[#676F7F] animate-pulse">
                Menyiapkan pratinjau dari template… (bisa sampai 40 detik)
              </p>
            </div>
          </Transition>
        </div>

        <!-- Dot navigation -->
        <div class="flex items-center justify-center gap-1.5 pt-1">
          <button v-for="(_, idx) in totalSlides" :key="idx" type="button"
            @click="activeSlideIndex = idx"
            class="h-1.5 rounded-full transition-all duration-300"
            :class="activeSlideIndex === idx ? 'w-6 bg-[#14213D]' : 'w-2 bg-[#C9CED7] hover:bg-[#A1A7B2]'"/>
        </div>

        <!-- Swipe hint (only show on mobile) -->
        <p class="text-center text-[10px] text-[#788191] font-medium">
          ← Geser layar ke kiri atau kanan untuk berpindah slide →
        </p>
      </div>

      <!-- ── DOKUMEN STATUS CARD ── -->
      <div class="bg-[#F5F7FA] rounded-2xl p-4 border border-[#D7DBE2] shadow-sm space-y-3">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#14213D]">Status Berkas Dokumen</h4>
          <span class="text-[10px] text-[#788191] font-mono">Format 16:9 HD</span>
        </div>

        <div class="space-y-2">
          <!-- PDF status -->
          <div class="flex items-center justify-between p-3 rounded-xl border"
            :class="hasPdf ? 'bg-white border-[#CBD1DB]' : 'bg-[#E9ECF2] border-[#D1D6DF]'">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                :class="hasPdf ? 'bg-[#14213D] text-[#F5A524]' : 'bg-[#C9CED7] text-[#788191]'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-[#14213D]">Dokumen PDF Resmi</p>
                <p class="text-[10px]" :class="hasPdf ? 'text-[#1B2C4D] font-medium' : 'text-[#788191]'">
                  {{ hasPdf ? 'Siap diunduh / dibagikan' : 'Belum digenerate' }}
                </p>
              </div>
            </div>
            <span v-if="hasPdf" class="text-[10px] text-[#1B2C4D] font-bold bg-[#EAF3EC] px-2 py-0.5 rounded-full border border-[#C8E0D0] flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              Tersedia
            </span>
          </div>

        <!-- Generate PDF button (jika belum ada) -->
        <button v-if="!hasPdf" type="button" @click="doGeneratePdf" :disabled="generatingPdf"
          class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-[#14213D] text-[#F5F7FA] font-bold text-sm shadow-sm hover:bg-[#14213D] active-press transition disabled:opacity-60">
          <svg v-if="generatingPdf" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <svg v-else class="w-5 h-5 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          {{ generatingPdf ? 'Memproses Berkas PDF 16:9…' : 'Generate Dokumen PDF 16:9' }}
        </button>

        <!-- PDF action buttons (jika sudah ada) -->
        <div v-else class="grid grid-cols-2 gap-2">
          <button type="button" @click="openPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-[#14213D] text-[#F5F7FA] font-bold text-xs shadow-sm hover:bg-[#14213D] active-press transition">
            <svg class="w-4 h-4 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Buka PDF
          </button>
          <button type="button" @click="downloadPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-white border border-[#CBD1DB] text-[#14213D] font-bold text-xs hover:bg-[#EEF0F4] active-press transition">
            <svg class="w-4 h-4 text-[#14213D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Unduh PDF
          </button>
        </div>

        <!-- Share (Unified Native Sheet) -->
        <div class="pt-2 border-t border-[#DEE2EB]">
          <button type="button" @click="shareReport" :disabled="!hasPdf || sharing"
            class="w-full flex items-center justify-center gap-2 py-3 rounded-xl bg-[#1B2C4D] text-[#F5F7FA] font-bold text-xs hover:bg-[#14213D] active-press transition disabled:opacity-40 shadow-sm">
            <svg v-if="sharing" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <svg v-else class="w-4 h-4 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <span>{{ sharing ? 'Menyiapkan File…' : 'Bagikan' }}</span>
          </button>
        </div>

        <!-- Error message for generate -->
        <p v-if="generateError" class="text-[11px] text-[#A6382C] font-medium text-center">
          {{ generateError }}
        </p>
      </div>
      </div>
      <!-- ── NAVIGATION ── -->
      <div class="flex items-center justify-between text-xs px-1">
        <router-link to="/history"
          class="text-[#676F7F] hover:text-[#14213D] font-semibold flex items-center gap-1 active-press transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Riwayat Laporan
        </router-link>
        <router-link to="/create"
          class="text-[#14213D] hover:text-[#1B2C4D] font-bold flex items-center gap-1 bg-[#DEE2EB] px-3 py-1.5 rounded-xl border border-[#CBD1DB] active-press transition">
          + Laporan Baru
          <svg class="w-3.5 h-3.5 text-[#14213D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
      </div>

    </div>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="toastMessage"
          class="fixed bottom-20 left-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold border"
          :class="toastType === 'success' ? 'bg-[#14213D] text-[#F5F7FA] border-[#2A4068]' : 'bg-[#A6382C] text-white border-[#8F2F24]'"
          style="max-width: 440px; margin-left: auto; margin-right: auto;">
          <svg v-if="toastType === 'success'" class="w-4 h-4 shrink-0 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span class="flex-1">{{ toastMessage }}</span>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api.js';
import { sharePdfDocument } from '../services/reportShare.js';
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.js';
import pdfWorkerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.js?url';


pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;
import { normalizeReportLocations } from '../services/reportSlides.js';

const route = useRoute();
const reportId = route.params.id;

// ── State ──
const loading = ref(true);
const errorMessage = ref('');
const report = ref(null);
const activeSlideIndex = ref(0);
const totalSlides = ref(6); // 1 sampul + 5 titik pemantauan, sama dengan PDF

const generatingPdf = ref(false);

const sharing = ref(false);
const generateError = ref('');

// Touch swipe state
let touchStartX = 0;

// ── Data slide: sama dengan yang dirender PDF (selalu 5 titik pemantauan) ──
const slideLocations = computed(() => normalizeReportLocations(report.value));
const slideReport = computed(() => ({
  report_number: report.value?.report_number,
  formatted_date: report.value?.formatted_date || report.value?.inspection_date,
  officer_name: report.value?.officer_name,
  officer_photo_url: report.value?.officer_photo_url || null,
}));

// ── Computed ──
const hasPdf  = computed(() => !!report.value?.pdf_path);


const activeSlideTitle = computed(() => {
  if (activeSlideIndex.value === 0) return 'Cover Laporan';
  return slideLocations.value[activeSlideIndex.value - 1]?.name || `Lokasi ${activeSlideIndex.value}`;
});

// ── Slide navigation ──
const nextSlide = () => {
  if (activeSlideIndex.value < totalSlides.value - 1) activeSlideIndex.value++;
};
const prevSlide = () => {
  if (activeSlideIndex.value > 0) activeSlideIndex.value--;
};

// Touch swipe support
const onTouchStart = (e) => {
  touchStartX = e.touches[0].clientX;
};
const onTouchEnd = (e) => {
  const diff = touchStartX - e.changedTouches[0].clientX;
  if (Math.abs(diff) > 40) {
    if (diff > 0) nextSlide();
    else prevSlide();
  }
};

// ── Base URL for API ──
const baseUrl = api.defaults.baseURL;

// ── Fetch Report ──
const fetchReport = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    let id = reportId;
    if (!id) {
      const listRes = await api.get('/reports', { params: { per_page: 1 } });
      const first = listRes.data?.data?.data?.[0];
      if (first) {
        id = first.id;
      } else {
        errorMessage.value = 'Belum ada laporan yang dibuat.';
        loading.value = false;
        return;
      }
    }
    const res = await api.get(`/reports/${id}`);
    if (res.data?.data) {
      report.value = res.data.data;
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat pratinjau laporan.';
  } finally {
    loading.value = false;
  }
};

// ── Pratinjau = halaman PDF hasil template PPTX ──
const pageImages = ref([]);
const pagesError = ref('');

const renderPdfPages = async () => {
  if (!report.value) return;
  pagesError.value = '';
  pageImages.value = [];
  try {
    // PDF belum ada -> buat dulu (memakai template yang dipilih)
    if (!report.value.pdf_path) {
      const g = await api.post(`/reports/${report.value.id}/generate-pdf`);
      if (g.data?.data) report.value = g.data.data;
    }
    const res = await api.get(`/reports/${report.value.id}/pdf`, {
      params: { inline: 1, t: Date.now() },
      responseType: 'arraybuffer',
    });
    const pdf = await pdfjsLib.getDocument({ data: new Uint8Array(res.data) }).promise;
    const imgs = [];
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);
      const base = page.getViewport({ scale: 1 });
      const vp = page.getViewport({ scale: Math.min(2, 1280 / base.width) });
      const canvas = document.createElement('canvas');
      canvas.width = Math.floor(vp.width);
      canvas.height = Math.floor(vp.height);
      await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
      imgs.push(canvas.toDataURL('image/jpeg', 0.88));
    }
    pageImages.value = imgs;
    totalSlides.value = imgs.length;
    if (activeSlideIndex.value >= imgs.length) activeSlideIndex.value = 0;
  } catch (err) {
    pagesError.value = 'Gagal memuat pratinjau PDF: ' + (err.message || 'kesalahan tidak diketahui');
  }
};

// ── Generate PDF ──
const doGeneratePdf = async () => {
  generatingPdf.value = true;
  generateError.value = '';
  try {
    const res = await api.post(`/reports/${report.value.id}/generate-pdf`);
    if (res.data?.data) report.value = res.data.data;
    await renderPdfPages();
  } catch (err) {
    generateError.value = err.response?.data?.message || 'Gagal membuat PDF.';
  } finally {
    generatingPdf.value = false;
  }
};

// ── Open / Download ──
const openPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf?inline=1`, '_blank');
};
const downloadPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf`, '_blank');
};

// ── Share (Capacitor native PDF File Share) ──
const shareReport = async () => {
  if (!report.value) return;
  sharing.value = true;
  generateError.value = '';
  try {
    const result = await sharePdfDocument(report.value);
    if (result.success) {
      if (result.method === 'native-file' || result.method === 'web-file') {
        showToast('Dokumen PDF berhasil dibagikan!', 'success');
      } else if (result.method === 'web-open') {
        showToast('PDF dibuka di tab baru untuk dibagikan manual.', 'success');
      }
      // native-url / web-url: the OS share sheet already shows its own confirmation.
    } else if (!result.cancelled) {
      generateError.value = 'Gagal membagikan laporan: ' + (result.error || 'Terjadi kesalahan.');
    }
    // result.cancelled === true: user backed out of the share sheet, nothing to show.
  } finally {
    sharing.value = false;
  }
};



// ── Toast State ──
const toastMessage = ref('');
const toastType = ref('success');
let toastTimer = null;

const showToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toastMessage.value = '';
  }, 3500);
};

onMounted(async () => {
  await fetchReport();
  renderPdfPages();
});
</script>

<style scoped>
/* Slide transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(18px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-18px);
}

/* Modal Bottom Sheet Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
