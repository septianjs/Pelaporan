<template>
  <div class="px-4 py-5 space-y-5 pb-24">

    <!-- ── HEADER ── -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-bold text-slate-800">Upload & Validasi Template PPTX</h2>
          <p class="text-xs text-slate-500">PowerPoint Widescreen 16:9 (13.333 × 7.5")</p>
        </div>
      </div>

      <!-- Info box -->
      <div class="bg-amber-50 border border-amber-200 rounded-xl p-3 space-y-1.5 mt-3">
        <p class="text-xs font-bold text-amber-800 flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Syarat Template PPTX
        </p>
        <ul class="text-xs text-amber-700 space-y-1 list-disc list-inside">
          <li>Format file: <strong>.pptx</strong> (PowerPoint 2007+)</li>
          <li>Rasio slide: <strong>16:9 Widescreen</strong> (13.333 × 7.5 inch)</li>
          <li>Struktur: <strong>Minimal 6 slide</strong> (1 Cover + 5 Lokasi Pengawasan)</li>
          <li>Maksimal file: <strong>20 MB</strong></li>
        </ul>
        <p class="text-[11px] text-amber-600 mt-1">
          Di PowerPoint: <em>Design → Slide Size → Widescreen (16:9)</em>
        </p>
      </div>
    </div>

    <!-- ── PLACEHOLDER GUIDE ── -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
      <div class="flex items-center justify-between cursor-pointer" @click="showPlaceholderGuide = !showPlaceholderGuide">
        <h3 class="text-sm font-bold text-slate-800 flex items-center gap-2">
          <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Panduan Placeholder Resmi
        </h3>
        <span class="text-xs text-blue-600 font-semibold flex items-center gap-1">
          {{ showPlaceholderGuide ? 'Tutup' : 'Lihat Daftar' }}
          <svg class="w-3.5 h-3.5 transform transition-transform" :class="{ 'rotate-180': showPlaceholderGuide }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </span>
      </div>

      <div v-if="showPlaceholderGuide" class="space-y-2 mt-4 pt-3 border-t border-slate-100">
        <div v-for="ph in placeholders" :key="ph.key"
          class="flex items-start gap-3 p-2.5 rounded-xl bg-slate-50 border border-slate-200">
          <code class="text-[11px] font-mono font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded shrink-0">
            {{ ph.key }}
          </code>
          <span class="text-xs text-slate-600">{{ ph.desc }}</span>
        </div>
      </div>
    </div>

    <!-- ── UPLOAD FORM ── -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 space-y-4">
      <h3 class="text-sm font-bold text-slate-800">Pilih File PPTX</h3>

      <!-- File Picker PPTX -->
      <div class="space-y-1.5">
        <div
          @click="fileInput?.click()"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="onDrop"
          :class="[
            'relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition',
            isDragging ? 'border-blue-500 bg-blue-50' : 'border-slate-300 bg-slate-50 hover:bg-slate-100',
            errors.pptx_file ? 'border-rose-400 bg-rose-50' : '',
          ]"
        >
          <input
            ref="fileInput"
            type="file"
            accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation"
            class="hidden"
            @change="onFileSelect"
          />

          <!-- No file selected -->
          <div v-if="!selectedFile" class="space-y-2">
            <div class="w-12 h-12 mx-auto rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-slate-700">Ketuk untuk pilih file PPTX</p>
              <p class="text-[11px] text-slate-400 mt-0.5">atau seret dan lepas di sini</p>
              <p class="text-[11px] text-slate-500 mt-1 font-medium">Format .pptx · Maksimal 20 MB</p>
            </div>
          </div>

          <!-- File selected -->
          <div v-else class="flex items-center gap-3 text-left">
            <div class="w-12 h-12 rounded-xl bg-indigo-600 flex items-center justify-center shrink-0">
              <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-slate-800 truncate">{{ selectedFile.name }}</p>
              <p class="text-xs text-slate-500 mt-0.5">{{ formatBytes(selectedFile.size) }}</p>
              <p v-if="isValidating" class="text-[11px] text-blue-600 font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Memeriksa struktur & rasio slide…
              </p>
              <p v-else-if="validationReport?.valid" class="text-[11px] text-emerald-600 font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Template valid (16:9 Widescreen • {{ validationReport.details?.slide_count }} Slide)
              </p>
              <p v-else-if="validationReport && !validationReport.valid" class="text-[11px] text-rose-600 font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Template tidak memenuhi syarat
              </p>
            </div>
            <button
              type="button"
              @click.stop="clearFile"
              class="w-8 h-8 rounded-full bg-slate-200 hover:bg-rose-100 text-slate-500 hover:text-rose-600 flex items-center justify-center transition shrink-0"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <p v-if="errors.pptx_file" class="text-[11px] text-rose-600 flex items-center gap-1">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ errors.pptx_file }}
        </p>
      </div>

      <!-- ── VALIDATION & INSPECTION REPORT CARD ── -->
      <Transition name="fade-up">
        <div v-if="validationReport" class="rounded-2xl border p-4 space-y-3"
          :class="validationReport.valid ? 'bg-emerald-50/60 border-emerald-200' : 'bg-rose-50/60 border-rose-200'">
          
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs text-white"
                :class="validationReport.valid ? 'bg-emerald-600' : 'bg-rose-600'">
                {{ validationReport.valid ? '✓' : '!' }}
              </span>
              <p class="text-xs font-bold" :class="validationReport.valid ? 'text-emerald-900' : 'text-rose-900'">
                {{ validationReport.valid ? 'Hasil Validasi: Template Siap Digunakan' : 'Hasil Validasi: Template Ditolak' }}
              </p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full"
              :class="validationReport.valid ? 'bg-emerald-100 text-emerald-800' : 'bg-rose-100 text-rose-800'">
              {{ validationReport.valid ? 'VALID 16:9' : 'TIDAK VALID' }}
            </span>
          </div>

          <!-- Parameter grid -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-white/80 rounded-xl p-2.5 border border-slate-200">
              <span class="text-[10px] text-slate-500 block">Rasio & Ukuran Slide</span>
              <p class="font-bold text-slate-800 mt-0.5">
                {{ validationReport.details?.width_inch }}" × {{ validationReport.details?.height_inch }}"
                <span class="text-[10px] font-semibold text-emerald-600 ml-1">
                  ({{ validationReport.details?.aspect_ratio }}:1)
                </span>
              </p>
            </div>
            <div class="bg-white/80 rounded-xl p-2.5 border border-slate-200">
              <span class="text-[10px] text-slate-500 block">Jumlah Slide</span>
              <p class="font-bold text-slate-800 mt-0.5">
                {{ validationReport.details?.slide_count }} Slide
                <span v-if="validationReport.details?.slide_count >= 6" class="text-[10px] text-emerald-600 font-semibold ml-1">✓ Cukup</span>
                <span v-else class="text-[10px] text-rose-600 font-semibold ml-1">✕ Kurang (min 6)</span>
              </p>
            </div>
          </div>

          <!-- Errors list (if any) -->
          <div v-if="validationReport.errors?.length" class="space-y-1">
            <p class="text-[11px] font-bold text-rose-800">Masalah yang Perlu Diperbaiki:</p>
            <ul class="text-xs text-rose-700 list-disc list-inside space-y-1">
              <li v-for="(err, idx) in validationReport.errors" :key="idx">{{ err }}</li>
            </ul>
          </div>

          <!-- Warnings list (if any) -->
          <div v-if="validationReport.warnings?.length" class="space-y-1">
            <p class="text-[11px] font-bold text-amber-800">Peringatan / Saran:</p>
            <ul class="text-xs text-amber-700 list-disc list-inside space-y-1">
              <li v-for="(warn, idx) in validationReport.warnings" :key="idx">{{ warn }}</li>
            </ul>
          </div>

          <!-- Detected Placeholders Badges -->
          <div v-if="validationReport.details?.all_placeholders?.length" class="space-y-1.5 pt-1">
            <p class="text-[11px] font-bold text-slate-700">
              Placeholder Terdeteksi ({{ validationReport.details.all_placeholders.length }}):
            </p>
            <div class="flex flex-wrap gap-1">
              <span
                v-for="ph in validationReport.details.all_placeholders"
                :key="ph"
                class="px-2 py-0.5 bg-indigo-50 border border-indigo-200 text-indigo-700 text-[10px] font-mono font-semibold rounded-md"
              >
                {{ ph }}
              </span>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Nama Template -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
          Nama Template <span class="text-rose-500">*</span>
        </label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Contoh: Template Hijau Modern"
          maxlength="100"
          class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition"
          :class="{ 'border-rose-400 ring-1 ring-rose-400': errors.name }"
        />
        <p v-if="errors.name" class="text-[11px] text-rose-600">{{ errors.name }}</p>
      </div>

      <!-- Deskripsi -->
      <div class="space-y-1.5">
        <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
          Deskripsi <span class="text-slate-400 font-normal normal-case">(opsional)</span>
        </label>
        <textarea
          v-model="form.description"
          placeholder="Deskripsi singkat tentang template ini…"
          rows="2"
          maxlength="500"
          class="w-full px-4 py-3 rounded-xl border border-slate-300 text-sm text-slate-800 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition resize-none"
        />
      </div>
    </div>

    <!-- ── UPLOAD BUTTON ── -->
    <button
      type="button"
      @click="submitUpload"
      :disabled="isUploading || isValidating || (validationReport && !validationReport.valid)"
      class="w-full flex items-center justify-center gap-2 px-5 py-4 rounded-2xl bg-gradient-to-r from-indigo-600 to-blue-600 text-white font-bold text-sm shadow-lg hover:from-indigo-700 hover:to-blue-700 active:scale-[0.98] transition disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <svg v-if="isUploading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
      </svg>
      <span>{{ isUploading ? 'Mengunggah Template…' : (validationReport && !validationReport.valid ? 'Perbaiki File Sebelum Upload' : 'Upload Template') }}</span>
    </button>

    <!-- Upload progress bar -->
    <div v-if="isUploading" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
      <div class="flex items-center justify-between text-xs font-semibold text-slate-600 mb-2">
        <span>Menyimpan template & membuat preview…</span>
        <span>{{ uploadProgress }}%</span>
      </div>
      <div class="h-2 bg-slate-200 rounded-full overflow-hidden">
        <div
          class="h-full bg-gradient-to-r from-indigo-500 to-blue-500 rounded-full transition-all duration-300"
          :style="{ width: uploadProgress + '%' }"
        />
      </div>
    </div>

    <!-- ── RESULT CARD ── -->
    <!-- Success -->
    <Transition name="fade-up">
      <div
        v-if="uploadResult === 'success'"
        class="bg-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm"
      >
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-full bg-emerald-500 flex items-center justify-center shrink-0">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-emerald-800">Template Berhasil Diunggah & Divalidasi!</p>
            <p class="text-xs text-emerald-600">{{ uploadedTemplate?.name }}</p>
          </div>
        </div>

        <!-- Preview thumbnail -->
        <div v-if="uploadedTemplate?.preview_url" class="aspect-video w-full rounded-xl overflow-hidden mb-3 border border-emerald-200 shadow-sm">
          <img :src="uploadedTemplate.preview_url" alt="Preview" class="w-full h-full object-cover"/>
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            @click="resetForm"
            class="flex-1 py-2.5 rounded-xl bg-white border border-emerald-300 text-emerald-700 text-sm font-semibold active:scale-[0.98] transition"
          >
            Upload Lagi
          </button>
          <router-link
            to="/templates"
            class="flex-1 py-2.5 rounded-xl bg-emerald-600 text-white text-sm font-semibold text-center active:scale-[0.98] transition"
          >
            Lihat Semua Template
          </router-link>
        </div>
      </div>
    </Transition>

    <!-- Error -->
    <Transition name="fade-up">
      <div
        v-if="uploadResult === 'error'"
        class="bg-rose-50 border border-rose-200 rounded-2xl p-5 shadow-sm"
      >
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-full bg-rose-500 flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-bold text-rose-800 mb-1">Upload Gagal</p>
            <p class="text-xs text-rose-700 leading-relaxed">{{ serverError }}</p>
          </div>
        </div>
      </div>
    </Transition>

  </div>
</template>

<script setup>
import { ref, reactive } from 'vue';
import api from '../services/api.js';

// ── State ─────────────────────────────────────────────────────────────────────
const fileInput = ref(null);
const selectedFile = ref(null);
const isDragging = ref(false);
const isUploading = ref(false);
const isValidating = ref(false);
const validationReport = ref(null);
const showPlaceholderGuide = ref(false);

const uploadProgress = ref(0);
const uploadResult = ref(null); // null | 'success' | 'error'
const uploadedTemplate = ref(null);
const serverError = ref('');

const form = reactive({
  name: '',
  description: '',
});

const errors = reactive({
  name: '',
  pptx_file: '',
});

// ── Placeholder guide data ─────────────────────────────────────────────────────
const placeholders = [
  { key: '{{NOMOR_LAPORAN}}', desc: 'Nomor laporan otomatis (LAP-BPSDM-2026-0001)' },
  { key: '{{TANGGAL}} / {{HARI_TANGGAL}}', desc: 'Tanggal laporan (Kamis, 8 Oktober 2026)' },
  { key: '{{NAMA_PETUGAS}}', desc: 'Nama petugas pengawas' },
  { key: '{{FOTO_PETUGAS}}', desc: 'Foto petugas (pada cover)' },
  { key: '{{NAMA_LOKASI}}', desc: 'Nama lokasi pantau pada slide lokasi' },
  { key: '{{KONDISI}}', desc: 'Kondisi kebersihan (Bersih / Perlu Penanganan)' },
  { key: '{{CATATAN}}', desc: 'Catatan pemantauan' },
  { key: '{{PERMASALAHAN}}', desc: 'Uraian masalah (jika perlu penanganan)' },
  { key: '{{SOLUSI}}', desc: 'Uraian solusi penanganan' },
  { key: '{{FOTO_1}}', desc: 'Dokumentasi foto pertama lokasi' },
  { key: '{{FOTO_2}}', desc: 'Dokumentasi foto kedua lokasi' },
];

// ── Helpers ───────────────────────────────────────────────────────────────────
const formatBytes = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

const validateForm = () => {
  let valid = true;
  errors.name = '';
  errors.pptx_file = '';

  if (!form.name.trim()) {
    errors.name = 'Nama template wajib diisi.';
    valid = false;
  }

  if (!selectedFile.value) {
    errors.pptx_file = 'Pilih file PPTX terlebih dahulu.';
    valid = false;
  } else if (validationReport.value && !validationReport.value.valid) {
    errors.pptx_file = 'File PPTX belum memenuhi kriteria validasi.';
    valid = false;
  }

  return valid;
};

// ── Inspect file structure & 16:9 ratio in background ─────────────────────────
const inspectFile = async (file) => {
  isValidating.value = true;
  validationReport.value = null;
  errors.pptx_file = '';

  try {
    const formData = new FormData();
    formData.append('pptx_file', file);

    const res = await api.post('/templates/validate', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    });

    if (res.data?.report) {
      validationReport.value = res.data.report;
    }
  } catch (err) {
    if (err.response?.data?.report) {
      validationReport.value = err.response.data.report;
    } else {
      errors.pptx_file = err.response?.data?.message || 'Gagal memvalidasi file PPTX.';
    }
  } finally {
    isValidating.value = false;
  }
};

// ── File selection ────────────────────────────────────────────────────────────
const onFileSelect = (event) => {
  const file = event.target.files?.[0];
  if (file) setFile(file);
};

const onDrop = (event) => {
  isDragging.value = false;
  const file = event.dataTransfer?.files?.[0];
  if (file) setFile(file);
};

const setFile = (file) => {
  errors.pptx_file = '';
  uploadResult.value = null;
  validationReport.value = null;

  const ext = file.name.split('.').pop().toLowerCase();
  if (ext !== 'pptx') {
    errors.pptx_file = 'File harus berekstensi .pptx';
    return;
  }
  if (file.size > 20 * 1024 * 1024) {
    errors.pptx_file = 'Ukuran file PPTX maksimal 20 MB.';
    return;
  }

  selectedFile.value = file;

  // Auto-fill name from filename if empty
  if (!form.name.trim()) {
    form.name = file.name.replace(/\.pptx$/i, '').replace(/[-_]/g, ' ');
  }

  // Run pre-flight inspection
  inspectFile(file);
};

const clearFile = () => {
  selectedFile.value = null;
  validationReport.value = null;
  errors.pptx_file = '';
  if (fileInput.value) fileInput.value.value = '';
};

// ── Submit Upload ─────────────────────────────────────────────────────────────
const submitUpload = async () => {
  if (!validateForm()) return;

  isUploading.value = true;
  uploadProgress.value = 15;
  uploadResult.value = null;
  serverError.value = '';

  try {
    const formData = new FormData();
    formData.append('name', form.name.trim());
    if (form.description.trim()) {
      formData.append('description', form.description.trim());
    }
    formData.append('pptx_file', selectedFile.value);

    const progressInterval = setInterval(() => {
      if (uploadProgress.value < 85) uploadProgress.value += 10;
    }, 300);

    const res = await api.post('/templates', formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
      onUploadProgress: (e) => {
        if (e.total) {
          uploadProgress.value = Math.min(80, Math.round((e.loaded / e.total) * 80));
        }
      },
    });

    clearInterval(progressInterval);
    uploadProgress.value = 100;

    uploadedTemplate.value = res.data.data;
    uploadResult.value = 'success';
  } catch (err) {
    uploadResult.value = 'error';

    if (err.response?.data?.message) {
      serverError.value = err.response.data.message;
    } else if (err.response?.data?.errors) {
      const firstKey = Object.keys(err.response.data.errors)[0];
      serverError.value = err.response.data.errors[firstKey]?.[0] || 'Validasi gagal.';
    } else {
      serverError.value = 'Gagal mengunggah template. Periksa koneksi dan coba lagi.';
    }
  } finally {
    isUploading.value = false;
  }
};

// ── Reset ─────────────────────────────────────────────────────────────────────
const resetForm = () => {
  form.name = '';
  form.description = '';
  selectedFile.value = null;
  validationReport.value = null;
  errors.name = '';
  errors.pptx_file = '';
  uploadResult.value = null;
  uploadedTemplate.value = null;
  serverError.value = '';
  uploadProgress.value = 0;
  if (fileInput.value) fileInput.value.value = '';
};
</script>

<style scoped>
.fade-up-enter-active {
  transition: all 0.3s ease;
}
.fade-up-enter-from {
  opacity: 0;
  transform: translateY(12px);
}
</style>
