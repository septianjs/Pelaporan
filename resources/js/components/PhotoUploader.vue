<template>
  <div class="space-y-2">
    <label v-if="label" class="block text-xs font-bold text-navy-700 uppercase tracking-wider">
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
      class="relative w-full rounded-2xl overflow-hidden bg-[#14213D] flex flex-col items-center justify-center gap-3"
    >
      <!-- Spinner -->
      <svg class="w-8 h-8 text-[#F5A524] animate-spin" fill="none" viewBox="0 0 24 24">
        <circle class="opacity-20" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
        <path class="opacity-90" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
      </svg>
      <p class="text-[#F2F5FA] text-xs font-medium">Mengoptimalkan gambar…</p>
    </div>

    <!-- ─── PREVIEW FRAME (object-fit: cover, no stretching) ─── -->
    <div
      v-else-if="previewUrl"
      :class="aspectRatioClass"
      class="relative w-full rounded-2xl overflow-hidden border border-[#14213D]/25 bg-navy-900 shadow-sm"
    >
      <img
        :src="previewUrl"
        :alt="label || 'Dokumentasi'"
        :class="['w-full h-full object-cover', isSquare ? 'object-[50%_20%]' : 'object-center']"
      />

      <!-- Overlay bottom bar -->
      <div class="absolute inset-0 bg-gradient-to-t from-black/85 via-black/15 to-transparent flex flex-col justify-end p-3">
        <div :class="['flex items-center', isSquare ? 'justify-center' : 'justify-between']">
          <!-- Size info badge (disembunyikan pada foto kotak: ruangnya sempit) -->
          <span v-if="!isSquare" class="inline-flex items-center gap-1.5 whitespace-nowrap px-2.5 py-1 rounded-full text-[10px] font-medium bg-black/60 text-[#F5A524] border border-white/10 backdrop-blur-md">
            <svg class="w-3 h-3 text-[#34D399]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
            {{ sizeLabel || 'Tersedia' }}
          </span>

          <div class="flex items-center gap-2">
            <!-- Ganti button → opens bottom sheet -->
            <button
              type="button"
              @click="showActionSheet = true"
              class="px-2.5 py-1 rounded-lg bg-white/20 hover:bg-white/30 backdrop-blur-md text-white text-xs font-semibold transition active-press flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
              </svg>
              Ganti
            </button>

            <!-- Hapus button -->
            <button
              type="button"
              @click="removePhoto"
              class="px-2.5 py-1 rounded-lg bg-rose-950/80 hover:bg-rose-900 border border-rose-500/30 backdrop-blur-md text-rose-200 text-xs font-semibold transition active-press flex items-center gap-1"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
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
      class="border border-dashed border-[#CAD0DC] rounded-2xl p-4 bg-[#F5F7FA] hover:bg-[#ECEFF5] transition text-center space-y-3"
    >
      <div class="w-10 h-10 mx-auto rounded-xl bg-[#E8EDF5] text-[#1B2C4D] flex items-center justify-center border border-[#D0D8E8]">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
          <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0zM18.75 10.5h.008v.008h-.008V10.5z" />
        </svg>
      </div>

      <div>
        <p class="text-xs font-bold text-navy-800">{{ emptyTitle }}</p>
        <p class="text-[11px] text-navy-400 mt-0.5">{{ emptyHint }}</p>
      </div>

      <!-- Two buttons -->
      <div class="grid grid-cols-2 gap-2 pt-1">
        <button
          type="button"
          :disabled="isProcessing"
          @click="captureFromCamera"
          class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-[#14213D] hover:bg-[#263B61] text-white text-xs font-semibold shadow-sm active-press transition disabled:opacity-50"
        >
          <svg class="w-3.5 h-3.5 text-[#F5A524]" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
          </svg>
          <span>Buka Kamera</span>
        </button>

        <button
          type="button"
          :disabled="isProcessing"
          @click="selectFromGallery"
          class="flex items-center justify-center gap-1.5 px-3 py-2.5 rounded-xl bg-[#E9ECF2] hover:bg-[#DDE0E6] text-navy-800 text-xs font-semibold active-press transition border border-navy-200/80 disabled:opacity-50"
        >
          <svg class="w-3.5 h-3.5 text-navy-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
          </svg>
          <span>Pilih Berkas</span>
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
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#F5F7FA] rounded-t-3xl shadow-2xl pb-safe border-t border-navy-200"
          style="max-width: 480px; margin: 0 auto;"
        >
          <!-- Handle -->
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-navy-300"/>
          </div>

          <div class="px-5 pt-2 pb-6">
            <p class="text-sm font-bold text-navy-900 text-center mb-4">Pilih Sumber Foto</p>

            <div class="space-y-2.5">
              <!-- Kamera -->
              <button
                type="button"
                @click="sheetAction('camera')"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl bg-[#14213D] text-white font-semibold text-xs active-press transition"
              >
                <span class="w-8 h-8 rounded-lg bg-white/10 text-[#F5A524] flex items-center justify-center shrink-0">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 015.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 00-1.134-.175 2.31 2.31 0 01-1.64-1.055l-.822-1.316a2.192 2.192 0 00-1.736-1.039 48.774 48.774 0 00-5.232 0 2.192 2.192 0 00-1.736 1.039l-.821 1.316z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 11-9 0 4.5 4.5 0 019 0z" />
                  </svg>
                </span>
                <span>Ambil Foto Baru (Kamera)</span>
              </button>

              <!-- Galeri -->
              <button
                type="button"
                @click="sheetAction('gallery')"
                class="w-full flex items-center gap-3 px-4 py-3 rounded-2xl bg-white text-navy-800 font-semibold text-xs border border-navy-200 active-press transition"
              >
                <span class="w-8 h-8 rounded-lg bg-[#F5F7FA] text-navy-700 flex items-center justify-center shrink-0 border border-navy-200">
                  <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                  </svg>
                </span>
                <span>Pilih dari Galeri Perangkat</span>
              </button>

              <!-- Batal -->
              <button
                type="button"
                @click="showActionSheet = false"
                class="w-full py-2.5 rounded-xl text-navy-500 font-semibold text-xs hover:bg-navy-200/60 active-press transition"
              >
                Tutup Pilihan
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
const isSquare = computed(() => props.aspectRatio === 'square');

const emptyTitle = computed(() => (isSquare.value ? 'Foto Belum Diunggah' : 'Dokumentasi Belum Diunggah'));
const emptyHint = computed(() =>
  isSquare.value
    ? 'Format JPG / PNG · Tampil kotak (1:1)'
    : 'Format JPG / PNG · Format 16:9 otomatis'
);

const aspectRatioClass = computed(() => {
  if (props.aspectRatio === 'square') return 'aspect-square max-w-[15rem] mx-auto';
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
