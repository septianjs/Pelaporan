<script setup>
import { computed } from 'vue'

const props = defineProps({
  size: { type: [Number, String], default: 40 },
  // 'default' = D navy | 'inverse' = D putih (untuk latar navy) | 'icon' = kotak navy
  variant: { type: String, default: 'default' },
})

const c = computed(() => {
  if (props.variant === 'inverse') return { d: '#FFFFFF', bar: '#14213D', bg: null }
  if (props.variant === 'icon') return { d: '#FFFFFF', bar: '#14213D', bg: '#14213D' }
  return { d: '#14213D', bar: '#FFFFFF', bg: null }
})
</script>

<template>
  <svg :width="size" :height="size" viewBox="0 0 120 120" role="img" aria-label="Decora">
    <rect v-if="c.bg" width="120" height="120" rx="28" :fill="c.bg" />
    <path
      d="M34 12H62C92 12 112 32 112 60C112 88 92 108 62 108H34C28.5 108 24 103.5 24 98V22C24 16.5 28.5 12 34 12Z"
      :fill="c.d"
      :transform="c.bg ? 'translate(60 60) scale(0.8) translate(-68 -60)' : undefined"
    />
    <g :transform="c.bg ? 'translate(60 60) scale(0.8) translate(-68 -60)' : undefined">
      <rect x="40" y="25" width="30" height="6" rx="3" :fill="c.bar" fill-opacity="0.65" />
      <rect x="40" y="70" width="14" height="22" rx="3.5" :fill="c.bar" />
      <rect x="60" y="54" width="14" height="38" rx="3.5" :fill="c.bar" />
      <rect x="80" y="38" width="14" height="54" rx="3.5" fill="#F5A524" />
    </g>
  </svg>
</template>
