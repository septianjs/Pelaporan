<template>
  <div class="w-full overflow-hidden rounded-lg border border-navy-200 bg-white">
    <img
      v-if="previewSrc && !imageFailed"
      :key="previewSrc"
      :src="previewSrc"
      :alt="`Pratinjau ${name}`"
      class="block h-auto w-full object-contain"
      loading="lazy"
      @error="imageFailed = true"
    />

    <div
      v-else
      class="flex min-h-32 items-center justify-center bg-navy-100 p-4 text-center text-sm text-navy-500"
    >
      Pratinjau gambar belum tersedia
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';
import { resolveAssetUrl } from '../services/api';
// SEMENTARA (ngrok): lihat services/ngrokWorkaround.js. Di server non-ngrok fungsi ini
// langsung mengembalikan URL aslinya.
import { loadImageSrc } from '../services/ngrokWorkaround';

const props = defineProps({
  theme: {
    type: Object,
    default: null,
  },
  name: {
    type: String,
    default: '',
  },
  preview: {
    type: String,
    default: '',
  },
});

const imageFailed = ref(false);

const rawSrc = computed(() => {
  const preview = props.preview?.trim();

  if (preview) {
    if (preview.startsWith('template-previews/') || preview.startsWith('previews/')) {
      return resolveAssetUrl(`/storage/${preview}`);
    }
    if (/^(https?:)?\/\//i.test(preview) || preview.startsWith('/')) {
      return resolveAssetUrl(preview);
    }
    return resolveAssetUrl(`/storage/previews/${preview}`);
  }

  const match = props.name.match(/(?:template|templat)\s*0?(\d+)/i);
  if (match) {
    const number = Number(match[1]);
    if (number >= 1 && number <= 4) {
      return resolveAssetUrl(`/storage/template-previews/template-${String(number).padStart(2, '0')}.png`);
    }
  }

  return '';
});

// Nilai akhir untuk <img :src>. Bukan ngrok = rawSrc apa adanya; ngrok = blob: URL.
const previewSrc = ref('');
let loadToken = 0;

watch(
  rawSrc,
  async (url) => {
    const token = ++loadToken;
    imageFailed.value = false;
    previewSrc.value = '';
    if (!url) return;
    try {
      const src = await loadImageSrc(url);
      if (token === loadToken) previewSrc.value = src;
    } catch {
      if (token === loadToken) imageFailed.value = true;
    }
  },
  { immediate: true }
);
</script>
