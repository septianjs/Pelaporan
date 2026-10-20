<template>
  <!-- Miniatur desain template: sampul + salah satu titik pemantauan.
       Dirender dari CSS desain yang sama dengan PDF (bukan gambar statis). -->
  <div class="grid grid-cols-2 gap-1.5">
    <div class="rounded-lg overflow-hidden border border-stone-200 shadow-inner">
      <ReportSlide :theme="theme" :template-name="name" :slide="0" />
    </div>
    <div class="rounded-lg overflow-hidden border border-stone-200 shadow-inner">
      <ReportSlide :theme="theme" :template-name="name" :slide="1" />
    </div>
  </div>
</template>

<script setup>
import ReportSlide from './ReportSlide.vue';

defineProps({
  theme: { type: Object, default: null },
  name: { type: String, default: '' },
});
</script>
