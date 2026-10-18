<template>
  <header class="sticky top-0 z-30 bg-[#14251D] text-[#F5F4EE] border-b border-[#233F31] shadow-[0_2px_12px_rgba(10,18,14,0.15)] safe-top">
    <div class="px-4 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <!-- Back button if not home -->
        <button
          v-if="showBack"
          @click="goBack"
          class="w-8 h-8 -ml-1 rounded-lg bg-[#1D3529] hover:bg-[#254435] text-[#D1C7B7] flex items-center justify-center active-press transition"
          aria-label="Kembali"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Institutional Insignia Mark -->
        <div v-else class="w-8 h-8 rounded-lg bg-[#1E372B] border border-[#2D503F] text-[#D8C79C] flex items-center justify-center shadow-inner">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
          </svg>
        </div>

        <div class="leading-tight">
          <div class="flex items-center gap-1.5">
            <span class="text-[9.5px] uppercase tracking-[0.16em] font-bold text-[#C8B788]">BPSDM</span>
            <span class="text-stone-500 text-[9px]">•</span>
            <span class="text-[9.5px] tracking-wide text-stone-400 font-medium">Pengawasan Lingkungan</span>
          </div>
          <p class="text-sm font-semibold text-white tracking-tight truncate max-w-[210px] mt-0.5">
            {{ title || 'Beranda' }}
          </p>
        </div>
      </div>

      <!-- Right Action / Status Badge -->
      <div class="flex items-center space-x-1.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-[#1B3629] text-[#97C5AC] border border-[#2C523F]">
          <span class="w-1.5 h-1.5 rounded-full bg-[#34D399] mr-1.5 animate-pulse"></span>
          Aktif
        </span>
      </div>
    </div>
  </header>
</template>

<script setup>
import { computed } from 'vue';
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

