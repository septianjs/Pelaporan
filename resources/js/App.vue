<template>
  <div class="min-h-screen bg-slate-900 flex justify-center items-start sm:py-6">
    <!-- Mobile Frame Container -->
    <div class="w-full max-w-md min-h-screen sm:min-h-[844px] bg-slate-50 flex flex-col shadow-2xl sm:rounded-[36px] sm:overflow-hidden sm:border-[6px] sm:border-slate-800 relative">
      <!-- Top App Bar -->
      <TopHeader :title="currentTitle" />

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
import { computed } from 'vue';
import { useRoute } from 'vue-router';
import TopHeader from './components/TopHeader.vue';
import BottomNav from './components/BottomNav.vue';

const route = useRoute();

const currentTitle = computed(() => {
  return route.meta?.title || 'Pengawasan Lingkungan';
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

