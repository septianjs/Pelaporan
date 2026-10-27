<template>
  <div class="h-dvh sm:h-auto sm:min-h-screen bg-[#E3E6EC] flex justify-center items-start sm:py-6 selection:bg-[#1B2C4D] selection:text-white">
    <!-- Main Frame Container: Seamless mobile edge-to-edge, refined desktop tablet dock -->
    <div class="w-full max-w-md h-dvh sm:h-[850px] bg-[#F5F7FA] flex flex-col overflow-hidden sm:rounded-3xl sm:border sm:border-navy-300/70 shadow-[0_20px_50px_-15px_rgba(28,25,23,0.08)] relative">
      <!-- Top App Bar -->
      <TopHeader :title="currentTitle" />

      <!-- ── OFFLINE & SYNC BANNER (Minimalist Utilitarian) ── -->
      <div v-if="!isOnline" class="bg-[#FAF2E6] border-b border-[#ECD9BA] text-[#7A4B10] text-[11px] font-medium px-4 py-2 flex items-center justify-between z-30 transition-all">
        <div class="flex items-center gap-2 min-w-0">
          <span class="w-2 h-2 rounded-full bg-[#C27D26] animate-pulse shrink-0"></span>
          <span class="truncate">Mode Offline — Data tersimpan di memori perangkat</span>
        </div>
        <span v-if="queuedCount > 0" class="bg-[#EEDBBA] text-[#5C3608] px-2 py-0.5 rounded-full text-[10px] font-semibold tabular-nums shrink-0 ml-2">
          {{ queuedCount }} antrian
        </span>
      </div>

      <div v-else-if="isSyncing" class="bg-[#E5EAF4] border-b border-[#C8D4E9] text-[#13264A] text-[11px] font-medium px-4 py-2 flex items-center gap-2 z-30">
        <svg class="w-3.5 h-3.5 animate-spin shrink-0 text-[#1C3970]" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
          <path class="opacity-80" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span class="truncate">Menyinkronkan {{ queuedCount }} laporan ke server pusat...</span>
      </div>

      <div v-else-if="syncSuccessMessage" class="bg-[#EAF3EC] border-b border-[#CCE4D3] text-[#1E4826] text-[11px] font-medium px-4 py-2 flex items-center justify-between z-30">
        <div class="flex items-center gap-2 truncate">
          <svg class="w-3.5 h-3.5 text-[#1D3B72] shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <span class="truncate">{{ syncSuccessMessage }}</span>
        </div>
        <button type="button" @click="syncSuccessMessage = ''" class="text-navy-500 hover:text-navy-800 px-1 font-bold">✕</button>
      </div>

      <!-- Pending Queue Bar (Online but pending queue exists) -->
      <div v-else-if="isOnline && queuedCount > 0" class="bg-[#E9ECF2] border-b border-[#CFD5DF] text-[#3A3E46] text-[11px] font-medium px-4 py-2 flex items-center justify-between z-30">
        <span class="truncate font-medium"><span class="tabular-nums font-semibold">{{ queuedCount }}</span> laporan offline siap dikirim</span>
        <button
          type="button"
          @click="triggerManualSync"
          class="bg-[#1B2C4D] text-white px-2.5 py-0.5 rounded-lg text-[10px] font-semibold active-press transition"
        >
          Kirim Sekarang
        </button>
      </div>

      <!-- Scrollable Main Content -->
      <main id="app-scroll" class="flex-1 min-h-0 overflow-y-auto overscroll-contain pb-4">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Bottom Navigation Bar -->
      <BottomNav class="shrink-0" />
    </div>
  </div>
</template>

<script setup>
import { computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import TopHeader from './components/TopHeader.vue';
import BottomNav from './components/BottomNav.vue';
import api from './services/api.js';
import {
  isOnline,
  isSyncing,
  queuedCount,
  syncSuccessMessage,
  setupOfflineListeners,
  syncOfflineQueue,
} from './services/offlineStorage.js';

const route = useRoute();

const currentTitle = computed(() => {
  return route.meta?.title || 'Pengawasan Lingkungan';
});

const triggerManualSync = () => {
  syncOfflineQueue(api);
};

onMounted(() => {
  setupOfflineListeners(api);
});
</script>

<style>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.15s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
