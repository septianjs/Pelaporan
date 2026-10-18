<template>
  <div class="px-4 py-5 space-y-5 pb-24">

    <!-- ── HEADER ── -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm">
      <div class="flex items-center gap-3 mb-2">
        <div class="w-10 h-10 rounded-xl bg-[#14251D] text-[#D8C79C] flex items-center justify-center shrink-0">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
          </svg>
        </div>
        <div>
          <h2 class="text-base font-bold text-[#14251D]">Unggah & Validasi Template PPTX</h2>
          <p class="text-xs text-[#6F7771]">Standar PowerPoint Widescreen 16:9 (13.333 × 7.5")</p>
        </div>
      </div>

      <!-- Info box -->
      <div class="bg-[#FAF2DF] border border-[#EADBBD] rounded-xl p-3.5 space-y-1.5 mt-3">
        <p class="text-xs font-bold text-[#6B5A2E] flex items-center gap-1.5">
          <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          Spesifikasi Standar Template PPTX
        </p>
        <ul class="text-xs text-[#6B5A2E] space-y-1 list-disc list-inside">
          <li>Format berkas: <strong>.pptx</strong> (PowerPoint 2007+)</li>
          <li>Rasio aspek slide: <strong>16:9 Widescreen</strong> (13.333 × 7.5 inci)</li>
          <li>Komposisi: <strong>Minimal 6 slide</strong> (1 Cover Laporan + 5 Titik Pemantauan)</li>
          <li>Ukuran maksimal berkas: <strong>20 MB</strong></li>
        </ul>
        <p class="text-[11px] text-[#8C6D1F] mt-1 font-mono">
          Menu PowerPoint: Design → Slide Size → Widescreen (16:9)
        </p>
      </div>
    </div>

    <!-- ── PLACEHOLDER GUIDE ── -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm">
      <div class="flex items-center justify-between cursor-pointer" @click="showPlaceholderGuide = !showPlaceholderGuide">
        <h3 class="text-sm font-bold text-[#14251D] flex items-center gap-2">
          <svg class="w-4 h-4 text-[#14251D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          Daftar Placeholder Resmi Sistem
        </h3>
        <span class="text-xs text-[#14251D] font-semibold flex items-center gap-1 active-press">
          {{ showPlaceholderGuide ? 'Sembunyikan' : 'Buka Panduan' }}
          <svg class="w-3.5 h-3.5 transform transition-transform" :class="{ 'rotate-180': showPlaceholderGuide }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
          </svg>
        </span>
      </div>

      <div v-if="showPlaceholderGuide" class="space-y-2 mt-4 pt-3 border-t border-[#ECE8DD]">
        <div v-for="ph in placeholders" :key="ph.key"
          class="flex items-start gap-3 p-2.5 rounded-xl bg-white border border-[#DCD7CA]">
          <code class="text-[11px] font-mono font-bold text-[#14251D] bg-[#ECE8DD] px-2 py-0.5 rounded border border-[#DCD7CA] shrink-0">
            {{ ph.key }}
          </code>
          <span class="text-xs text-[#59635D]">{{ ph.desc }}</span>
        </div>
      </div>
    </div>

    <!-- ── UPLOAD FORM ── -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm space-y-4">
      <h3 class="text-sm font-bold text-[#14251D]">Pilih Berkas PPTX</h3>

      <!-- File Picker PPTX -->
      <div class="space-y-1.5">
        <div
          @click="fileInput?.click()"
          @dragover.prevent="isDragging = true"
          @dragleave.prevent="isDragging = false"
          @drop.prevent="onDrop"
          :class="[
            'relative border-2 border-dashed rounded-2xl p-6 text-center cursor-pointer transition',
            isDragging ? 'border-[#14251D] bg-[#EAF3EC]' : 'border-[#DCD7CA] bg-white hover:bg-[#F4F3EE]',
            errors.pptx_file ? 'border-[#F5C7C1] bg-[#FDF0EE]' : '',
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
            <div class="w-12 h-12 mx-auto rounded-xl bg-[#ECE8DD] text-[#14251D] border border-[#DCD7CA] flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
              </svg>
            </div>
            <div>
              <p class="text-sm font-semibold text-[#14251D]">Ketuk untuk memilih berkas PPTX</p>
              <p class="text-[11px] text-[#808983] mt-0.5">atau seret dan lepaskan dokumen di sini</p>
              <p class="text-[11px] text-[#6F7771] mt-1 font-mono">Format .pptx · Batas maksimum 20 MB</p>
            </div>
          </div>

          <!-- File selected -->
          <div v-else class="flex items-center gap-3 text-left">
            <div class="w-12 h-12 rounded-xl bg-[#14251D] text-[#D8C79C] flex items-center justify-center shrink-0">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
              </svg>
            </div>
            <div class="flex-1 min-w-0">
              <p class="text-sm font-bold text-[#14251D] truncate">{{ selectedFile.name }}</p>
              <p class="text-xs text-[#808983] font-mono mt-0.5">{{ formatBytes(selectedFile.size) }}</p>
              <p v-if="isValidating" class="text-[11px] text-[#14251D] font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                Memeriksa struktur & rasio slide…
              </p>
              <p v-else-if="validationReport?.valid" class="text-[11px] text-[#1A3428] font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                Template valid (16:9 Widescreen • {{ validationReport.details?.slide_count }} Slide)
              </p>
              <p v-else-if="validationReport && !validationReport.valid" class="text-[11px] text-[#A6382C] font-semibold mt-0.5 flex items-center gap-1">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                Template tidak memenuhi syarat validasi
              </p>
            </div>
            <button
              type="button"
              @click.stop="clearFile"
              class="w-8 h-8 rounded-xl bg-[#ECE8DD] hover:bg-[#FDF0EE] text-[#6F7771] hover:text-[#A6382C] flex items-center justify-center transition shrink-0 border border-[#DCD7CA]"
            >
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
              </svg>
            </button>
          </div>
        </div>

        <p v-if="errors.pptx_file" class="text-[11px] text-[#A6382C] flex items-center gap-1">
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
          :class="validationReport.valid ? 'bg-[#EAF3EC] border-[#C8E0D0]' : 'bg-[#FDF0EE] border-[#F5C7C1]'">
          
          <div class="flex items-center justify-between">
            <div class="flex items-center gap-2">
              <span class="w-6 h-6 rounded-full flex items-center justify-center text-xs text-white"
                :class="validationReport.valid ? 'bg-[#1A3428]' : 'bg-[#A6382C]'">
                {{ validationReport.valid ? '✓' : '!' }}
              </span>
              <p class="text-xs font-bold" :class="validationReport.valid ? 'text-[#1A3428]' : 'text-[#A6382C]'">
                {{ validationReport.valid ? 'Hasil Validasi: Template Siap Digunakan' : 'Hasil Validasi: Template Ditolak' }}
              </p>
            </div>
            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full border"
              :class="validationReport.valid ? 'bg-white text-[#1A3428] border-[#C8E0D0]' : 'bg-white text-[#A6382C] border-[#F5C7C1]'">
              {{ validationReport.valid ? 'VALID 16:9' : 'TIDAK VALID' }}
            </span>
          </div>

          <!-- Parameter grid -->
          <div class="grid grid-cols-2 gap-2 text-xs">
            <div class="bg-white rounded-xl p-2.5 border border-[#DCD7CA]">
              <span class="text-[10px] text-[#808983] block">Dimensi & Rasio Slide</span>
              <p class="font-bold text-[#14251D] mt-0.5 font-mono">
                {{ validationReport.details?.width_inch }}" × {{ validationReport.details?.height_inch }}"
                <span class="text-[10px] font-semibold text-[#1A3428] ml-1">
                  ({{ validationReport.details?.aspect_ratio }}:1)
                </span>
              </p>
            </div>
            <div class="bg-white rounded-xl p-2.5 border border-[#DCD7CA]">
              <span class="text-[10px] text-[#808983] block">Jumlah Slide</span>
              <p class="font-bold text-[#14251D] mt-0.5 font-mono">
                {{ validationReport.details?.slide_count }} Slide
                <span v-if="validationReport.details?.slide_count >= 6" class="text-[10px] text-[#1A3428] font-semibold ml-1">✓ Sesuai</span>
                <span v-else class="text-[10px] text-[#A6382C] font-semibold ml-1">✕ Kurang (min 6)</span>
              </p>
            </div>
          </div>

          <!-- Errors list (if any) -->
          <div v-if="validationReport.errors?.length" class="space-y-1">
            <p class="text-[11px] font-bold text-[#A6382C]">Masalah yang Perlu Diperbaiki:</p>
            <ul class="text-xs text-[#A6382C] list-disc list-inside space-y-1">
              <li v-for="(err, idx) in validationReport.errors" :key="idx">{{ err }}</li>
            </ul>
          </div>

          <!-- Warnings list (if any) -->
          <div v-if="validationReport.warnings?.length" class="space-y-1">
            <p class="text-[11px] font-bold text-[#6B5A2E]">Catatan Penyesuaian:</p>
            <ul class="text-xs text-[#6B5A2E] list-disc list-inside space-y-1">
              <li v-for="(warn, idx) in validationReport.warnings" :key="idx">{{ warn }}</li>
            </ul>
          </div>

          <!-- Detected Placeholders Badges -->
          <div v-if="validationReport.details?.all_placeholders?.length" class="space-y-1.5 pt-1">
            <p class="text-[11px] font-bold text-[#14251D]">
              Placeholder Terdeteksi ({{ validationReport.details.all_placeholders.length }}):
            </p>
            <div class="flex flex-wrap gap-1">
              <span
                v-for="ph in validationReport.details.all_placeholders"
                :key="ph"
                class="px-2 py-0.5 bg-white border border-[#DCD7CA] text-[#14251D] text-[10px] font-mono font-semibold rounded-md"
              >
                {{ ph }}
              </span>
            </div>
          </div>
        </div>
      </Transition>

      <!-- Nama Template -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-[#14251D]">
          Nama Template <span class="text-[#A6382C]">*</span>
        </label>
        <input
          v-model="form.name"
          type="text"
          placeholder="Contoh: Template Hijau Resmi BPSDM"
          maxlength="100"
          class="w-full px-4 py-3 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder:text-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D] transition font-medium"
          :class="{ 'border-[#F5C7C1] ring-1 ring-[#F5C7C1]': errors.name }"
        />
        <p v-if="errors.name" class="text-[11px] text-[#A6382C]">{{ errors.name }}</p>
      </div>

      <!-- Deskripsi -->
      <div class="space-y-1.5">
        <label class="block text-xs font-semibold text-[#14251D]">
          Deskripsi Format <span class="text-[#808983] font-normal">(opsional)</span>
        </label>
        <textarea
          v-model="form.description"
          placeholder="Tuliskan keterangan singkat mengenai peruntukan template ini…"
          rows="2"
          maxlength="500"
          class="w-full px-4 py-3 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder:text-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D] transition resize-none"
        />
      </div>
    </div>

    <!-- ── UPLOAD BUTTON ── -->
    <button
      type="button"
      @click="submitUpload"
      :disabled="isUploading || isValidating || (validationReport && !validationReport.valid)"
      class="w-full flex items-center justify-center gap-2 px-5 py-4 rounded-2xl bg-[#14251D] text-[#FAF9F5] font-bold text-sm shadow-sm hover:bg-[#1C362A] active-press transition disabled:opacity-50 disabled:cursor-not-allowed"
    >
      <svg v-if="isUploading" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <svg v-else class="w-5 h-5 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
      </svg>
      <span>{{ isUploading ? 'Mengunggah Template…' : (validationReport && !validationReport.valid ? 'Perbaiki Berkas Sebelum Unggah' : 'Unggah & Simpan Template') }}</span>
    </button>

    <!-- Upload progress bar -->
    <div v-if="isUploading" class="bg-[#FAF9F5] rounded-2xl p-4 border border-[#E3E0D6] shadow-sm">
      <div class="flex items-center justify-between text-xs font-semibold text-[#14251D] mb-2 font-mono">
        <span>Menyimpan template & memproses thumbnail preview…</span>
        <span>{{ uploadProgress }}%</span>
      </div>
      <div class="h-2 bg-[#ECE8DD] rounded-full overflow-hidden">
        <div
          class="h-full bg-[#14251D] rounded-full transition-all duration-300"
          :style="{ width: uploadProgress + '%' }"
        />
      </div>
    </div>

    <!-- ── RESULT CARD ── -->
    <!-- Success -->
    <Transition name="fade-up">
      <div
        v-if="uploadResult === 'success'"
        class="bg-[#EAF3EC] border border-[#C8E0D0] rounded-2xl p-5 shadow-sm"
      >
        <div class="flex items-center gap-3 mb-3">
          <div class="w-10 h-10 rounded-xl bg-[#1A3428] text-[#D8C79C] flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div>
            <p class="text-sm font-bold text-[#1A3428]">Template Berhasil Diunggah & Divalidasi!</p>
            <p class="text-xs text-[#59635D]">{{ uploadedTemplate?.name }}</p>
          </div>
        </div>

        <!-- Preview thumbnail -->
        <div v-if="uploadedTemplate?.preview_url" class="aspect-video w-full rounded-xl overflow-hidden mb-3 border border-[#C8E0D0] shadow-inner">
          <img :src="uploadedTemplate.preview_url" alt="Preview" class="w-full h-full object-cover"/>
        </div>

        <div class="flex gap-2">
          <button
            type="button"
            @click="resetForm"
            class="flex-1 py-2.5 rounded-xl bg-white border border-[#DCD7CA] text-[#14251D] text-xs font-semibold active-press transition"
          >
            Unggah Berkas Lain
          </button>
          <router-link
            to="/templates"
            class="flex-1 py-2.5 rounded-xl bg-[#14251D] text-[#FAF9F5] text-xs font-semibold text-center hover:bg-[#1C362A] active-press transition"
          >
            Lihat Katalog Template
          </router-link>
        </div>
      </div>
    </Transition>

    <!-- Error -->
    <Transition name="fade-up">
      <div
        v-if="uploadResult === 'error'"
        class="bg-[#FDF0EE] border border-[#F5C7C1] rounded-2xl p-5 shadow-sm"
      >
        <div class="flex items-start gap-3">
          <div class="w-9 h-9 rounded-xl bg-[#A6382C] text-white flex items-center justify-center shrink-0 mt-0.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
          </div>
          <div class="flex-1">
            <p class="text-sm font-bold text-[#A6382C] mb-1">Unggah Gagal</p>
            <p class="text-xs text-[#8F2F24] leading-relaxed">{{ serverError }}</p>
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
