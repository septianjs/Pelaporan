<template>
  <header class="sticky top-0 z-30 bg-[#14213D] text-[#EEF1F5] border-b border-[#2A4068] shadow-[0_2px_12px_rgba(10,18,14,0.15)] safe-top">
    <div class="px-4 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <!-- Back button if not home -->
        <button
          v-if="showBack"
          @click="goBack"
          class="w-8 h-8 -ml-1 rounded-lg bg-[#263B61] hover:bg-[#2A4068] text-[#C7D2E5] flex items-center justify-center active-press transition"
          aria-label="Kembali"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Logo Decora -->
        <LogoMark v-else :size="32" variant="inverse" class="shrink-0" />

        <div class="leading-tight">
          <div class="flex items-center gap-1.5">
            <span class="text-[9.5px] uppercase tracking-[0.16em] font-bold text-[#F5A524]">BPSDM</span>
            <span class="text-navy-500 text-[9px]">•</span>
            <span class="text-[9.5px] tracking-wide text-navy-400 font-medium">Pengawasan Lingkungan</span>
          </div>
          <p class="text-sm font-semibold text-white tracking-tight truncate max-w-[210px] mt-0.5">
            {{ title || 'Beranda' }}
          </p>
        </div>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
import LogoMark from './LogoMark.vue';
import { useRoute, useRouter } from 'vue-router';

const props = defineProps({
  title: {
    type: String,
    default: '',
  },
});

const route = useRoute();
const router = useRouter();

const showBack = computed(() => route.path !== '/');

const goBack = () => {
  if (window.history.length > 1) {
    router.back();
  } else {
    router.push('/');
  }
};
</script>

