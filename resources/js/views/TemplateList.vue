<template>
  <div class="template-list-container">
    <!-- ── HEADER CARD ── -->
    <div class="header-card">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
          <div class="header-icon-box">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
            </svg>
          </div>
          <div>
            <h2 class="header-title">Katalog Desain PDF</h2>
          </div>
        </div>
      </div>

      <p class="header-desc">
        Setiap desain menghasilkan 6 halaman PDF 16:9: satu sampul dan lima titik pemantauan.
      </p>

      <router-link
        to="/templates/create"
        class="btn-create-template"
      >
        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 5v14M5 12h14"/>
        </svg>
        <span>Buat Template</span>
      </router-link>
    </div>

    <!-- ── SECTION TITLE ── -->
    <div class="section-header">
      <h3 class="section-title">
        Koleksi Template ({{ templates.length }})
      </h3>
      <span class="section-badge">16:9 Widescreen</span>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-3">
      <div
        v-for="i in 3"
        :key="i"
        class="skeleton-card"
      >
        <div class="skeleton-thumb"/>
        <div class="skeleton-line-title"/>
        <div class="skeleton-line-desc"/>
      </div>
    </div>

    <!-- ── GAGAL MEMUAT ── -->
    <div v-else-if="loadError" class="error-card">
      <div class="error-icon-box">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
      </div>
      <div>
        <p class="error-title">Gagal Memuat Daftar Template</p>
        <p class="error-desc">Periksa koneksi jaringan Anda, kemudian segarkan kembali.</p>
      </div>
      <button
        type="button"
        @click="fetchTemplates"
        class="btn-retry"
      >
        Muat Ulang
      </button>
    </div>

    <!-- ── TEMPLATE CARDS ── -->
    <div v-else class="spce-y-4">
      <div
        v-for="tpl in templates"
        :key="tpl.id"
        class="template-card"
      >
        <!-- Pratinjau desain asli: sampul + titik pemantauan -->
        <div class="relative mb-3">
          <TemplateThumb :theme="tpl.theme" :name="tpl.name" :preview="tpl.preview_url || ''" />

          <!-- Type badge -->
          <div class="template-type-badge">
            {{ tpl.type === 'uploaded' ? 'BUATAN ANDA' : 'TEMA PDF' }}
          </div>
        </div>

        <!-- Template meta -->
        <div>
          <div class="flex items-center justify-between">
            <h4 class="template-name">{{ tpl.name }}</h4>
            <span class="template-status-badge">
              Siap Digunakan
            </span>
          </div>

          <p class="template-desc">
            {{ tpl.description || 'Format laporan pengawasan kebersihan lingkungan kantor BPSDM.' }}
          </p>

          <!-- Actions row -->
          <div class="template-footer">
            <span class="font-mono">PDF 16:9 • 6 halaman</span>
            <div class="flex items-center gap-2">
              <!-- Delete (only uploaded templates) -->
              <button
                v-if="tpl.type === 'uploaded'"
                type="button"
                @click="confirmDelete(tpl)"
                class="btn-delete"
              >
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                    d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
                <span>Hapus</span>
              </button>

              <!-- Use template -->
              <router-link
                :to="{ path: '/create', query: { template_id: tpl.id } }"
                class="btn-use-template"
              >
                <span>Gunakan</span>
                <svg class="w-3.5 h-3.5 text-[#14213D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          class="modal-backdrop"
          @click="showDeleteSheet = false"
        />
      </Transition>

      <Transition name="slide-up">
        <div
          v-if="showDeleteSheet"
          class="delete-sheet"
        >
          <div class="modal-handle-bar">
            <div class="modal-handle-pill"/>
          </div>

          <div class="delete-sheet-body">
            <!-- Trash icon -->
            <div class="delete-icon-box">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>

            <div class="text-center">
              <p class="delete-dialog-title">Hapus Template?</p>
              <p class="delete-dialog-desc">
                Template <strong class="text-[#14213D]">"{{ templateToDelete?.name }}"</strong> akan dihapus permanen dari sistem dan tidak dapat dipulihkan.
              </p>
            </div>

            <div class="space-y-2">
              <button
                type="button"
                :disabled="isDeleting"
                @click="deleteTemplate"
                class="btn-delete-confirm"
              >
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <span>{{ isDeleting ? 'Menghapus Data…' : 'Ya, Hapus Template' }}</span>
              </button>
              <button
                type="button"
                @click="showDeleteSheet = false"
                class="btn-cancel-delete"
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
        <div
          v-if="toast.show"
          class="toast-pill"
          :class="toast.type === 'success' ? 'toast-success' : 'toast-error'"
        >
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { useRoute, useRouter } from 'vue-router';
import api from '../services/api.js';
import TemplateThumb from '../components/TemplateThumb.vue';

const route = useRoute();
const router = useRouter();
const templates = ref([]);
const loading = ref(true);
const loadError = ref(false);
const showDeleteSheet = ref(false);
const templateToDelete = ref(null);
const isDeleting = ref(false);

// Toast
const toast = reactive({ show: false, message: '', type: 'success' });
let toastTimer = null;
const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer);
  toast.message = message;
  toast.type = type;
  toast.show = true;
  toastTimer = setTimeout(() => { toast.show = false; }, 3000);
};

// Fetch templates
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

// Delete flow
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
  } finally {
    isDeleting.value = false;
  }
};

onMounted(() => {
  fetchTemplates();
  if (route.query.created) {
    showToast(route.query.warn ? 'Template dibuat, ada peringatan pada desain.' : 'Template berhasil dibuat.');
    router.replace({ path: '/templates' });
  }
});
</script>

<style scoped>
.template-list-container {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 1.25rem;
  padding-bottom: 6rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Header Card */
.header-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.header-icon-box {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  background-color: #14213D;
  color: #F5A524;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
}

.header-title {
  font-size: 1rem;
  font-weight: 700;
  color: #14213D;
}

.header-desc {
  font-size: 0.75rem;
  color: #525A6A;
  line-height: 1.6;
  margin-bottom: 1rem;
}

.btn-create-template {
  width: 100%;
  display: inline-flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 1rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-size: 0.875rem;
  font-weight: 700;
  text-decoration: none;
  transition: all 0.2s ease;
}

.btn-create-template:active {
  transform: scale(0.98);
}

/* Section Title */
.section-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding-left: 0.25rem;
  padding-right: 0.25rem;
  padding-top: 0.25rem;
}

.section-title {
  font-size: 0.75rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.05em;
  color: #676F7F;
}

.section-badge {
  font-size: 0.6875rem;
  color: #14213D;
  font-family: var(--font-mono, monospace);
  font-weight: 500;
}

/* Skeleton */
.skeleton-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1rem;
  border: 1px solid #D7DBE2;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.skeleton-thumb {
  height: 9rem;
  background-color: #DDE0E7;
  border-radius: 0.75rem;
}

.skeleton-line-title {
  height: 1rem;
  background-color: #DDE0E7;
  border-radius: 0.25rem;
  width: 50%;
}

.skeleton-line-desc {
  height: 0.75rem;
  background-color: #DDE0E7;
  border-radius: 0.25rem;
  width: 75%;
}

/* Error Card */
.error-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1.5rem;
  border: 1px solid #D7DBE2;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.error-icon-box {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  background-color: #FDF0EE;
  border: 1px solid #F5C7C1;
  color: #A6382C;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.error-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #14213D;
}

.error-desc {
  font-size: 0.75rem;
  color: #676F7F;
  margin-top: 0.25rem;
}

.btn-retry {
  padding: 0.5rem 1rem;
  border-radius: 0.75rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-size: 0.75rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
  align-self: center;
  transition: all 0.15s ease;
}

.btn-retry:active {
  transform: scale(0.97);
}

/* Template Cards */
.template-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1rem;
  border: 1px solid #D7DBE2;
  overflow: hidden;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: border-color 0.15s ease;
}

.template-card:hover {
  border-color: rgba(20, 33, 61, 0.3);
}

.template-type-badge {
  position: absolute;
  top: 0.375rem;
  left: 0.375rem;
  padding: 0.125rem 0.625rem;
  border-radius: 0.375rem;
  backdrop-filter: blur(12px);
  background-color: rgba(20, 33, 61, 0.9);
  color: #F5A524;
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.05em;
  border: 1px solid rgba(255, 255, 255, 0.1);
}

.template-name {
  font-size: 0.875rem;
  font-weight: 700;
  color: #14213D;
}

.template-status-badge {
  font-size: 0.625rem;
  font-weight: 600;
  color: #1B2C4D;
  background-color: #EAF3EC;
  padding: 0.125rem 0.625rem;
  border-radius: 9999px;
  border: 1px solid #C8E0D0;
}

.template-desc {
  font-size: 0.75rem;
  color: #676F7F;
  margin-top: 0.25rem;
  line-height: 1.5;
}

.template-footer {
  margin-top: 0.75rem;
  padding-top: 0.75rem;
  border-top: 1px solid #DEE2EB;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.6875rem;
  color: #788191;
}

.btn-delete {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  color: #A6382C;
  font-weight: 600;
  font-size: 0.75rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: color 0.15s ease;
}

.btn-delete:hover {
  color: #8F2F24;
}

.btn-delete:active {
  transform: scale(0.97);
}

.btn-use-template {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-weight: 700;
  font-size: 0.75rem;
  color: #14213D;
  background-color: #DEE2EB;
  padding: 0.375rem 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #CBD1DB;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-use-template:hover {
  background-color: #D1D5DE;
}

.btn-use-template:active {
  transform: scale(0.97);
}

/* Delete Sheet Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  background-color: rgba(11, 20, 40, 0.7);
  backdrop-filter: blur(4px);
}

.delete-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50;
  max-width: 480px;
  margin: 0 auto;
  background-color: #F5F7FA;
  border-top-left-radius: 1.5rem;
  border-top-right-radius: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border-top: 1px solid #D7DBE2;
  padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
}

.modal-handle-bar {
  display: flex;
  justify-content: center;
  padding-top: 0.75rem;
  padding-bottom: 0.25rem;
}

.modal-handle-pill {
  width: 2.5rem;
  height: 0.25rem;
  border-radius: 9999px;
  background-color: #C9CED7;
}

.delete-sheet-body {
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  padding-top: 0.5rem;
  padding-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.delete-icon-box {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 1rem;
  background-color: #FDF0EE;
  border: 1px solid #F5C7C1;
  color: #A6382C;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-dialog-title {
  font-size: 1rem;
  font-weight: 700;
  color: #14213D;
}

.delete-dialog-desc {
  font-size: 0.75rem;
  color: #676F7F;
  margin-top: 0.25rem;
  line-height: 1.6;
}

.btn-delete-confirm {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border-radius: 1rem;
  background-color: #A6382C;
  color: #FFFFFF;
  font-weight: 700;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.15s ease;
}

.btn-delete-confirm:hover {
  background-color: #8F2F24;
}

.btn-delete-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel-delete {
  width: 100%;
  padding: 0.75rem;
  border-radius: 1rem;
  color: #676F7F;
  font-weight: 600;
  font-size: 0.875rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.btn-cancel-delete:hover {
  background-color: #E3E6EC;
}

/* Toast */
.toast-pill {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  z-index: 60;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 200px;
  max-width: 90vw;
  border-width: 1px;
  border-style: solid;
  top: max(1.5rem, env(safe-area-inset-top));
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

/* Animations */
.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
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
