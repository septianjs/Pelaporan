<template>
  <transition name="splash-fade">
    <div v-if="visible" class="splash-overlay">
      <!-- Ambient glow behind logo -->
      <div class="splash-glow"></div>

      <!-- Logo container with entrance animation -->
      <div class="splash-logo-group">
        <!-- The D-shape body -->
        <svg
          class="splash-logo"
          xmlns="http://www.w3.org/2000/svg"
          viewBox="0 0 120 120"
          width="120"
          height="120"
        >
          <!-- Main D shape -->
          <path
            class="logo-body"
            d="M34 12H62C92 12 112 32 112 60C112 88 92 108 62 108H34C28.5 108 24 103.5 24 98V22C24 16.5 28.5 12 34 12Z"
            fill="#14213D"
          />
          <!-- Title bar -->
          <rect class="logo-bar" x="40" y="25" width="30" height="6" rx="3" fill="#FFFFFF" fill-opacity="0.65"/>
          <!-- Bar 1 (white short) -->
          <rect class="logo-col logo-col-1" x="40" y="70" width="14" height="22" rx="3.5" fill="#FFFFFF"/>
          <!-- Bar 2 (white medium) -->
          <rect class="logo-col logo-col-2" x="60" y="54" width="14" height="38" rx="3.5" fill="#FFFFFF"/>
          <!-- Bar 3 (amber tall) -->
          <rect class="logo-col logo-col-3" x="80" y="38" width="14" height="54" rx="3.5" fill="#F5A524"/>
        </svg>
      </div>

      <!-- App name with typewriter-fade effect -->
      <div class="splash-text-group">
        <p class="splash-app-name">Decora</p>
        <p class="splash-tagline">Pelaporan Pengawasan Lingkungan</p>
      </div>

      <!-- Orbiting dots -->
      <div class="orbit-ring">
        <span class="orbit-dot orbit-dot-1"></span>
        <span class="orbit-dot orbit-dot-2"></span>
        <span class="orbit-dot orbit-dot-3"></span>
      </div>
    </div>
  </transition>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { SplashScreen as CapSplash } from '@capacitor/splash-screen';

const visible = ref(true);

onMounted(async () => {
  // Hide native Capacitor splash as soon as Vue is ready
  try {
    await CapSplash.hide();
  } catch (_) {
    // Not on native — ignore
  }

  // Show animated splash for 2.8 seconds, then fade out
  setTimeout(() => {
    visible.value = false;
  }, 2800);
});
</script>

<style scoped>
/* ── Overlay ── */
.splash-overlay {
  position: fixed;
  inset: 0;
  z-index: 9999;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  background: linear-gradient(160deg, #0D1A30 0%, #14213D 45%, #1B2C4D 100%);
  overflow: hidden;
}

/* ── Fade-out transition ── */
.splash-fade-leave-active {
  transition: opacity 0.5s ease;
}
.splash-fade-leave-to {
  opacity: 0;
}

/* ── Ambient Glow ── */
.splash-glow {
  position: absolute;
  width: 260px;
  height: 260px;
  border-radius: 50%;
  background: radial-gradient(circle, rgba(245, 165, 36, 0.12) 0%, transparent 70%);
  animation: glow-pulse 2.5s ease-in-out infinite;
}

@keyframes glow-pulse {
  0%, 100% { transform: scale(1); opacity: 0.6; }
  50% { transform: scale(1.25); opacity: 1; }
}

/* ── Logo Group ── */
.splash-logo-group {
  position: relative;
  z-index: 2;
  animation: logo-entrance 0.8s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes logo-entrance {
  0% {
    opacity: 0;
    transform: scale(0.5) translateY(20px);
  }
  100% {
    opacity: 1;
    transform: scale(1) translateY(0);
  }
}

.splash-logo {
  width: 110px;
  height: 110px;
  filter: drop-shadow(0 8px 24px rgba(0,0,0,0.35));
}

/* ── Logo D-body subtle float ── */
.logo-body {
  animation: body-float 3s ease-in-out infinite;
}

@keyframes body-float {
  0%, 100% { transform: translateY(0); }
  50% { transform: translateY(-2px); }
}

/* ── Title bar shimmer ── */
.logo-bar {
  animation: bar-shimmer 2s ease-in-out infinite;
}

@keyframes bar-shimmer {
  0%, 100% { fill-opacity: 0.4; }
  50% { fill-opacity: 0.85; }
}

/* ── Column bars grow up (staggered) ── */
.logo-col {
  transform-origin: bottom center;
  animation: col-grow 0.7s cubic-bezier(0.16, 1, 0.3, 1) both;
}
.logo-col-1 { animation-delay: 0.35s; }
.logo-col-2 { animation-delay: 0.50s; }
.logo-col-3 { animation-delay: 0.65s; }

@keyframes col-grow {
  0% {
    transform: scaleY(0);
    opacity: 0;
  }
  100% {
    transform: scaleY(1);
    opacity: 1;
  }
}

/* ── Text group ── */
.splash-text-group {
  position: relative;
  z-index: 2;
  text-align: center;
  margin-top: 24px;
  animation: text-fade-up 0.6s 0.9s cubic-bezier(0.16, 1, 0.3, 1) both;
}

@keyframes text-fade-up {
  0% {
    opacity: 0;
    transform: translateY(12px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}

.splash-app-name {
  font-size: 28px;
  font-weight: 700;
  letter-spacing: 2px;
  color: #FFFFFF;
  margin: 0;
  text-transform: uppercase;
}

.splash-tagline {
  font-size: 12px;
  font-weight: 400;
  color: rgba(255, 255, 255, 0.5);
  margin: 6px 0 0;
  letter-spacing: 0.5px;
}

/* ── Orbiting Dots ── */
.orbit-ring {
  position: absolute;
  width: 200px;
  height: 200px;
  z-index: 1;
  animation: ring-spin 4s linear infinite;
}

@keyframes ring-spin {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(360deg); }
}

.orbit-dot {
  position: absolute;
  width: 5px;
  height: 5px;
  border-radius: 50%;
  background: rgba(245, 165, 36, 0.7);
}

.orbit-dot-1 {
  top: 0;
  left: 50%;
  transform: translateX(-50%);
  animation: dot-pulse 1.5s ease-in-out infinite;
}

.orbit-dot-2 {
  bottom: 15%;
  right: 5%;
  width: 4px;
  height: 4px;
  background: rgba(255, 255, 255, 0.35);
  animation: dot-pulse 1.5s 0.5s ease-in-out infinite;
}

.orbit-dot-3 {
  bottom: 15%;
  left: 5%;
  width: 3px;
  height: 3px;
  background: rgba(255, 255, 255, 0.2);
  animation: dot-pulse 1.5s 1s ease-in-out infinite;
}

@keyframes dot-pulse {
  0%, 100% { opacity: 0.4; transform: scale(1); }
  50% { opacity: 1; transform: scale(1.5); }
}
</style>

