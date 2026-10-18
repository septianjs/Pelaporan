<template>
  <div class="px-4 py-5 space-y-4 pb-24">
    <!-- ── HEADER CARD ── -->
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-lg font-bold text-slate-800">Daftar Template</h2>
            <p class="text-xs text-slate-500">PowerPoint Widescreen 16:9 (13.333 × 7.5")</p>
          </div>
        </div>
      </div>

      <p class="text-xs text-slate-600 leading-relaxed mb-4">
        Seluruh template memiliki struktur 6 halaman (Cover + 5 Lokasi Pantau) dengan tata letak visual profesional.
      </p>

      <router-link
        to="/templates/upload"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-gradient-to-r from-indigo-600 to-blue-600 text-white rounded-xl text-sm font-semibold shadow-sm hover:from-indigo-700 hover:to-blue-700 active:scale-[0.98] transition"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span>Upload Template PPTX Sendiri</span>
      </router-link>
    </div>

    <!-- ── SECTION TITLE ── -->
    <div class="flex items-center justify-between px-1 pt-1">
      <h3 class="text-xs font-bold uppercase tracking-wider text-slate-500">
        Semua Template ({{ templates.length }})
      </h3>
      <span class="text-[11px] text-blue-600 font-medium">100% 16:9 Widescreen</span>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i"
        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 animate-pulse space-y-3">
        <div class="h-36 bg-slate-200 rounded-xl"/>
        <div class="h-4 bg-slate-200 rounded w-1/2"/>
        <div class="h-3 bg-slate-200 rounded w-3/4"/>
      </div>
    </div>

    <!-- ── GAGAL MEMUAT (state error eksplisit, bukan daftar kosong diam-diam) ── -->
    <div v-else-if="loadError" class="bg-white rounded-2xl p-6 shadow-sm border border-slate-200 text-center space-y-3">
      <div class="w-12 h-12 rounded-full bg-rose-50 text-rose-500 flex items-center justify-center mx-auto">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-slate-800">Daftar template tidak bisa dimuat</p>
        <p class="text-xs text-slate-500 mt-1">Periksa koneksi internet Anda, lalu coba lagi.</p>
      </div>
      <button
        type="button"
        @click="fetchTemplates"
        class="px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-bold active:scale-95 transition"
      >
        Coba Lagi
      </button>
    </div>

    <!-- ── TEMPLATE CARDS ── -->
    <div v-else class="space-y-4">
      <div
        v-for="tpl in templates"
        :key="tpl.id"
        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 hover:border-blue-300 transition overflow-hidden"
      >
        <!-- 16:9 Preview Thumbnail -->
        <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-slate-100 border border-slate-200 mb-3 shadow-inner">
          <img
            v-if="tpl.preview_url"
            :src="tpl.preview_url"
            :alt="tpl.name"
            class="w-full h-full object-cover"
            loading="lazy"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-xs text-slate-400">
            Preview Tidak Tersedia
          </div>

          <!-- Type badge -->
          <div class="absolute top-2 left-2 px-2 py-0.5 rounded-md backdrop-blur-md text-white text-[10px] font-semibold tracking-wide"
            :class="tpl.type === 'builtin' ? 'bg-blue-900/80' : 'bg-orange-600/90'">
            {{ tpl.type === 'builtin' ? 'TEMPLATE BAWAAN' : 'TEMPLATE UPLOAD' }}
          </div>

          <!-- 16:9 tag -->
          <div class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-black/60 backdrop-blur-md text-amber-300 text-[10px] font-bold">
            16:9 Widescreen
          </div>
        </div>

        <!-- Template meta -->
        <div>
          <div class="flex items-center justify-between">
            <h4 class="text-base font-bold text-slate-800">{{ tpl.name }}</h4>
            <span class="text-[10px] font-semibold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full border border-emerald-200">
              Siap Digunakan
            </span>
          </div>

          <p class="text-xs text-slate-500 mt-1 leading-relaxed">
            {{ tpl.description || 'Template pelaporan pengawasan lingkungan kantor BPSDM.' }}
          </p>

          <!-- Actions row -->
          <div class="mt-3 pt-3 border-t border-slate-100 flex items-center justify-between text-[11px] text-slate-400">
            <span>6 Slide • {{ tpl.template_key }}.pptx</span>
            <div class="flex items-center gap-3">
              <!-- Delete (only uploaded templates) -->
              <button
                v-if="tpl.type === 'uploaded'"
                type="button"
                @click="confirmDelete(tpl)"
                class="inline-flex items-center gap-1 text-rose-500 hover:text-rose-700 font-semibold transition"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>

              <!-- Use template -->
              <router-link
                :to="{ path: '/create', query: { template_id: tpl.id } }"
                class="inline-flex items-center gap-1 font-semibold text-blue-600 hover:text-blue-800 transition"
              >
                Gunakan
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
              </router-link>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- ── DELETE CONFIRMATION BOTTOM SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showDeleteSheet"
          class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
          @click="showDeleteSheet = false"
        />
      </Transition>

      <Transition name="slide-up">
        <div
          v-if="showDeleteSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));"
        >
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>

          <div class="px-5 pt-2 pb-4 space-y-4">
            <!-- Trash icon -->
            <div class="w-14 h-14 rounded-full bg-rose-100 mx-auto flex items-center justify-center">
              <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>

            <div class="text-center">
              <p class="text-base font-bold text-slate-800">Hapus Template?</p>
              <p class="text-sm text-slate-500 mt-1">
                "<strong>{{ templateToDelete?.name }}</strong>" akan dihapus permanen dan tidak dapat dikembalikan.
              </p>
            </div>

            <div class="space-y-2">
              <button
                type="button"
                :disabled="isDeleting"
                @click="deleteTemplate"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-rose-600 text-white font-bold text-sm active:scale-[0.98] transition disabled:opacity-60"
              >
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                {{ isDeleting ? 'Menghapus…' : 'Ya, Hapus Template' }}
              </button>
              <button
                type="button"
                @click="showDeleteSheet = false"
                class="w-full py-3 rounded-2xl text-slate-500 font-semibold text-sm hover:bg-slate-100 transition"
              >
                Batal
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show"
          class="fixed top-safe left-1/2 -translate-x-1/2 z-[60] px-4 py-3 rounded-2xl shadow-xl text-sm font-semibold flex items-center gap-2 min-w-[200px] max-w-[90vw]"
          :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
          style="top: max(1.5rem, env(safe-area-inset-top));">
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue';
import api from '../services/api.js';

const templates = ref([]);
const loading = ref(true);
const loadError = ref(false);
const showDeleteSheet = ref(false);
const templateToDelete = ref(null);
const isDeleting = ref(false);

// Toast (konsisten dengan ReportHistory, ReportPreview & CreateReport)
const toast = reactive({ show: false, message: '', type: 'success' });
let toastTimer = null;
const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer);
  toast.message = message;
  toast.type = type;
  toast.show = true;
  toastTimer = setTimeout(() => { toast.show = false; }, 3000);
};

// ── Fetch templates ───────────────────────────────────────────────────────────
const fetchTemplates = async () => {
  loading.value = true;
  loadError.value = false;
  try {
    const res = await api.get('/templates');
    if (res.data?.data) {
      templates.value = res.data.data;
    }
  } catch (err) {
    console.error('Gagal mengambil template:', err);
    loadError.value = true;
    showToast('Gagal memuat daftar template. Periksa koneksi Anda.', 'error');
  } finally {
    loading.value = false;
  }
};

// ── Delete flow ───────────────────────────────────────────────────────────────
const confirmDelete = (tpl) => {
  templateToDelete.value = tpl;
  showDeleteSheet.value = true;
};

const deleteTemplate = async () => {
  if (!templateToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/templates/${templateToDelete.value.id}`);
    templates.value = templates.value.filter((t) => t.id !== templateToDelete.value.id);
    showDeleteSheet.value = false;
    showToast('Template berhasil dihapus.');
    templateToDelete.value = null;
  } catch (err) {
    console.error('Gagal menghapus template:', err);
    const msg = err.response?.data?.message || 'Gagal menghapus template. Coba lagi.';
    showToast(msg, 'error');
    // Sheet dibiarkan terbuka agar pesan error terlihat jelas oleh pengguna.
  } finally {
    isDeleting.value = false;
  }
};

onMounted(() => {
  fetchTemplates();
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-16px);
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.22s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
