<template>
  <!--
    Satu slide laporan (cover atau titik pemantauan) dengan desain template yang
    SAMA dengan PDF: struktur HTML mengikuti resources/views/pdf/report_16_9.blade.php
    dan CSS desainnya adalah resources/css/report-templates.css (file yang sama
    di-inline ke blade). Slide dirender 960pt x 540pt lalu diskalakan ke lebar wadah.
  -->
  <div
    ref="host"
    class="rt-scope"
    :class="'theme-' + resolvedTheme.style_type"
    :style="hostStyle"
    role="img"
    :aria-label="ariaLabel"
  >
    <div class="rt-stage" :style="stageStyle">
      <!-- ═══ COVER ═══ -->
      <div v-if="slide === 0" class="slide">
        <div class="cover-header">
          <div class="cover-badge">BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR</div>
          <h1 class="cover-title">LAPORAN PENGAWASAN LINGKUNGAN KANTOR</h1>
        </div>

        <div class="cover-card-left">
          <div class="meta-group">
            <div class="meta-label">Nomor Laporan Resmi</div>
            <div class="meta-value">{{ report.report_number }}</div>
          </div>
          <div class="meta-group">
            <div class="meta-label">Hari &amp; Tanggal Inspeksi</div>
            <div class="meta-value-sub">{{ report.formatted_date }}</div>
          </div>
          <div class="meta-group">
            <div class="meta-label">Petugas Pengawas Lapangan</div>
            <div class="meta-value-sub" :style="{ color: '#' + resolvedTheme.primary_color, fontSize: '14pt' }">
              {{ report.officer_name }}
            </div>
          </div>
          <div class="meta-group" style="margin-bottom: 0;">
            <div class="meta-label">Tema Desain PDF</div>
            <div style="font-size: 10pt; color: #64748b;">
              {{ templateName || 'Default' }} • 16:9 PDF Widescreen 16:9
            </div>
          </div>
        </div>

        <div class="cover-photo-frame">
          <img v-if="report.officer_photo_url" :src="report.officer_photo_url"
            class="cover-photo-img" :style="officerSize" alt="Foto Petugas" />
          <div v-else class="cover-photo-placeholder"
            :style="{ ...officerSize, lineHeight: (officerH - 40) + 'pt' }">[ FOTO PETUGAS ]</div>
          <div style="font-size: 8.5pt; font-weight: bold; color: #64748b; margin-top: 5pt; text-transform: uppercase;">
            Foto Petugas Pengawas
          </div>
        </div>

        <div class="cover-footer">
          <span>Sistem Informasi Pelaporan Pengawasan Lingkungan Kantor BPSDM • PDF Widescreen 16:9</span>
        </div>
      </div>

      <!-- ═══ TITIK PEMANTAUAN ═══ -->
      <div v-else-if="location" class="slide">
        <div class="loc-header">
          <div class="loc-counter">TITIK PEMANTAUAN {{ slide }} DARI 5</div>
          <div class="loc-title">{{ location.name }}</div>
        </div>

        <div class="loc-body">
          <div class="photos-container">
            <div class="photo-box">
              <img v-if="location.photo_1" :src="location.photo_1" class="photo-img" :style="locSize" alt="Dokumentasi 1" />
              <div v-else class="photo-placeholder" :style="{ ...locSize, lineHeight: locH + 'pt' }">[ DOKUMENTASI 1 ]</div>
              <div class="photo-label">Dokumentasi 1</div>
            </div>
            <div class="photo-box photo-box-right">
              <img v-if="location.photo_2" :src="location.photo_2" class="photo-img" :style="locSize" alt="Dokumentasi 2" />
              <div v-else class="photo-placeholder" :style="{ ...locSize, lineHeight: locH + 'pt' }">[ DOKUMENTASI 2 ]</div>
              <div class="photo-label">Dokumentasi 2</div>
            </div>
          </div>

          <div class="info-box">
            <div style="margin-bottom: 6pt;">
              <span style="font-size: 9pt; font-weight: bold; color: #64748b; margin-right: 8pt; text-transform: uppercase;">
                Status Kebersihan:
              </span>
              <span v-if="location.condition === 'clean'" class="badge-clean">✓ {{ location.condition_label }}</span>
              <span v-else class="badge-attention">! {{ location.condition_label }}</span>
            </div>

            <table class="info-table">
              <tbody v-if="location.condition === 'clean'">
                <tr>
                  <td class="info-title">Catatan Pemantauan:</td>
                  <td class="info-content">{{ location.notes || 'Area terpantau bersih, rapi, dan terawat dengan baik.' }}</td>
                </tr>
              </tbody>
              <tbody v-else>
                <tr>
                  <td class="info-title" style="color: #b91c1c;">Permasalahan:</td>
                  <td class="info-content" style="color: #991b1b; font-weight: bold;">
                    {{ location.problem || 'Perlu penanganan kebersihan lebih lanjut pada titik ini.' }}
                  </td>
                </tr>
                <tr>
                  <td class="info-title" style="color: #0284c7;">Solusi Penanganan:</td>
                  <td class="info-content">
                    {{ location.solution || 'Telah dikoordinasikan dengan tim kebersihan untuk penanganan segera.' }}
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>

        <div class="loc-footer">
          <div class="footer-left">
            {{ report.report_number }} • {{ report.formatted_date }} • Petugas: {{ report.officer_name }}
          </div>
          <div class="footer-right">Slide {{ slide + 1 }} dari 6</div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { FALLBACK_THEME, SAMPLE_LOCATIONS, SAMPLE_REPORT } from '../services/reportSlides.js';

const props = defineProps({
  /** Template::theme dari API (style_type, warna, ukuran foto). */
  theme: { type: Object, default: null },
  /** Nama template, untuk baris "Tema Desain PDF" di sampul. */
  templateName: { type: String, default: '' },
  /** 0 = sampul, 1..5 = titik pemantauan. */
  slide: { type: Number, default: 0 },
  /** Data laporan: report_number, formatted_date, officer_name, officer_photo_url. */
  report: { type: Object, default: () => SAMPLE_REPORT },
  /** Hasil normalizeReportLocations(); index 0 = titik 1. Default: data contoh. */
  locations: { type: Array, default: () => SAMPLE_LOCATIONS },
});

// ── Skala: 960pt = 1280px → lebar wadah ──
const STAGE_PX = 1280;
const host = ref(null);
const scale = ref(0);

const resolvedTheme = computed(() => ({
  ...FALLBACK_THEME,
  ...(props.theme || {}),
  photo: { ...FALLBACK_THEME.photo, ...(props.theme?.photo || {}) },
}));

// Ukuran foto dari tema (sama dengan crop server-side & style inline di blade).
const officerW = computed(() => resolvedTheme.value.photo.officer[0]);
const officerH = computed(() => resolvedTheme.value.photo.officer[1]);
const locW = computed(() => resolvedTheme.value.photo.location[0]);
const locH = computed(() => resolvedTheme.value.photo.location[1]);
const officerSize = computed(() => ({ width: officerW.value + 'pt', height: officerH.value + 'pt' }));
const locSize = computed(() => ({ width: locW.value + 'pt', height: locH.value + 'pt' }));

// Sampel hanya punya 1 lokasi; slide > jumlah data memakai lokasi pertama.
const location = computed(() => {
  if (props.slide < 1) return null;
  return props.locations[props.slide - 1] || props.locations[0] || null;
});

const hostStyle = computed(() => ({
  '--rt-bg': '#' + resolvedTheme.value.bg_color,
  '--rt-primary': '#' + resolvedTheme.value.primary_color,
  '--rt-accent': '#' + resolvedTheme.value.accent_color,
  visibility: scale.value > 0 ? 'visible' : 'hidden',
}));

const ariaLabel = computed(() =>
  props.slide === 0 ? `Sampul laporan, desain ${props.templateName}` : `Titik pemantauan ${props.slide}, desain ${props.templateName}`
);

let observer = null;

const measure = () => {
  if (host.value && host.value.clientWidth > 0) {
    scale.value = host.value.clientWidth / STAGE_PX;
  }
};
const stageStyle = computed(() => ({ transform: `scale(${scale.value})` }));

onMounted(() => {
  measure();
  if (typeof ResizeObserver !== 'undefined') {
    observer = new ResizeObserver(measure);
    observer.observe(host.value);
  } else {
    window.addEventListener('resize', measure);
  }
});
onBeforeUnmount(() => {
  observer?.disconnect();
  window.removeEventListener('resize', measure);
});
</script>
