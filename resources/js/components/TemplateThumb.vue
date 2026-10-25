
<template>
  <div class="w-full overflow-hidden rounded-lg border border-stone-200 bg-white">
    <img
      v-if="previewSrc"
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
  theme: { type: Object, default: null },
  name: { type: String, default: '' },
  preview: { type: String, default: '' },
});

const imageFailed = ref(false);

watch(
  () => [props.preview, props.name],
  () => {
    imageFailed.value = false;
  }
);

const previewSrc = computed(() => {
  if (imageFailed.value) return '';

  if (props.preview) {
    return /^(https?:)?\/\/|^\//.test(props.preview)
      ? props.preview
      : `/storage/previews/${props.preview}`;
  }

  // Cadangan untuk template yang namanya mengandung nomor 01–10.
  const match = props.name.match(/(?:template|templat)\s*0?(\d+)/i);

  if (!match) return '';

  const number = Number(match[1]);

  if (number < 1 || number > 10) return '';

  return `/storage/previews/${number}tem.png`;
});
</script>
