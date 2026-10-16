<template>
  <div class="min-h-screen bg-slate-900 flex justify-center items-start sm:py-6">
    <!-- Mobile Frame Container -->
    <div class="w-full max-w-md min-h-screen sm:min-h-[844px] bg-slate-50 flex flex-col shadow-2xl sm:rounded-[36px] sm:overflow-hidden sm:border-[6px] sm:border-slate-800 relative">
      <!-- Top App Bar -->
      <TopHeader :title="currentTitle" />

      <!-- ── OFFLINE & SYNC BANNER ── -->
      <div v-if="!isOnline" class="bg-amber-500 text-white text-[11px] font-semibold px-4 py-2 flex items-center justify-between shadow-sm z-30 transition-all">
        <div class="flex items-center gap-1.5 min-w-0">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 5.636a9 9 0 010 12.728m-3.536-3.536a5 5 0 000-7.072M6 18L18 6"/>
          </svg>
          <span class="truncate">Mode Offline — Laporan disimpan lokal</span>
        </div>
        <span v-if="queuedCount > 0" class="bg-white/20 text-white px-2 py-0.5 rounded text-[10px] font-bold shrink-0 ml-2">
          {{ queuedCount }} antrian
        </span>
      </div>

      <div v-else-if="isSyncing" class="bg-blue-600 text-white text-[11px] font-semibold px-4 py-2 flex items-center gap-2 shadow-sm z-30 animate-pulse">
        <svg class="w-4 h-4 animate-spin shrink-0" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span class="truncate">Menyinkronkan {{ queuedCount }} laporan offline ke server...</span>
      </div>

      <div v-else-if="syncSuccessMessage" class="bg-emerald-600 text-white text-[11px] font-semibold px-4 py-2 flex items-center justify-between shadow-sm z-30">
        <div class="flex items-center gap-1.5 truncate">
          <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <span class="truncate">{{ syncSuccessMessage }}</span>
        </div>
        <button type="button" @click="syncSuccessMessage = ''" class="text-white/80 hover:text-white px-1">✕</button>
      </div>

      <!-- Pending Queue Bar (Online but pending queue exists) -->
      <div v-else-if="isOnline && queuedCount > 0" class="bg-indigo-600 text-white text-[11px] font-semibold px-4 py-1.5 flex items-center justify-between shadow-sm z-30">
        <span class="truncate">{{ queuedCount }} laporan tersimpan di antrian lokal.</span>
        <button
          type="button"
          @click="triggerManualSync"
          class="bg-white text-indigo-700 px-2 py-0.5 rounded text-[10px] font-bold active:scale-95 transition"
        >
          Sinkronkan
        </button>
      </div>

      <!-- Scrollable Main Content -->
      <main class="flex-1 overflow-y-auto pb-6">
        <router-view v-slot="{ Component }">
          <transition name="fade" mode="out-in">
            <component :is="Component" />
          </transition>
        </router-view>
      </main>

      <!-- Bottom Navigation Bar -->
      <BottomNav />
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
