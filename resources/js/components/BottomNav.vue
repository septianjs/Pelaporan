<template>
  <nav class="bottom-nav-bar safe-bottom">
    <div class="nav-inner">
      <!-- Beranda -->
      <router-link
        to="/"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="nav-tab group"
        >
          <div
            :class="[
              'nav-icon-box',
              isActive ? 'icon-box-active' : 'icon-box-idle'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12l8.954-8.955a1.126 1.126 0 011.592 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
            </svg>
          </div>
          <span
            :class="[
              'nav-tab-label',
              isActive ? 'label-active' : 'label-idle'
            ]"
          >
            Beranda
          </span>
        </a>
      </router-link>

      <!-- Buat Laporan -->
      <router-link
        to="/create"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="nav-tab group"
        >
          <div
            :class="[
              'nav-icon-box',
              isActive ? 'icon-box-action-active' : 'icon-box-action-idle'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
          <span
            :class="[
              'nav-tab-label',
              isActive ? 'label-active' : 'label-action-idle'
            ]"
          >
            Input Laporan
          </span>
        </a>
      </router-link>

      <!-- Riwayat -->
      <router-link
        to="/history"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="nav-tab group"
        >
          <div
            :class="[
              'nav-icon-box',
              isActive ? 'icon-box-active' : 'icon-box-idle'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
          <span
            :class="[
              'nav-tab-label',
              isActive ? 'label-active' : 'label-idle'
            ]"
          >
            Arsip Riwayat
          </span>
        </a>
      </router-link>

      <!-- Template -->
      <router-link
        to="/templates"
        custom
        v-slot="{ href, navigate, isActive }"
      >
        <a
          :href="href"
          @click="navigate"
          class="nav-tab group"
        >
          <div
            :class="[
              'nav-icon-box',
              isActive ? 'icon-box-active' : 'icon-box-idle'
            ]"
          >
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
          </div>
          <span
            :class="[
              'nav-tab-label',
              isActive ? 'label-active' : 'label-idle'
            ]"
          >
            Template
          </span>
        </a>
      </router-link>
    </div>
  </nav>
</template>

<script setup>
</script>

<style scoped>
.bottom-nav-bar {
  position: sticky;
  bottom: 0;
  z-index: 30;
  background-color: rgba(255, 255, 255, 0.95);
  backdrop-filter: blur(12px);
  border-top: 1px solid #DFE2E8;
  box-shadow: 0 -4px 24px rgba(28, 25, 23, 0.04);
}

.nav-inner {
  display: flex;
  align-items: center;
  justify-content: space-around;
  height: 4rem;
  max-width: 28rem;
  margin: 0 auto;
  padding-left: 0.75rem;
  padding-right: 0.75rem;
}

.nav-tab {
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  flex: 1 1 0%;
  height: 100%;
  padding-top: 0.25rem;
  padding-bottom: 0.25rem;
  text-align: center;
  text-decoration: none;
  transition: all 0.15s ease;
}

.nav-tab:active {
  transform: scale(0.95);
}

.nav-icon-box {
  width: 2.25rem;
  height: 1.75rem;
  border-radius: 0.5rem;
  display: flex;
  align-items: center;
  justify-content: center;
  transition: all 0.2s ease;
}

.icon-box-active {
  background-color: #14213D;
  color: #FFFFFF;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.icon-box-idle {
  color: #4A6798;
}

.nav-tab:hover .icon-box-idle {
  color: #1B2C4D;
}

.icon-box-action-active {
  background-color: #14213D;
  color: #F5A524;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.icon-box-action-idle {
  background-color: #E9ECF3;
  color: #1B2C4D;
}

.nav-tab:hover .icon-box-action-idle {
  background-color: #DAE0EB;
}

.nav-tab-label {
  font-size: 0.625rem;
  letter-spacing: -0.025em;
  margin-top: 0.25rem;
  transition: color 0.15s ease;
}

.label-active {
  font-weight: 700;
  color: #14213D;
}

.label-idle {
  font-weight: 500;
  color: #4A6798;
}

.label-action-idle {
  font-weight: 600;
  color: #1B2C4D;
}
</style>
