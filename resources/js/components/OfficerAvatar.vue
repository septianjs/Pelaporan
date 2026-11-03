<template>
  <div class="officer-avatar-box">
    <img
      v-if="photoSrc && !failed"
      :src="photoSrc"
      alt="Foto Petugas"
      class="w-full h-full object-cover"
      @error="failed = true"
    />
    <div v-else class="w-full h-full flex items-center justify-center text-navy-400">
      <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
      </svg>
    </div>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue';
import { resolveAssetUrl } from '../services/api.js';
import { loadImageSrc } from '../services/ngrokWorkaround.js';

const props = defineProps({
  photoUrl: {
    type: String,
    default: '',
  },
});

const photoSrc = ref('');
const failed = ref(false);
let token = 0;

watch(
  () => props.photoUrl,
  async (url) => {
    const currentToken = ++token;
    failed.value = false;
    photoSrc.value = '';

    if (!url) return;

    const resolved = resolveAssetUrl(url);
    if (!resolved) return;

    try {
      const src = await loadImageSrc(resolved);
      if (currentToken === token) {
        photoSrc.value = src;
      }
    } catch {
      if (currentToken === token) {
        // Fallback to resolved URL directly if loadImageSrc fails
        photoSrc.value = resolved;
      }
    }
  },
  { immediate: true }
);
</script>

<style scoped>
.officer-avatar-box {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.75rem;
  background-color: #E3E6EC;
  border: 1px solid #CBD1DB;
  overflow: hidden;
  flex-shrink: 0;
}
</style>

