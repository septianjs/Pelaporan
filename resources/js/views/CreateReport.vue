<template>
  <div class="px-4 py-4 space-y-4">
    <!-- WIZARD STEP HEADER & PROGRESS -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[11px] font-bold uppercase tracking-wider text-blue-700">
          Langkah {{ currentStep }} dari {{ totalSteps }}
        </span>
        <button
          type="button"
          @click="saveDraftManually"
          class="inline-flex items-center gap-1 text-[11px] font-semibold text-slate-500 hover:text-blue-700 active:scale-95 transition"
        >
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4" />
          </svg>
          Simpan Draft
        </button>
      </div>

      <!-- Horizontal Progress Bar Indicator -->
      <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden mb-3">
        <div
          class="bg-blue-700 h-full transition-all duration-300 rounded-full"
          :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
        ></div>
      </div>

      <!-- Step Title & Description -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-slate-800 leading-tight">
            {{ stepTitles[currentStep - 1] }}
          </h2>
          <p class="text-xs text-slate-500 mt-0.5">
            {{ stepDescriptions[currentStep - 1] }}
          </p>
        </div>
      </div>
    </div>

    <!-- DRAFT RESTORE ALERT (If draft exists in localStorage) -->
    <div
      v-if="hasSavedDraft && !draftRestored"
      class="bg-amber-50 border border-amber-200 rounded-2xl p-3 flex items-center justify-between"
    >
      <div class="flex items-center space-x-2">
        <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
        </svg>
        <span class="text-xs text-amber-800 font-medium">Ada draft laporan yang tersimpan.</span>
      </div>
      <div class="flex items-center space-x-1.5">
        <button
          type="button"
          @click="restoreDraft"
          class="px-2.5 py-1 bg-amber-600 text-white rounded-lg text-xs font-semibold active:scale-95 transition"
        >
          Pulihkan
        </button>
        <button
          type="button"
          @click="discardDraft"
          class="px-2 py-1 text-slate-500 text-xs font-semibold hover:text-slate-700"
        >
          Abaikan
        </button>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEP 1: INFORMASI LAPORAN                                       -->
    <!-- =============================================================== -->
    <div v-show="currentStep === 1" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 space-y-4">
        <!-- Nomor Laporan (Otomatis, Readonly) -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
            Nomor Laporan
          </label>
          <div class="relative">
            <input
              type="text"
              :value="form.report_number || 'LAP-BPSDM-2026-0001'"
              readonly
              class="w-full px-3.5 py-2.5 rounded-xl bg-slate-100 text-slate-600 font-mono text-sm font-semibold border border-slate-200 focus:outline-none cursor-not-allowed"
            />
            <span class="absolute right-3 top-2.5 text-[10px] uppercase font-bold text-slate-400 bg-slate-200/80 px-2 py-0.5 rounded">
              Otomatis
            </span>
          </div>
          <p class="text-[11px] text-slate-400 mt-1">Dibuat otomatis oleh sistem berurutan.</p>
        </div>

        <!-- Tanggal & Hari Otomatis -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
            Tanggal Pemantauan <span class="text-rose-500">*</span>
          </label>
          <input
            type="date"
            v-model="form.inspection_date"
            @change="onDateChange"
            class="w-full px-3.5 py-2.5 rounded-xl bg-white text-slate-800 text-sm font-semibold border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition"
          />
          <!-- Label Hari Otomatis Bahasa Indonesia -->
          <div class="mt-2 p-2.5 rounded-xl bg-blue-50/70 border border-blue-100 flex items-center space-x-2">
            <svg class="w-4 h-4 text-blue-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
            </svg>
            <div class="text-xs text-blue-900">
              <span class="font-bold">Hari:</span> {{ calculatedDayName }}
              <span class="text-slate-400 mx-1">•</span>
              <span class="text-blue-700 font-medium">{{ formattedFullDate }}</span>
            </div>
          </div>
        </div>

        <!-- Nama Petugas -->
        <div>
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide mb-1">
            Nama Petugas Pengawas <span class="text-rose-500">*</span>
          </label>
          <input
            type="text"
            v-model="form.officer_name"
            placeholder="Masukkan nama lengkap petugas..."
            class="w-full px-3.5 py-2.5 rounded-xl bg-white text-slate-800 text-sm font-semibold border border-slate-300 focus:border-blue-500 focus:ring-2 focus:ring-blue-100 transition placeholder:text-slate-400 placeholder:font-normal"
          />
        </div>

        <!-- Foto Petugas -->
        <PhotoUploader
          label="Foto Petugas Pengawas"
          v-model="form.officer_photo"
          aspectRatio="square"
        />
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEPS 2 s/d 6: FORM 5 LOKASI                                    -->
    <!-- =============================================================== -->
    <div
      v-for="(loc, locIdx) in locations"
      :key="loc.id"
      v-show="currentStep === locIdx + 2"
      class="space-y-4"
    >
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 space-y-5">
        <!-- Location Header Badge -->
        <div class="p-3 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-100 rounded-xl flex items-center justify-between">
          <div class="flex items-center space-x-2.5">
            <div class="w-8 h-8 rounded-lg bg-blue-700 text-white font-black text-sm flex items-center justify-center">
              {{ locIdx + 1 }}
            </div>
            <div>
              <span class="text-[10px] uppercase font-bold text-blue-600 tracking-wider">Lokasi {{ locIdx + 1 }}</span>
              <h3 class="text-sm font-black text-slate-800">{{ loc.name }}</h3>
            </div>
          </div>
          <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-blue-200/60 text-blue-800">
            Titik Pantau
          </span>
        </div>

        <!-- DOKUMENTASI 1 -->
        <PhotoUploader
          :label="`Dokumentasi 1 — ${loc.name}`"
          v-model="form.locations[locIdx].photo_1"
          aspectRatio="16:9"
        />

        <!-- DOKUMENTASI 2 -->
        <PhotoUploader
          :label="`Dokumentasi 2 — ${loc.name}`"
          v-model="form.locations[locIdx].photo_2"
          aspectRatio="16:9"
        />

        <!-- KONDISI KEBERSIHAN (Card Radio Buttons) -->
        <div class="space-y-2 pt-1">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            Kondisi Kebersihan Lokasi <span class="text-rose-500">*</span>
          </label>
          <div class="grid grid-cols-1 gap-2.5">
            <!-- Pilihan 1: clean -->
            <label
              :class="[
                'flex items-center space-x-3 p-3.5 rounded-xl border-2 cursor-pointer transition',
                form.locations[locIdx].condition === 'clean'
                  ? 'border-emerald-600 bg-emerald-50/70 shadow-sm'
                  : 'border-slate-200 hover:border-slate-300 bg-white'
              ]"
            >
              <input
                type="radio"
                :name="`cond_${loc.id}`"
                value="clean"
                v-model="form.locations[locIdx].condition"
                class="w-4 h-4 text-emerald-600 focus:ring-emerald-500"
              />
              <div class="flex-1">
                <p class="text-xs font-bold text-slate-800">Kondisi Sudah Bersih dan Rapi</p>
                <p class="text-[11px] text-slate-500">Area bersih, tertata, dan memenuhi standar</p>
              </div>
              <span class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-600 flex items-center justify-center text-xs">
                ✓
              </span>
            </label>

            <!-- Pilihan 2: needs_attention -->
            <label
              :class="[
                'flex items-center space-x-3 p-3.5 rounded-xl border-2 cursor-pointer transition',
                form.locations[locIdx].condition === 'needs_attention'
                  ? 'border-rose-600 bg-rose-50/70 shadow-sm'
                  : 'border-slate-200 hover:border-slate-300 bg-white'
              ]"
            >
              <input
                type="radio"
                :name="`cond_${loc.id}`"
                value="needs_attention"
                v-model="form.locations[locIdx].condition"
                class="w-4 h-4 text-rose-600 focus:ring-rose-500"
              />
              <div class="flex-1">
                <p class="text-xs font-bold text-slate-800">Kondisi Masih Perlu Penanganan Khusus</p>
                <p class="text-[11px] text-slate-500">Terdapat sampah, kotoran, atau kendala fasilitas</p>
              </div>
              <span class="w-6 h-6 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center text-xs font-bold">
                !
              </span>
            </label>
          </div>
        </div>

        <!-- FORM DINAMIS: JIKA CLEAN -> CATATAN PEMANTAUAN -->
        <div v-if="form.locations[locIdx].condition === 'clean'" class="space-y-1 pt-1 transition-all">
          <label class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
            Catatan Pemantauan
          </label>
          <textarea
            v-model="form.locations[locIdx].notes"
            rows="3"
            placeholder="Contoh: Kondisi area sangat bersih dan harum. Semua perlengkapan tersusun rapi."
            class="w-full px-3.5 py-2.5 rounded-xl bg-white text-slate-800 text-sm border border-slate-300 focus:border-emerald-500 focus:ring-2 focus:ring-emerald-100 transition"
          ></textarea>
        </div>

        <!-- FORM DINAMIS: JIKA NEEDS_ATTENTION -> PERMASALAHAN & SOLUSI -->
        <div v-else-if="form.locations[locIdx].condition === 'needs_attention'" class="space-y-3 pt-1 transition-all">
          <div>
            <label class="block text-xs font-bold text-rose-700 uppercase tracking-wide mb-1">
              Permasalahan <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.locations[locIdx].problem"
              rows="2"
              placeholder="Jelaskan permasalahan atau bagian yang kotor/rusak..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-white text-slate-800 text-sm border border-rose-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-100 transition"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-amber-700 uppercase tracking-wide mb-1">
              Solusi / Tindak Lanjut <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.locations[locIdx].solution"
              rows="2"
              placeholder="Jelaskan tindakan pembersihan atau perbaikan yang dilakukan..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-white text-slate-800 text-sm border border-amber-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-100 transition"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEP 7: PILIH TEMPLATE                                          -->
    <!-- =============================================================== -->
    <div v-show="currentStep === 7" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
        <h3 class="text-sm font-bold text-slate-800 mb-1">
          Pilih Template Presentasi Widescreen 16:9
        </h3>
        <p class="text-xs text-slate-500 mb-4">
          Laporan akan di-generate menggunakan template PowerPoint 6 halaman ini.
        </p>

        <div class="space-y-3">
          <div
            v-for="tpl in templates"
            :key="tpl.id"
            @click="form.template_id = tpl.id"
            :class="[
              'p-3.5 rounded-2xl border-2 cursor-pointer transition flex flex-col space-y-2.5',
              form.template_id === tpl.id
                ? 'border-blue-700 bg-blue-50/60 shadow-md ring-2 ring-blue-200'
                : 'border-slate-200 hover:border-slate-300 bg-white'
            ]"
          >
            <!-- 16:9 Thumbnail -->
            <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-slate-100 border border-slate-200">
              <img
                v-if="tpl.preview_url"
                :src="tpl.preview_url"
                :alt="tpl.name"
                class="w-full h-full object-cover"
              />
              <div v-else class="w-full h-full flex items-center justify-center text-xs text-slate-400">
                Preview Template
              </div>

              <div
                v-if="form.template_id === tpl.id"
                class="absolute top-2 right-2 px-2.5 py-1 rounded-full bg-blue-700 text-white text-xs font-bold shadow flex items-center gap-1"
              >
                <span>Dipilih</span>
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7" />
                </svg>
              </div>
            </div>

            <div class="flex items-center justify-between">
              <div>
                <h4 class="text-sm font-bold text-slate-800">{{ tpl.name }}</h4>
                <p class="text-[11px] text-slate-500">{{ tpl.description }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEP 8: PREVIEW & REVIEW SEBELUM SUBMIT                         -->
    <!-- =============================================================== -->
    <div v-show="currentStep === 8" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 space-y-4">
        <div class="border-b border-slate-100 pb-3 flex items-center justify-between">
          <div>
            <h3 class="text-base font-black text-slate-800">Ringkasan Laporan</h3>
            <p class="text-xs text-slate-500">Periksa seluruh data sebelum menyimpan</p>
          </div>
          <span class="px-2.5 py-1 rounded-full bg-blue-100 text-blue-800 text-xs font-bold font-mono">
            {{ form.report_number }}
          </span>
        </div>

        <!-- Info Petugas & Tanggal -->
        <div class="p-3 bg-slate-50 rounded-xl space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-slate-500">Hari, Tanggal:</span>
            <span class="font-bold text-slate-800">{{ calculatedDayName }}, {{ form.inspection_date }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Petugas:</span>
            <span class="font-bold text-slate-800">{{ form.officer_name || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-slate-500">Template Terpilih:</span>
            <span class="font-bold text-blue-700">{{ selectedTemplateName }}</span>
          </div>
        </div>

        <!-- 5 Lokasi Status Checklist -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-slate-600">
            Status 5 Lokasi Pantau:
          </h4>
          <div
            v-for="(loc, idx) in locations"
            :key="loc.id"
            class="p-2.5 rounded-xl border border-slate-200 flex items-center justify-between text-xs"
          >
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-md bg-slate-100 text-slate-700 font-bold flex items-center justify-center text-[10px]">
                {{ idx + 1 }}
              </span>
              <span class="font-medium text-slate-800">{{ loc.name }}</span>
            </div>
            <span
              :class="[
                'px-2 py-0.5 rounded-full font-semibold text-[10px]',
                form.locations[idx].condition === 'clean'
                  ? 'bg-emerald-100 text-emerald-800'
                  : 'bg-rose-100 text-rose-800'
              ]"
            >
              {{ form.locations[idx].condition === 'clean' ? 'Bersih & Rapi' : 'Perlu Penanganan' }}
            </span>
          </div>
        </div>

        <!-- Simpan & Selesai Banner -->
        <div class="p-3 rounded-xl bg-blue-50 border border-blue-100 text-xs text-blue-800">
          Setelah laporan disimpan, sistem siap melakukan pratinjau dan generate PDF 16:9.
        </div>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- WIZARD NAVIGATION BUTTONS (STICKY BOTTOM CONTROLS)               -->
    <!-- =============================================================== -->
    <div class="sticky bottom-18 z-20 bg-white/95 backdrop-blur-md p-3 rounded-2xl shadow-lg border border-slate-200 flex items-center gap-2">
      <!-- Tombol Kembali -->
      <button
        v-if="currentStep > 1"
        type="button"
        @click="prevStep"
        class="flex-1 py-2.5 px-4 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-700 text-xs font-bold active:scale-95 transition"
      >
        ← Kembali
      </button>

      <!-- Tombol Lanjut -->
      <button
        v-if="currentStep < totalSteps"
        type="button"
        @click="nextStep"
        class="flex-[2] py-2.5 px-4 rounded-xl bg-blue-700 hover:bg-blue-800 text-white text-xs font-bold shadow-md shadow-blue-700/20 active:scale-95 transition flex items-center justify-center gap-1"
      >
        <span>Lanjut</span>
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
        </svg>
      </button>

      <!-- Tombol Simpan Laporan (Step 8) -->
      <button
        v-if="currentStep === totalSteps"
        type="button"
        :disabled="isSubmitting"
        @click="submitReport"
        class="flex-[2] py-2.5 px-4 rounded-xl bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-700 hover:to-teal-700 text-white text-xs font-bold shadow-md shadow-emerald-700/20 active:scale-95 transition disabled:opacity-50 flex items-center justify-center gap-1.5"
      >
        <span v-if="isSubmitting" class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></span>
        <span>{{ isSubmitting ? 'Menyimpan...' : 'Simpan & Buat Laporan' }}</span>
      </button>
    </div>

    <!-- ── OFFLINE QUEUE SUCCESS MODAL ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showOfflineSuccessModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" />
      </Transition>
      <Transition name="slide-up">
        <div v-if="showOfflineSuccessModal"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl p-6 space-y-4"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(2rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center -mt-2 mb-2">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>
          <div class="w-14 h-14 rounded-full bg-amber-100 text-amber-600 flex items-center justify-center mx-auto">
            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
          </div>
          <div class="text-center space-y-1">
            <h3 class="text-base font-bold text-slate-800">Laporan Masuk Antrian Offline</h3>
            <p class="text-xs text-slate-500 leading-relaxed">
              Perangkat sedang tidak terhubung internet. Seluruh data laporan dan foto dokumentasi telah diamankan di penyimpanan lokal perangkat dan akan <strong>otomatis disinkronkan ke server</strong> begitu koneksi internet terhubung.
            </p>
          </div>
          <div class="pt-2">
            <button
              type="button"
              @click="router.push('/history')"
              class="w-full py-3.5 rounded-2xl bg-amber-600 text-white font-bold text-sm shadow-md active:scale-95 transition"
            >
              Lihat Riwayat &amp; Antrian
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import { useRouter, useRoute } from 'vue-router';
import api from '../services/api';
import PhotoUploader from '../components/PhotoUploader.vue';
import {
  saveFormDraft,
  getFormDraft,
  clearFormDraft,
  cacheMasterData,
  getCachedMasterData,
  queueOfflineReport,
  isOnline,
} from '../services/offlineStorage.js';

const router = useRouter();
const route = useRoute();

const currentStep = ref(1);
const totalSteps = 8;
const isSubmitting = ref(false);
const hasSavedDraft = ref(false);
const draftRestored = ref(false);
const showOfflineSuccessModal = ref(false);

const stepTitles = [
  '1. Informasi Laporan',
  '2. Lokasi 1 — Halaman Depan',
  '3. Lokasi 2 — Ruang Pelayanan',
  '4. Lokasi 3 — Ruang Kerja',
  '5. Lokasi 4 — Toilet',
  '6. Lokasi 5 — Area Parkir',
  '7. Pemilihan Template',
  '8. Pratinjau & Konfirmasi',
];

const stepDescriptions = [
  'Nomor laporan, tanggal & hari otomatis, identitas petugas.',
  'Dokumentasi dan evaluasi kebersihan Halaman Depan Kantor.',
  'Dokumentasi dan evaluasi kebersihan Ruang Pelayanan.',
  'Dokumentasi dan evaluasi kebersihan Ruang Kerja.',
  'Dokumentasi dan evaluasi kebersihan Toilet.',
  'Dokumentasi dan evaluasi kebersihan Area Parkir.',
  'Pilih 1 dari 10 template presentasi widescreen 16:9.',
  'Tinjau kembali data laporan sebelum penyimpanan final.',
];

const locations = ref([
  { id: 1, name: 'Halaman Depan Kantor' },
  { id: 2, name: 'Ruang Pelayanan' },
  { id: 3, name: 'Ruang Kerja' },
  { id: 4, name: 'Toilet' },
  { id: 5, name: 'Area Parkir' },
]);

const templates = ref([]);

// Today's date string YYYY-MM-DD
const todayIso = () => {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const form = reactive({
  report_number: 'LAP-BPSDM-2026-0001',
  inspection_date: todayIso(),
  officer_name: '',
  officer_photo: null,
  template_id: 1,
  locations: [
    { location_id: 1, condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 2, condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 3, condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 4, condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 5, condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
  ],
});

// Hitung hari otomatis berdasarkan tanggal Bahasa Indonesia (Section 10)
const calculatedDayName = computed(() => {
  if (!form.inspection_date) return '';
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const d = new Date(form.inspection_date + 'T00:00:00');
  return days[d.getDay()] || '';
});

const formattedFullDate = computed(() => {
  if (!form.inspection_date) return '';
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const d = new Date(form.inspection_date + 'T00:00:00');
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
});

const selectedTemplateName = computed(() => {
  const tpl = templates.value.find(t => t.id === form.template_id);
  return tpl ? tpl.name : 'Corporate Blue';
});

// Fetch Initial Data
const loadInitialData = async () => {
  try {
    // 1. Fetch next report number if online
    if (isOnline.value) {
      try {
        const numRes = await api.get('/reports/next-number');
        if (numRes.data?.report_number) {
          form.report_number = numRes.data.report_number;
        }
      } catch {}
    } else {
      form.report_number = `LAP-BPSDM-${new Date().getFullYear()}-OFFLINE`;
    }

    // 2. Fetch locations (with IndexedDB cache fallback)
    try {
      const locRes = await api.get('/locations');
      if (locRes.data?.data && locRes.data.data.length > 0) {
        locations.value = locRes.data.data;
        await cacheMasterData('locations', locRes.data.data);
      }
    } catch {
      const cachedLocs = await getCachedMasterData('locations');
      if (cachedLocs && cachedLocs.length > 0) {
        locations.value = cachedLocs;
      }
    }

    // Sync locations structure
    form.locations = locations.value.map(loc => ({
      location_id: loc.id,
      condition: 'clean',
      notes: '',
      problem: '',
      solution: '',
      photo_1: null,
      photo_2: null,
    }));

    // 3. Fetch templates (with IndexedDB cache fallback)
    try {
      const tplRes = await api.get('/templates');
      if (tplRes.data?.data && tplRes.data.data.length > 0) {
        templates.value = tplRes.data.data;
        await cacheMasterData('templates', tplRes.data.data);
      }
    } catch {
      const cachedTpls = await getCachedMasterData('templates');
      if (cachedTpls && cachedTpls.length > 0) {
        templates.value = cachedTpls;
      }
    }

    if (route.query.template_id) {
      form.template_id = parseInt(route.query.template_id);
    } else if (templates.value.length > 0) {
      form.template_id = templates.value[0].id;
    }
  } catch (err) {
    console.warn('Initial load warning:', err.message);
  }

  // Check saved draft from IndexedDB
  await checkSavedDraft();
};

const checkSavedDraft = async () => {
  const draft = await getFormDraft();
  if (draft) {
    hasSavedDraft.value = true;
  }
};

const saveDraftManually = async () => {
  const success = await saveFormDraft(form);
  if (success) {
    alert('✓ Draft laporan dan seluruh foto dokumentasi berhasil disimpan di perangkat lokal.');
  } else {
    alert('Draft berhasil disimpan.');
  }
};

const restoreDraft = async () => {
  const draft = await getFormDraft();
  if (draft) {
    form.report_number = draft.report_number || form.report_number;
    form.inspection_date = draft.inspection_date || form.inspection_date;
    form.officer_name = draft.officer_name || '';
    form.officer_photo = draft.officer_photo || null;
    form.template_id = draft.template_id || form.template_id;

    if (Array.isArray(draft.locations)) {
      draft.locations.forEach((dLoc, i) => {
        if (form.locations[i]) {
          form.locations[i].condition = dLoc.condition || 'clean';
          form.locations[i].notes = dLoc.notes || '';
          form.locations[i].problem = dLoc.problem || '';
          form.locations[i].solution = dLoc.solution || '';
          form.locations[i].photo_1 = dLoc.photo_1 || null;
          form.locations[i].photo_2 = dLoc.photo_2 || null;
        }
      });
    }
    draftRestored.value = true;
    hasSavedDraft.value = false;
  }
};

const discardDraft = async () => {
  await clearFormDraft();
  hasSavedDraft.value = false;
};

// Wizard Navigation
const nextStep = () => {
  if (currentStep.value === 1) {
    if (!form.officer_name.trim()) {
      alert('Mohon masukkan nama petugas pengawas.');
      return;
    }
  } else if (currentStep.value >= 2 && currentStep.value <= 6) {
    const locIdx = currentStep.value - 2;
    const locItem = form.locations[locIdx];
    if (locItem.condition === 'needs_attention') {
      if (!locItem.problem?.trim() || !locItem.solution?.trim()) {
        alert('Untuk kondisi perlu penanganan, mohon isi permasalahan dan solusinya.');
        return;
      }
    }
  }

  // Auto-save draft on step progression
  saveFormDraft(form);

  if (currentStep.value < totalSteps) {
    currentStep.value++;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    window.scrollTo({ top: 0, behavior: 'smooth' });
  }
};

// Submit Report (Online or Offline Queue)
const submitReport = async () => {
  isSubmitting.value = true;

  // 1. If currently offline, queue immediately
  if (!isOnline.value || !navigator.onLine) {
    try {
      await queueOfflineReport(form);
      await clearFormDraft();
      showOfflineSuccessModal.value = true;
    } catch (err) {
      alert('Gagal menyimpan ke antrian offline: ' + err.message);
    } finally {
      isSubmitting.value = false;
    }
    return;
  }

  // 2. If online, attempt API submission
  try {
    const formData = new FormData();
    formData.append('report_number', form.report_number);
    formData.append('inspection_date', form.inspection_date);
    formData.append('officer_name', form.officer_name);
    formData.append('template_id', form.template_id);

    if (form.officer_photo instanceof Blob || form.officer_photo instanceof File) {
      formData.append('officer_photo', form.officer_photo, 'officer.jpg');
    }

    form.locations.forEach((loc, idx) => {
      formData.append(`locations[${idx}][location_id]`, loc.location_id);
      formData.append(`locations[${idx}][condition]`, loc.condition);
      if (loc.condition === 'clean') {
        formData.append(`locations[${idx}][notes]`, loc.notes || '');
      } else {
        formData.append(`locations[${idx}][problem]`, loc.problem || '');
        formData.append(`locations[${idx}][solution]`, loc.solution || '');
      }

      if (loc.photo_1 instanceof Blob || loc.photo_1 instanceof File) {
        formData.append(`locations.${idx}.photo_1`, loc.photo_1, `loc_${loc.location_id}_p1.jpg`);
      }
      if (loc.photo_2 instanceof Blob || loc.photo_2 instanceof File) {
        formData.append(`locations.${idx}.photo_2`, loc.photo_2, `loc_${loc.location_id}_p2.jpg`);
      }
    });

    const res = await api.post('/reports', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data && res.data.status === 'success') {
      await clearFormDraft();
      const newReportId = res.data.data.id;
      alert('Laporan berhasil dibuat!');
      router.push(`/preview/${newReportId}`);
    }
  } catch (err) {
    console.error('Error saat menyimpan laporan:', err);
    const isNetworkError = !err.response || err.message === 'Network Error' || !navigator.onLine;

    if (isNetworkError) {
      // Graceful fallback to offline queue on network drop
      try {
        await queueOfflineReport(form);
        await clearFormDraft();
        showOfflineSuccessModal.value = true;
      } catch (qErr) {
        alert('Koneksi terputus dan gagal menyimpan offline: ' + qErr.message);
      }
    } else {
      const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan sistem.';
      alert('Gagal menyimpan laporan: ' + msg);
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadInitialData();
});
</script>

<style scoped>
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
