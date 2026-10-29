<template>
  <div class="report-preview-container">

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-4">
      <div class="skeleton-card">
        <div class="skeleton-line-title"/>
        <div class="skeleton-line-sub"/>
        <div class="skeleton-canvas"/>
      </div>
      <div class="skeleton-card">
        <div class="skeleton-line-title w-1/4"/>
        <div class="skeleton-bar"/>
        <div class="skeleton-bar"/>
      </div>
    </div>

    <!-- ── ERROR STATE ── -->
    <div v-else-if="errorMessage" class="preview-error-card">
      <div class="preview-error-icon">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <p class="text-sm font-bold text-[#14213D]">{{ errorMessage }}</p>
      <router-link
        to="/history"
        class="btn-back-history"
      >
        ← Kembali ke Riwayat
      </router-link>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div v-else-if="report" class="space-y-4">

      <!-- ── HEADER METADATA ── -->
      <div class="meta-card">
        <div class="flex items-center justify-between mb-3">
          <span class="report-number-badge">
            {{ report.report_number }}
          </span>
          <span
            class="report-status-badge"
            :class="report.status === 'completed' ? 'status-completed' : 'status-draft'"
          >
            {{ report.status === 'completed' ? 'Tersimpan Resmi' : 'Draft' }}
          </span>
        </div>

        <div class="meta-grid">
          <div>
            <span class="meta-label">Tanggal Pemeriksaan</span>
            <span class="meta-value font-mono">{{ report.formatted_date || report.inspection_date }}</span>
          </div>
          <div>
            <span class="meta-label">Petugas Pengawas</span>
            <span class="meta-value">{{ report.officer_name }}</span>
          </div>
          <div class="meta-full-row">
            <span class="meta-label">Format Dokumen</span>
            <span class="template-name-pill">
              {{ report.template?.name || 'Standar BPSDM' }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── SLIDE VIEWER (16:9 CAROUSEL) ── -->
      <div class="viewer-card">
        <!-- Top navigator -->
        <div class="flex items-center justify-between px-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#F5A524] animate-pulse"/>
            <h3 class="viewer-counter">
              Slide {{ activeSlideIndex + 1 }}/{{ totalSlides }}
            </h3>
            <span class="viewer-title">• {{ activeSlideTitle }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <button
              type="button"
              @click="prevSlide"
              :disabled="activeSlideIndex === 0"
              class="btn-slide-nav"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button
              type="button"
              @click="nextSlide"
              :disabled="activeSlideIndex === totalSlides - 1"
              class="btn-slide-nav"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 16:9 Slide Canvas -->
        <div
          class="viewer-canvas"
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
              class="slide-img"
            />
            <div v-else :key="'ph'" class="slide-placeholder">
              <div v-if="pagesError" class="space-y-2">
                <p class="text-xs font-semibold text-[#A6382C]">{{ pagesError }}</p>
                <button
                  type="button"
                  @click="renderPdfPages"
                  class="btn-retry-render"
                >
                  Coba lagi
                </button>
              </div>
              <p v-else class="text-xs font-semibold text-[#676F7F] animate-pulse">
                Menyiapkan pratinjau dari template… (bisa sampai 40 detik)
              </p>
            </div>
          </Transition>
        </div>

        <!-- Dot navigation -->
        <div class="flex items-center justify-center gap-1.5 pt-1">
          <button
            v-for="(_, idx) in totalSlides"
            :key="idx"
            type="button"
            @click="activeSlideIndex = idx"
            class="dot-indicator"
            :class="activeSlideIndex === idx ? 'dot-active' : 'dot-inactive'"
          />
        </div>

        <!-- Swipe hint -->
        <p class="swipe-hint">
          ← Geser layar ke kiri atau kanan untuk berpindah slide →
        </p>
      </div>

      <!-- ── DOKUMEN STATUS CARD ── -->
      <div class="doc-card">
        <div class="flex items-center justify-between">
          <h4 class="doc-title">Status Berkas Dokumen</h4>
          <span class="text-[10px] text-[#788191] font-mono">Format 16:9 HD</span>
        </div>

        <div class="space-y-2">
          <!-- PDF status box -->
          <div
            class="doc-status-box"
            :class="hasPdf ? 'bg-white border-[#CBD1DB]' : 'bg-[#E9ECF2] border-[#D1D6DF]'"
          >
            <div class="flex items-center gap-2.5">
              <div
                class="doc-status-icon"
                :class="hasPdf ? 'bg-[#14213D] text-[#F5A524]' : 'bg-[#C9CED7] text-[#788191]'"
              >
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
            <span v-if="hasPdf" class="badge-pdf-ready">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              <span>Tersedia</span>
            </span>
          </div>

          <!-- Generate PDF button -->
          <button
            v-if="!hasPdf"
            type="button"
            @click="doGeneratePdf"
            :disabled="generatingPdf"
            class="btn-generate-pdf"
          >
            <svg v-if="generatingPdf" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <svg v-else class="w-5 h-5 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
            </svg>
            <span>{{ generatingPdf ? 'Memproses Berkas PDF 16:9…' : 'Generate Dokumen PDF 16:9' }}</span>
          </button>

          <!-- PDF action buttons -->
          <div v-else class="grid grid-cols-2 gap-2">
            <button
              type="button"
              @click="openPdf"
              class="btn-open-pdf"
            >
              <svg class="w-4 h-4 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              <span>Buka PDF</span>
            </button>
            <button
              type="button"
              @click="downloadPdf"
              class="btn-download-pdf"
            >
              <svg class="w-4 h-4 text-[#14213D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              <span>Unduh PDF</span>
            </button>
          </div>

          <!-- Share button -->
          <div class="pt-2 border-t border-[#DEE2EB]">
            <button
              type="button"
              @click="shareReport"
              :disabled="!hasPdf || sharing"
              class="btn-share-report"
            >
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
        <router-link
          to="/history"
          class="btn-nav-history"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          <span>Riwayat Laporan</span>
        </router-link>
        <router-link
          to="/create"
          class="btn-nav-create"
        >
          <span>+ Laporan Baru</span>
          <svg class="w-3.5 h-3.5 text-[#14213D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
      </div>

    </div>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="toastMessage"
          class="toast-bottom"
          :class="toastType === 'success' ? 'toast-success' : 'toast-error'"
        >
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
// SEMENTARA (ngrok): lihat services/ngrokWorkaround.js. Di server non-ngrok perilakunya sama seperti kode biasa.
import { getPdfJsSource, openReportPdf, downloadReportPdf } from '../services/ngrokWorkaround.js';
import pdfWorkerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.js?url';

pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;
import { normalizeReportLocations } from '../services/reportSlides.js';

const route = useRoute();
const reportId = route.params.id;

// State
const loading = ref(true);
const errorMessage = ref('');
const report = ref(null);
const activeSlideIndex = ref(0);
const generatingPdf = ref(false);
const generateError = ref('');
const sharing = ref(false);

const pageImages = ref([]);
const pagesError = ref('');

let touchStartX = 0;

// Computed
const hasPdf = computed(() => !!report.value?.pdf_path);

const slideLocations = computed(() => {
  return normalizeReportLocations(report.value?.report_locations || []);
});

const totalSlides = computed(() => {
  return 1 + slideLocations.value.length;
});

const activeSlideTitle = computed(() => {
  if (activeSlideIndex.value === 0) return 'Cover Laporan';
  return slideLocations.value[activeSlideIndex.value - 1]?.name || `Lokasi ${activeSlideIndex.value}`;
});

// Slide navigation
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

const baseUrl = api.defaults.baseURL;

// Fetch report
const fetchReport = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    const res = await api.get(`/reports/${reportId}`);
    report.value = res.data?.data;
  } catch (err) {
    console.error('Gagal mengambil laporan:', err);
    errorMessage.value = 'Laporan tidak ditemukan atau server tidak merespons.';
  } finally {
    loading.value = false;
  }
};

// Render PDF pages
const renderPdfPages = async () => {
  pagesError.value = '';
  pageImages.value = [];
  try {
    const url = `${baseUrl}/reports/${reportId}/pdf?preview=1`;
    const loadingTask = pdfjsLib.getDocument(await getPdfJsSource(url));
    const pdfDoc = await loadingTask.promise;
    const numPages = pdfDoc.numPages;

    const rendered = [];
    for (let i = 1; i <= numPages; i++) {
      const page = await pdfDoc.getPage(i);
      const viewport = page.getViewport({ scale: 1.5 });
      const canvas = document.createElement('canvas');
      canvas.width = viewport.width;
      canvas.height = viewport.height;
      const ctx = canvas.getContext('2d');
      await page.render({ canvasContext: ctx, viewport }).promise;
      rendered.push(canvas.toDataURL('image/png'));
    }
    pageImages.value = rendered;
  } catch (e) {
    console.warn('Gagal render halaman PDF:', e);
    pagesError.value = 'Pratinjau visual belum siap.';
  }
};

// Generate PDF
const doGeneratePdf = async () => {
  generatingPdf.value = true;
  generateError.value = '';
  try {
    const res = await api.post(`/reports/${reportId}/generate-pdf`);
    report.value = res.data?.data;
    await renderPdfPages();
  } catch (err) {
    console.error('Gagal generate PDF:', err);
    generateError.value = err.response?.data?.message || 'Gagal membuat file PDF. Silakan coba lagi.';
  } finally {
    generatingPdf.value = false;
  }
};

// Open / Download PDF
const openPdf = async () => {
  const result = await openReportPdf(
    reportId,
    report.value?.report_number,
    `${baseUrl}/reports/${reportId}/pdf?inline=1`
  );
  if (!result.success && !result.cancelled) generateError.value = result.error;
};
const downloadPdf = async () => {
  const result = await downloadReportPdf(
    reportId,
    report.value?.report_number,
    `${baseUrl}/reports/${reportId}/pdf`
  );
  if (!result.success && !result.cancelled) generateError.value = result.error;
};

// Share Report
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
    } else if (!result.cancelled) {
      generateError.value = 'Gagal membagikan laporan: ' + (result.error || 'Terjadi kesalahan.');
    }
  } finally {
    sharing.value = false;
  }
};

// Toast State
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
.report-preview-container {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 1rem;
  padding-bottom: 7rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Skeleton */
.skeleton-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid #D7DBE2;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-line-title {
  height: 1.25rem;
  background-color: #DDE0E7;
  border-radius: 0.25rem;
  width: 33%;
}

.skeleton-line-sub {
  height: 0.75rem;
  background-color: #DDE0E7;
  border-radius: 0.25rem;
  width: 66%;
}

.skeleton-canvas {
  aspect-ratio: 16 / 9;
  background-color: #DDE0E7;
  border-radius: 0.75rem;
  width: 100%;
}

.skeleton-bar {
  height: 3rem;
  background-color: #DDE0E7;
  border-radius: 0.75rem;
}

/* Error State */
.preview-error-card {
  background-color: #FDF0EE;
  border: 1px solid #F5C7C1;
  border-radius: 1rem;
  padding: 1.5rem;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.preview-error-icon {
  width: 3.5rem;
  height: 3.5rem;
  margin: 0 auto;
  border-radius: 1rem;
  background-color: #FBF1EF;
  border: 1px solid #F3D5CF;
  color: #A6382C;
  display: flex;
  align-items: center;
  justify-content: center;
}

.btn-back-history {
  display: inline-flex;
  align-items: center;
  align-self: center;
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-size: 0.75rem;
  font-weight: 600;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-back-history:active {
  transform: scale(0.97);
}

/* Metadata Card */
.meta-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 0.875rem 1rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.report-number-badge {
  font-family: var(--font-mono, monospace);
  font-size: 0.75rem;
  font-weight: 700;
  color: #14213D;
  background-color: #DEE2EB;
  padding: 0.25rem 0.625rem;
  border-radius: 0.375rem;
  border: 1px solid #CBD1DB;
}

.report-status-badge {
  font-size: 0.625rem;
  font-weight: 700;
  padding: 0.125rem 0.625rem;
  border-radius: 9999px;
  border-width: 1px;
  border-style: solid;
}

.status-completed {
  background-color: #EAF3EC;
  color: #1B2C4D;
  border-color: #C8E0D0;
}

.status-draft {
  background-color: #FAF2DF;
  color: #8C6D1F;
  border-color: #EADBBD;
}

.meta-grid {
  display: grid;
  grid-template-columns: repeat(2, minmax(0, 1fr));
  column-gap: 1rem;
  row-gap: 0.5rem;
  font-size: 0.75rem;
}

.meta-label {
  color: #788191;
  font-size: 0.625rem;
  text-transform: uppercase;
  font-weight: 700;
  letter-spacing: 0.05em;
  display: block;
}

.meta-value {
  font-weight: 600;
  color: #14213D;
}

.meta-full-row {
  grid-column: span 2 / span 2;
  padding-top: 0.25rem;
  border-top: 1px solid #DEE2EB;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.template-name-pill {
  font-weight: 600;
  color: #14213D;
  font-size: 0.75rem;
  background-color: #DEE2EB;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  border: 1px solid #CBD1DB;
}

/* Viewer Card */
.viewer-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 0.75rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.viewer-counter {
  font-size: 0.75rem;
  font-weight: 700;
  color: #14213D;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  font-family: var(--font-mono, monospace);
}

.viewer-title {
  font-size: 0.625rem;
  color: #676F7F;
  font-weight: 500;
}

.btn-slide-nav {
  width: 2rem;
  height: 2rem;
  border-radius: 0.75rem;
  background-color: #FFFFFF;
  border: 1px solid #CBD1DB;
  color: #14213D;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-slide-nav:hover {
  background-color: #E9ECF2;
}

.btn-slide-nav:disabled {
  opacity: 0.3;
  cursor: not-allowed;
}

.btn-slide-nav:active {
  transform: scale(0.95);
}

.viewer-canvas {
  position: relative;
  width: 100%;
  border-radius: 0.75rem;
  overflow: hidden;
  border: 1px solid #E2E8F0;
  box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.05);
  user-select: none;
  touch-action: pan-y;
}

.slide-img {
  width: 100%;
  display: block;
  background-color: #FFFFFF;
  aspect-ratio: 16 / 9;
  object-fit: contain;
}

.slide-placeholder {
  width: 100%;
  aspect-ratio: 16 / 9;
  background-color: #DEE2EB;
  display: flex;
  align-items: center;
  justify-content: center;
  padding: 1rem;
  text-align: center;
}

.btn-retry-render {
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-size: 0.6875rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.dot-indicator {
  height: 0.375rem;
  border-radius: 9999px;
  border: none;
  cursor: pointer;
  transition: all 0.3s ease;
}

.dot-active {
  width: 1.5rem;
  background-color: #14213D;
}

.dot-inactive {
  width: 0.5rem;
  background-color: #C9CED7;
}

.swipe-hint {
  text-align: center;
  font-size: 0.625rem;
  color: #788191;
  font-weight: 500;
}

/* Document Status Card */
.doc-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.doc-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #14213D;
}

.doc-status-box {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.75rem;
  border-radius: 0.75rem;
  border-width: 1px;
  border-style: solid;
}

.doc-status-icon {
  width: 2rem;
  height: 2rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.badge-pdf-ready {
  font-size: 0.625rem;
  color: #1B2C4D;
  font-weight: 700;
  background-color: #EAF3EC;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  border: 1px solid #C8E0D0;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

.btn-generate-pdf {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border-radius: 1rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-weight: 700;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.15s ease;
}

.btn-generate-pdf:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-generate-pdf:active {
  transform: scale(0.98);
}

.btn-open-pdf {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-weight: 700;
  font-size: 0.75rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.15s ease;
}

.btn-open-pdf:active {
  transform: scale(0.97);
}

.btn-download-pdf {
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.375rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background-color: #FFFFFF;
  border: 1px solid #CBD1DB;
  color: #14213D;
  font-weight: 700;
  font-size: 0.75rem;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-download-pdf:hover {
  background-color: #EEF0F4;
}

.btn-download-pdf:active {
  transform: scale(0.97);
}

.btn-share-report {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background-color: #1B2C4D;
  color: #F5F7FA;
  font-weight: 700;
  font-size: 0.75rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.15s ease;
}

.btn-share-report:disabled {
  opacity: 0.4;
  cursor: not-allowed;
}

.btn-share-report:active {
  transform: scale(0.98);
}

/* Navigation Row */
.btn-nav-history {
  color: #676F7F;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  text-decoration: none;
  transition: color 0.15s ease;
}

.btn-nav-history:hover {
  color: #14213D;
}

.btn-nav-create {
  color: #14213D;
  font-weight: 700;
  display: flex;
  align-items: center;
  gap: 0.25rem;
  background-color: #DEE2EB;
  padding: 0.375rem 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #CBD1DB;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-nav-create:hover {
  background-color: #D1D5DE;
}

/* Toast */
.toast-bottom {
  position: fixed;
  bottom: 5rem;
  left: 1rem;
  right: 1rem;
  z-index: 50;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  font-size: 0.75rem;
  font-weight: 600;
  border-width: 1px;
  border-style: solid;
  max-width: 440px;
  margin-left: auto;
  margin-right: auto;
}

.toast-success {
  background-color: #14213D;
  color: #F5F7FA;
  border-color: #2A4068;
}

.toast-error {
  background-color: #A6382C;
  color: #FFFFFF;
  border-color: #8F2F24;
}

/* Slide Transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(12px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-12px);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
