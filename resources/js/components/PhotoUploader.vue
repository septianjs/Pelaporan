<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-xs font-bold text-slate-700 uppercase tracking-wide">
      {{ label }}
      <span v-if="required" class="text-rose-500">*</span>
    </label>

    <!-- Hidden file inputs for web/browser fallback -->
    <input
      type="file"
      ref="cameraInput"
      accept="image/jpeg,image/png,image/webp,image/jpg"
      capture="environment"
      class="hidden"
      @change="onFileInputChange"
    />
    <input
      type="file"
      ref="galleryInput"
      accept="image/jpeg,image/png,image/webp,image/jpg"
      class="hidden"
      @change="onFileInputChange"
    />

    <!-- ─── PROCESSING OVERLAY ─── -->
    <div
      v-if="isProcessing"
      :class="aspectRatioClass"
      class="relative w-full rounded-2xl overflow-hidden bg-slate-800 flex flex-col items-center justify-center gap-3"
    >
      <!-- Spinner -->
      <svg class="w-10 h-10 text-blue-400 animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
        <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <p class="text-white text-xs font-semibold">Memproses foto…</p>
    </div>

    <!-- ─── PREVIEW FRAME (object-fit: cover, no stretching) ─── -->
    <div
      v-else-if="previewUrl"
      :class="aspectRatioClass"
      class="relative w-full rounded-2xl overflow-hidden border-2 border-blue-500/30 bg-slate-900 shadow-md"
    >
      <img
        :src="previewUrl"
        alt="Dokumentasi"
        class="w-full h-full object-cover"
      />

      <!-- Overlay bottom bar -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/10 to-transparent flex flex-col justify-end p-3">
        <div class="flex items-center justify-between">
          <!-- Size info badge -->
          <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-semibold bg-emerald-500/90 text-white backdrop-blur-sm">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
            </svg>
            {{ sizeLabel || 'Foto Tersedia' }}
          </span>

          <div class="flex items-center gap-2">
            <!-- Ganti button → opens bottom sheet -->
            <button
              type="button"
              @click="showActionSheet = true"
              class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-semibold transition active:scale-95 flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
              </svg>
              Ganti
            </button>

            <!-- Hapus button -->
            <button
              type="button"
              @click="removePhoto"
              class="px-2.5 py-1 rounded-lg bg-rose-600/80 hover:bg-rose-600 backdrop-blur-md text-white text-xs font-semibold transition active:scale-95 flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
              Hapus
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- ─── EMPTY PLACEHOLDER WITH BUTTONS ─── -->
    <div
      v-else
      class="border-2 border-dashed border-slate-300 rounded-2xl p-4 bg-slate-50/80 hover:bg-slate-50 transition text-center space-y-3"
    >
      <div class="w-12 h-12 mx-auto rounded-full bg-blue-100 text-blue-700 flex items-center justify-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
      </div>

      <div>
        <p class="text-xs font-semibold text-slate-700">Belum ada foto dokumentasi</p>
        <p class="text-[11px] text-slate-400 mt-0.5">Format JPG, PNG, WEBP · Maks. 10 MB</p>
      </div>

      <!-- Two big buttons -->
      <div class="grid grid-cols-2 gap-2 pt-1">
        <button
          type="button"
          :disabled="isProcessing"
          @click="captureFromCamera"
          class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-blue-700 text-white text-xs font-semibold shadow-sm hover:bg-blue-800 active:scale-95 transition disabled:opacity-50"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
          </svg>
          <span>Ambil Foto</span>
        </button>

        <button
          type="button"
          :disabled="isProcessing"
          @click="selectFromGallery"
          class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-slate-200 text-slate-800 text-xs font-semibold hover:bg-slate-300 active:scale-95 transition border border-slate-300 disabled:opacity-50"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
          </svg>
          <span>Pilih Galeri</span>
        </button>
      </div>
    </div>

    <!-- Error message -->
    <p v-if="errorMessage" class="text-[11px] text-rose-600 font-medium flex items-center gap-1">
      <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
      </svg>
      {{ errorMessage }}
    </p>

    <!-- ─── BOTTOM SHEET — Ganti Foto Action Sheet ─── -->
    <!-- Backdrop -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showActionSheet"
          class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
          @click="showActionSheet = false"
        />
      </Transition>

      <Transition name="slide-up">
        <div
          v-if="showActionSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl pb-safe"
          style="max-width: 480px; margin: 0 auto;"
        >
          <!-- Handle -->
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>

          <div class="px-5 pt-2 pb-6">
            <p class="text-sm font-bold text-slate-800 text-center mb-4">Ganti Foto</p>

            <div class="space-y-3">
              <!-- Kamera -->
              <button
                type="button"
                @click="sheetAction('camera')"
                class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-blue-600 text-white font-semibold text-sm active:scale-[0.98] transition"
              >
                <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/>
                  </svg>
                </span>
                <span>📷 Ambil Foto dari Kamera</span>
              </button>

              <!-- Galeri -->
              <button
                type="button"
                @click="sheetAction('gallery')"
                class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-slate-100 text-slate-800 font-semibold text-sm border border-slate-200 active:scale-[0.98] transition"
              >
                <span class="w-9 h-9 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
                  <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                  </svg>
                </span>
                <span>🖼 Pilih dari Galeri</span>
              </button>

              <!-- Batal -->
              <button
                type="button"
                @click="showActionSheet = false"
                class="w-full py-3 rounded-2xl text-slate-500 font-semibold text-sm hover:bg-slate-100 active:scale-[0.98] transition"
              >
                Batal
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue';
import { Camera, CameraResultType, CameraSource } from '@capacitor/camera';
import { Capacitor } from '@capacitor/core';
import { validateImageFile, optimizeImage } from '../services/imageOptimizer.js';

const props = defineProps({
  label: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  modelValue: {
    type: [File, String, null],
    default: null,
  },
  aspectRatio: {
    type: String,
    default: '16:9', // '16:9', '4:3', 'square'
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const cameraInput = ref(null);
const galleryInput = ref(null);
const previewUrl = ref(null);
const errorMessage = ref('');
const isProcessing = ref(false);
const showActionSheet = ref(false);
const sizeLabel = ref('');

// ── Aspect ratio CSS class ──
const aspectRatioClass = computed(() => {
  if (props.aspectRatio === 'square') return 'aspect-square';
  if (props.aspectRatio === '4:3') return 'aspect-[4/3]';
  return 'aspect-video'; // 16:9 default
});

// ── Sync preview when modelValue is pre-loaded (e.g. from draft) ──
watch(
  () => props.modelValue,
  (val) => {
    if (typeof val === 'string' && val) {
      previewUrl.value = val;
      sizeLabel.value = '';
    } else if (val instanceof Blob || val instanceof File) {
      previewUrl.value = URL.createObjectURL(val);
    } else if (!val) {
      previewUrl.value = null;
      sizeLabel.value = '';
    }
  },
  { immediate: true }
);

// ── Format bytes to human-readable ──
const formatBytes = (bytes) => {
  if (bytes < 1024) return `${bytes} B`;
  if (bytes < 1024 * 1024) return `${(bytes / 1024).toFixed(0)} KB`;
  return `${(bytes / (1024 * 1024)).toFixed(1)} MB`;
};

// ── Core: process a File/Blob through optimizer and emit ──
const processAndEmit = async (fileOrBlob, baseName = 'photo') => {
  isProcessing.value = true;
  errorMessage.value = '';

  try {
    // 1. Validate raw file
    const validation = validateImageFile(fileOrBlob);
    if (!validation.valid) {
      errorMessage.value = validation.error;
      return;
    }

    // 2. Optimize (resize + compress)
    const result = await optimizeImage(fileOrBlob, `${baseName}_${Date.now()}.jpg`);

    // 3. Update preview & size badge
    previewUrl.value = result.dataUrl;
    sizeLabel.value = formatBytes(result.size);

    // 4. Emit optimized File
    emit('update:modelValue', result.file);
    emit('change', result.file);
  } catch (err) {
    console.error('Image optimization error:', err);
    errorMessage.value = err.message || 'Gagal memproses foto.';
  } finally {
    isProcessing.value = false;
  }
};

// ── Capacitor Camera: request permissions first, then open ──
const requestCameraPermission = async () => {
  try {
    const permissions = await Camera.requestPermissions({ permissions: ['camera', 'photos'] });
    // If any required permission is denied, warn but still attempt (user might approve in system dialog)
    const denied = Object.values(permissions).some((s) => s === 'denied');
    if (denied) {
      console.warn('Camera permission partially denied, attempting anyway.');
    }
  } catch {
    // Older Capacitor or browser — ignore
  }
};

// ── Ambil Foto dari Kamera ──
const captureFromCamera = async () => {
  errorMessage.value = '';
  if (Capacitor.isNativePlatform()) {
    await requestCameraPermission();
    try {
      const image = await Camera.getPhoto({
        quality: 90,
        allowEditing: false,
        resultType: CameraResultType.Uri,
        source: CameraSource.Camera,
      });

      if (image?.webPath) {
        const response = await fetch(image.webPath);
        const blob = await response.blob();
        await processAndEmit(blob, 'camera');
      }
    } catch (err) {
      if (err.message?.includes('User cancelled') || err.message?.includes('cancelled')) {
        return; // Silent cancel
      }
      console.warn('Native camera error, falling back to browser input:', err);
      cameraInput.value?.click();
    }
  } else {
    cameraInput.value?.click();
  }
};

// ── Pilih dari Galeri ──
const selectFromGallery = async () => {
  errorMessage.value = '';
  if (Capacitor.isNativePlatform()) {
    await requestCameraPermission();
    try {
      const image = await Camera.getPhoto({
        quality: 90,
        allowEditing: false,
        resultType: CameraResultType.Uri,
        source: CameraSource.Photos,
      });

      if (image?.webPath) {
        const response = await fetch(image.webPath);
        const blob = await response.blob();
        await processAndEmit(blob, 'gallery');
      }
    } catch (err) {
      if (err.message?.includes('User cancelled') || err.message?.includes('cancelled')) {
        return;
      }
      console.warn('Native gallery error, falling back to browser input:', err);
      galleryInput.value?.click();
    }
  } else {
    galleryInput.value?.click();
  }
};

// ── Bottom sheet action handler ──
const sheetAction = async (action) => {
  showActionSheet.value = false;
  // Small delay so sheet animates out before camera opens
  await new Promise((r) => setTimeout(r, 200));
  if (action === 'camera') {
    await captureFromCamera();
  } else {
    await selectFromGallery();
  }
};

// ── Web file input fallback ──
const onFileInputChange = async (event) => {
  const file = event.target.files?.[0];
  if (!file) return;

  // Reset input so same file can be re-selected after removal
  event.target.value = '';

  await processAndEmit(file, 'upload');
};

// ── Remove photo ──
const removePhoto = () => {
  previewUrl.value = null;
  errorMessage.value = '';
  sizeLabel.value = '';
  emit('update:modelValue', null);
  emit('change', null);
};
</script>

<style scoped>
/* Bottom sheet slide-up transition */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}

/* Backdrop fade */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.22s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

/* Safe-area bottom padding (Capacitor) */
.pb-safe {
  padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
}
</style>
