<template>
  <header class="sticky top-0 z-30 bg-blue-900 text-white shadow-md safe-top">
    <div class="px-4 py-3 flex items-center justify-between">
      <div class="flex items-center space-x-3">
        <!-- Back button if not home -->
        <button
          v-if="showBack"
          @click="goBack"
          class="p-2 -ml-2 rounded-full hover:bg-blue-800 active:bg-blue-950 transition"
          aria-label="Kembali"
        >
          <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7" />
          </svg>
        </button>

        <!-- Logo / Icon -->
        <div v-else class="w-9 h-9 rounded-xl bg-gradient-to-tr from-amber-400 to-amber-200 flex items-center justify-center shadow-sm">
          <svg class="w-5 h-5 text-blue-950" fill="currentColor" viewBox="0 0 24 24">
            <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
          </svg>
        </div>

        <div>
          <h1 class="text-xs font-semibold tracking-wider uppercase text-blue-200">BPSDM</h1>
          <p class="text-sm font-bold leading-tight truncate max-w-[220px]">
            {{ title || 'Pengawasan Lingkungan' }}
          </p>
        </div>
      </div>

      <!-- Right Action / Status Badge -->
      <div class="flex items-center space-x-1.5">
        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-800 text-blue-100 border border-blue-700/50">
          <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 mr-1 animate-pulse"></span>
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

