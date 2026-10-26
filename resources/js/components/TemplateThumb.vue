
<template>
  <div class="w-full overflow-hidden rounded-lg border border-stone-200 bg-white">
    <img
      v-if="previewSrc"
      :key="previewSrc"
      :src="previewSrc"
      :alt="`Pratinjau ${name}`"
      class="block h-auto w-full object-contain"
      loading="lazy"
      @error="imageFailed = true"
    />

    <div
      v-else
      class="flex min-h-32 items-center justify-center bg-stone-100 p-4 text-center text-sm text-stone-500"
    >
      Pratinjau gambar belum tersedia
    </div>
  </div>
</template>

<script setup>
import { computed, ref, watch } from 'vue';

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

watch(
  () => [props.preview, props.name, props.theme],
  () => {
    imageFailed.value = false;
  }
);

const previewSrc = computed(() => {
  if (imageFailed.value) {
    return '';
  }

  const preview = props.preview?.trim();

  // 1. Prioritaskan thumbnail yang dikirim backend.
  if (preview) {
    if (/^(https?:)?\/\//i.test(preview)) {
      return preview;
    }

    if (preview.startsWith('/')) {
      return preview;
    }

    // Path relatif dari storage Laravel.
    if (preview.startsWith('template-previews/')) {
      return `/storage/${preview}`;
    }

    if (preview.startsWith('previews/')) {
      return `/storage/${preview}`;
    }

    return `/storage/previews/${preview}`;
  }

  // 2. Cadangan berdasarkan nomor template pada namanya.
  // Tidak membuat gambar palsu untuk template yang belum memiliki preview.
  const match = props.name.match(/(?:template|templat)\s*0?(\d+)/i);

  if (match) {
    const number = Number(match[1]);

    if (number >= 1 && number <= 4) {
      return `/storage/template-previews/template-${String(number).padStart(2, '0')}.png`;
    }
  }

  // 3. Tidak ada thumbnail yang bisa dipastikan.
  return '';
});
</script>
