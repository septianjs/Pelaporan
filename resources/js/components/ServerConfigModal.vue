<template>
  <Teleport to="body">
    <Transition name="fade">
      <div
        v-if="modelValue"
        class="modal-backdrop"
        @click="close"
      />
    </Transition>

    <Transition name="slide-up">
      <div
        v-if="modelValue"
        class="modal-sheet"
      >
        <div class="modal-handle-bar">
          <div class="modal-handle-pill" />
        </div>

        <div class="modal-body">
          <div>
            <p class="modal-title">Konfigurasi Server API</p>
            <p class="modal-subtitle">
              Masukkan alamat IP komputer server Laravel Anda.
              HP dan komputer harus terhubung ke WiFi yang sama.
            </p>
          </div>

          <!-- Current URL preview -->
          <div class="status-preview-box">
            <span
              class="w-2 h-2 rounded-full shrink-0"
              :class="apiStatus.online ? 'bg-[#34D399]' : 'bg-[#EF4444]'"
            />
            <span class="status-preview-url">{{ currentApiUrl }}</span>
          </div>

          <!-- URL Input -->
          <div>
            <label class="input-label">
              Alamat Server <span class="text-[#A6382C]">*</span>
            </label>
            <input
              v-model="serverUrlInput"
              type="url"
              placeholder="http://192.168.1.100:8000/api"
              class="server-input"
              @keyup.enter="saveServerUrl"
            />
            <p class="input-hint">
              Format: <code>http://IP_KOMPUTER:8000/api</code><br>
              Jalankan <code>php artisan serve --host=0.0.0.0</code> di komputer
            </p>
          </div>

          <p v-if="configError" class="text-xs text-[#A6382C] font-medium">{{ configError }}</p>
          <p v-if="configSuccess" class="text-xs text-[#1B2C4D] font-medium">{{ configSuccess }}</p>

          <!-- Actions -->
          <div class="flex items-center gap-2">
            <button
              type="button"
              @click="resetServerUrl"
              class="btn-secondary"
            >
              Reset Default
            </button>
            <button
              type="button"
              @click="saveServerUrl"
              :disabled="testingConnection"
              class="btn-primary"
            >
              <svg v-if="testingConnection" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3" />
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z" />
              </svg>
              {{ testingConnection ? 'Menguji...' : 'Simpan & Uji' }}
            </button>
          </div>

          <button
            type="button"
            @click="close"
            class="btn-close"
          >
            Tutup
          </button>
        </div>
      </div>
    </Transition>
  </Teleport>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import api, { setCustomApiUrl, getApiUrl } from '../services/api';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  apiStatus: {
    type: Object,
    default: () => ({ online: false, message: '' }),
  },
});

const emit = defineEmits(['update:modelValue', 'status-changed']);

const serverUrlInput = ref('');
const testingConnection = ref(false);
const configError = ref('');
const configSuccess = ref('');

const currentApiUrl = computed(() => getApiUrl());

watch(
  () => props.modelValue,
  (isOpen) => {
    if (isOpen) {
      serverUrlInput.value = getApiUrl();
      configError.value = '';
      configSuccess.value = '';
    }
  },
  { immediate: true }
);

const close = () => {
  emit('update:modelValue', false);
};

const saveServerUrl = async () => {
  const url = serverUrlInput.value.trim();
  if (!url) {
    configError.value = 'Masukkan alamat server.';
    return;
  }
  if (!url.startsWith('http://') && !url.startsWith('https://')) {
    configError.value = 'URL harus diawali dengan http:// atau https://';
    return;
  }

  configError.value = '';
  configSuccess.value = '';
  testingConnection.value = true;

  setCustomApiUrl(url);

  try {
    const res = await api.get('/ping', { timeout: 8000 });
    if (res.data && res.data.status === 'ok') {
      configSuccess.value = 'Berhasil terhubung ke server!';
      configError.value = '';
      emit('status-changed');
      setTimeout(() => {
        close();
      }, 1500);
    } else {
      throw new Error('Response tidak valid');
    }
  } catch (err) {
    configError.value = `Gagal terhubung ke ${url} — pastikan server Laravel berjalan dan HP terhubung ke WiFi yang sama.`;
    emit('status-changed');
  } finally {
    testingConnection.value = false;
  }
};

const resetServerUrl = () => {
  setCustomApiUrl(null);
  serverUrlInput.value = '';
  configError.value = '';
  configSuccess.value = 'URL direset ke default.';
  emit('status-changed');
};
</script>

<style scoped>
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  background-color: rgba(11, 20, 40, 0.7);
  backdrop-filter: blur(4px);
}

.modal-sheet {
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
  border-top: 1px solid #D7DBE2;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
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

.modal-body {
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  padding-top: 0.5rem;
  padding-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.modal-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #14213D;
}

.modal-subtitle {
  font-size: 0.75rem;
  color: #676F7F;
  margin-top: 0.125rem;
}

.status-preview-box {
  display: flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.75rem;
  border-radius: 0.75rem;
  background-color: #ECEEF2;
  border: 1px solid #CFD5DF;
}

.status-preview-url {
  font-size: 0.75rem;
  color: #35507D;
  font-family: var(--font-mono, monospace);
  overflow: hidden;
  text-overflow: ellipsis;
  white-space: nowrap;
}

.input-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 600;
  color: #14213D;
  margin-bottom: 0.375rem;
}

.server-input {
  width: 100%;
  padding: 0.625rem 0.875rem;
  border-radius: 0.75rem;
  border: 1px solid #CBD1DB;
  background-color: #FFFFFF;
  font-size: 0.75rem;
  color: #14213D;
  font-family: var(--font-mono, monospace);
  box-sizing: border-box;
}

.server-input:focus {
  outline: none;
  border-color: #14213D;
  box-shadow: 0 0 0 1px #14213D;
}

.input-hint {
  font-size: 0.625rem;
  color: #7089B5;
  margin-top: 0.375rem;
  line-height: 1.5;
}

.input-hint code {
  background-color: #E4EAF3;
  padding: 0.125rem 0.25rem;
  border-radius: 0.25rem;
}

.btn-secondary {
  flex: 1;
  padding: 0.75rem;
  border-radius: 0.75rem;
  border: 1px solid #CBD1DB;
  color: #676F7F;
  font-weight: 600;
  font-size: 0.75rem;
  background: transparent;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-secondary:hover {
  background-color: #E3E6EC;
}

.btn-primary {
  flex: 1;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
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

.btn-primary:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.btn-close {
  width: 100%;
  padding: 0.625rem;
  border-radius: 0.75rem;
  color: #676F7F;
  font-weight: 600;
  font-size: 0.75rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.btn-close:hover {
  background-color: #E3E6EC;
}

/* Animations */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.22s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
</style>

