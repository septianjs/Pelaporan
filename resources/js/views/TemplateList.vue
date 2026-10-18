<template>
  <div class="px-4 py-5 space-y-4 pb-24">
    <!-- ── HEADER CARD ── -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-[#14251D] text-[#D8C79C] flex items-center justify-center shrink-0">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-bold text-[#14251D]">Katalog Format Template</h2>
            <p class="text-xs text-[#6F7771]">Standar PowerPoint Widescreen 16:9 HD</p>
          </div>
        </div>
      </div>

      <p class="text-xs text-[#59635D] leading-relaxed mb-4">
        Setiap template tersusun atas 6 slide berstandar resmi (Cover Laporan + 5 Titik Pemantauan Lingkungan BPSDM).
      </p>

      <router-link
        to="/templates/upload"
        class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-[#14251D] text-[#FAF9F5] rounded-xl text-xs font-semibold shadow-sm hover:bg-[#1C362A] active-press transition"
      >
        <svg class="w-4 h-4 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
        </svg>
        <span>Unggah Template Kustom (.pptx)</span>
      </router-link>
    </div>

    <!-- ── SECTION TITLE ── -->
    <div class="flex items-center justify-between px-1 pt-1">
      <h3 class="text-xs font-bold uppercase tracking-wider text-[#6F7771]">
        Koleksi Template ({{ templates.length }})
      </h3>
      <span class="text-[11px] text-[#14251D] font-mono font-medium">16:9 Widescreen</span>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i"
        class="bg-[#FAF9F5] rounded-2xl p-4 border border-[#E3E0D6] animate-pulse space-y-3">
        <div class="h-36 bg-[#E8E5DC] rounded-xl"/>
        <div class="h-4 bg-[#E8E5DC] rounded w-1/2"/>
        <div class="h-3 bg-[#E8E5DC] rounded w-3/4"/>
      </div>
    </div>

    <!-- ── GAGAL MEMUAT ── -->
    <div v-else-if="loadError" class="bg-[#FAF9F5] rounded-2xl p-6 border border-[#E3E0D6] text-center space-y-3">
      <div class="w-12 h-12 rounded-2xl bg-[#FDF0EE] border border-[#F5C7C1] text-[#A6382C] flex items-center justify-center mx-auto">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-[#14251D]">Gagal Memuat Daftar Template</p>
        <p class="text-xs text-[#6F7771] mt-1">Periksa koneksi jaringan Anda, kemudian segarkan kembali.</p>
      </div>
      <button
        type="button"
        @click="fetchTemplates"
        class="px-4 py-2 rounded-xl bg-[#14251D] text-[#FAF9F5] text-xs font-bold active-press transition"
      >
        Muat Ulang
      </button>
    </div>

    <!-- ── TEMPLATE CARDS ── -->
    <div v-else class="space-y-4">
      <div
        v-for="tpl in templates"
        :key="tpl.id"
        class="bg-[#FAF9F5] rounded-2xl p-4 border border-[#E3E0D6] hover:border-[#14251D]/30 transition overflow-hidden shadow-sm"
      >
        <!-- 16:9 Preview Thumbnail -->
        <div class="relative aspect-video w-full rounded-xl overflow-hidden bg-[#ECE8DD] border border-[#DCD7CA] mb-3 shadow-inner">
          <img
            v-if="tpl.preview_url"
            :src="tpl.preview_url"
            :alt="tpl.name"
            class="w-full h-full object-cover"
            loading="lazy"
          />
          <div v-else class="w-full h-full flex items-center justify-center text-xs text-[#808983]">
            Pratinjau Belum Tersedia
          </div>

          <!-- Type badge -->
          <div class="absolute top-2 left-2 px-2.5 py-0.5 rounded-md backdrop-blur-md text-[#FAF9F5] text-[10px] font-semibold tracking-wide border border-white/10"
            :class="tpl.type === 'builtin' ? 'bg-[#14251D]/90 text-[#D8C79C]' : 'bg-[#6B5A2E]/90 text-white'">
            {{ tpl.type === 'builtin' ? 'STANDAR BAWAAN' : 'TEMPLATE UNGGAHAN' }}
          </div>

          <!-- 16:9 tag -->
          <div class="absolute bottom-2 right-2 px-2 py-0.5 rounded-md bg-[#0E1A14]/80 backdrop-blur-md text-[#D8C79C] text-[10px] font-bold font-mono">
            16:9 Widescreen
          </div>
        </div>

        <!-- Template meta -->
        <div>
          <div class="flex items-center justify-between">
            <h4 class="text-sm font-bold text-[#14251D]">{{ tpl.name }}</h4>
            <span class="text-[10px] font-semibold text-[#1A3428] bg-[#EAF3EC] px-2.5 py-0.5 rounded-full border border-[#C8E0D0]">
              Siap Digunakan
            </span>
          </div>

          <p class="text-xs text-[#6F7771] mt-1 leading-relaxed">
            {{ tpl.description || 'Format laporan pengawasan kebersihan lingkungan kantor BPSDM.' }}
          </p>

          <!-- Actions row -->
          <div class="mt-3 pt-3 border-t border-[#ECE8DD] flex items-center justify-between text-[11px] text-[#808983]">
            <span class="font-mono">6 Slide • {{ tpl.template_key }}.pptx</span>
            <div class="flex items-center gap-2">
              <!-- Delete (only uploaded templates) -->
              <button
                v-if="tpl.type === 'uploaded'"
                type="button"
                @click="confirmDelete(tpl)"
                class="inline-flex items-center gap-1 text-[#A6382C] hover:text-[#8F2F24] font-semibold transition active-press"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                Hapus
              </button>

              <!-- Use template -->
              <router-link
                :to="{ path: '/create', query: { template_id: tpl.id } }"
                class="inline-flex items-center gap-1 font-bold text-xs text-[#14251D] bg-[#ECE8DD] px-3 py-1.5 rounded-xl border border-[#DCD7CA] hover:bg-[#DFDBD0] active-press transition"
              >
                Gunakan
                <svg class="w-3.5 h-3.5 text-[#14251D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          class="fixed inset-0 z-50 bg-[#0E1A14]/70 backdrop-blur-sm"
          @click="showDeleteSheet = false"
        />
      </Transition>

      <Transition name="slide-up">
        <div
          v-if="showDeleteSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#FAF9F5] rounded-t-3xl shadow-2xl border-t border-[#E3E0D6]"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));"
        >
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-[#D8D4C8]"/>
          </div>

          <div class="px-5 pt-2 pb-4 space-y-4">
            <!-- Trash icon -->
            <div class="w-14 h-14 rounded-2xl bg-[#FDF0EE] border border-[#F5C7C1] text-[#A6382C] mx-auto flex items-center justify-center">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>

            <div class="text-center">
              <p class="text-base font-bold text-[#14251D]">Hapus Template?</p>
              <p class="text-xs text-[#6F7771] mt-1 leading-relaxed">
                Template <strong class="text-[#14251D]">"{{ templateToDelete?.name }}"</strong> akan dihapus permanen dari sistem dan tidak dapat dipulihkan.
              </p>
            </div>

            <div class="space-y-2">
              <button
                type="button"
                :disabled="isDeleting"
                @click="deleteTemplate"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-[#A6382C] text-white font-bold text-sm hover:bg-[#8F2F24] active-press transition disabled:opacity-60 shadow-sm"
              >
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                {{ isDeleting ? 'Menghapus Data…' : 'Ya, Hapus Template' }}
              </button>
              <button
                type="button"
                @click="showDeleteSheet = false"
                class="w-full py-3 rounded-2xl text-[#6F7771] font-semibold text-sm hover:bg-[#ECEAE3] transition"
              >
                Batalkan
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
          class="fixed top-safe left-1/2 -translate-x-1/2 z-[60] px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2 min-w-[200px] max-w-[90vw] border"
          :class="toast.type === 'success' ? 'bg-[#14251D] text-[#FAF9F5] border-[#254234]' : 'bg-[#A6382C] text-white border-[#8F2F24]'"
          style="top: max(1.5rem, env(safe-area-inset-top));">
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ toast.message }}</span>
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
