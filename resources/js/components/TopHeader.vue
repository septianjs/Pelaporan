<template>
  <header class="top-header-bar safe-top">
    <div class="top-header-inner">
      <div class="flex items-center space-x-3">
        <!-- Logo Decora -->
        <LogoMark :size="32" variant="inverse" class="shrink-0" />
        <div class="leading-tight">
          <div class="flex items-center gap-1.5">
            <span class="brand-eyebrow">Decora</span>
            <span class="brand-dot">•</span>
            <span class="brand-subtext">Pengawasan Lingkungan</span>
          </div>
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

<style scoped>
.top-header-bar {
  position: sticky;
  top: 0;
  z-index: 30;
  background-color: #14213D;
  color: #EEF1F5;
  border-bottom: 1px solid #2A4068;
  box-shadow: 0 2px 12px rgba(10, 18, 14, 0.15);
}

.top-header-inner {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 0.75rem;
  padding-bottom: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: space-between;
}

.brand-eyebrow {
  font-size: 0.59375rem;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  font-weight: 700;
  color: #F5A524;
}

.brand-dot {
  color: #4A6798;
  font-size: 0.5625rem;
}

.brand-subtext {
  font-size: 0.59375rem;
  letter-spacing: 0.025em;
  color: #7089B5;
  font-weight: 500;
}
</style>

