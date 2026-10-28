<template>
  <div class="home-container">
    <!-- Header Hero Card — Architectural Institutional -->
    <div class="hero-card">
      <!-- Subtle ambient warm spotlight -->
      <div class="hero-spotlight-top"></div>
      <div class="hero-spotlight-bottom"></div>

      <!-- Institutional Eyebrow Badge -->
      <div class="hero-badge">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5" />
        </svg>
        <span>Decora</span>
      </div>

      <!-- Title Block -->
      <div class="space-y-1">
        <p class="hero-subtitle">
          Sistem Pengawasan Sarana & Prasarana
        </p>
        <h1 class="hero-title">
          Pengawasan Kebersihan & Lingkungan
        </h1>
      </div>

      <!-- Real-time Indonesian Date Bar -->
      <div class="hero-date-bar">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-[#F5A524]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
          <span class="font-medium tabular-nums">{{ indonesianToday }}</span>
        </div>
      </div>
    </div>

    <!-- MAIN ACTION: Double-Bezel CTA -->
    <div class="cta-bezel-outer">
      <router-link
        to="/create"
        class="cta-bezel-inner group"
      >
        <div class="flex items-center justify-between">
          <div>
            <span class="cta-eyebrow">Inspeksi Hari Ini</span>
            <h2 class="cta-heading">Buat Laporan Baru</h2>
            <p class="cta-description">Pemeriksaan 5 titik & dokumentasi foto 16:9</p>
          </div>
          <div class="cta-icon-box">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
          </div>
        </div>
      </router-link>
    </div>

    <!-- ASYMMETRICAL BENTO GRID: Arsip Riwayat & Katalog Template -->
    <div class="grid grid-cols-2 gap-3">
      <!-- BENTO 1: Arsip Riwayat -->
      <router-link
        to="/history"
        class="bento-card"
      >
        <div class="bento-icon bento-icon-navy">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="bento-title">Arsip Riwayat</h3>
        <p class="bento-desc">Daftar laporan, unduh & bagikan PDF</p>
        <div class="bento-footer text-[#1B2C4D]">
          <span>Buka Arsip</span>
          <span>&gt;</span>
        </div>
      </router-link>

      <!-- BENTO 2: Desain Template -->
      <router-link
        to="/templates"
        class="bento-card"
      >
        <div class="bento-icon bento-icon-amber">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
        </div>
        <h3 class="bento-title">Format Template</h3>
        <p class="bento-desc">Pilih desain PDF 16:9 atau buat template sendiri</p>
        <div class="bento-footer text-[#84631D]">
          <span>Katalog</span>
          <span>&gt;</span>
        </div>
      </router-link>
    </div>

    <!-- API Server Status Monitor -->
    <div
      class="status-bar"
      :class="apiStatus.online ? 'status-bar-online' : 'status-bar-offline'"
    >
      <span class="flex items-center gap-2">
        <span
          class="w-2 h-2 rounded-full"
          :class="apiStatus.online ? 'bg-[#34D399]' : 'bg-[#EF4444] animate-pulse'"
        ></span>
        <span class="font-medium">
          {{ apiStatus.online ? 'Server API Terhubung' : apiStatus.message || 'Server Tidak Terjangkau' }}
        </span>
      </span>
      <button
        type="button"
        @click="showServerConfig = true"
        class="status-btn"
        :class="apiStatus.online ? 'status-btn-online' : 'status-btn-offline'"
      >
        {{ apiStatus.online ? 'Pengaturan' : 'Atur Server' }}
      </button>
    </div>

    <!-- Subkomponen Modal Konfigurasi Server -->
    <ServerConfigModal
      v-model="showServerConfig"
      :api-status="apiStatus"
      @status-changed="checkApi"
    />
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api, { isApiUnconfigured } from '../services/api';
import ServerConfigModal from '../components/ServerConfigModal.vue';

const apiStatus = ref({
  online: false,
  message: 'Memeriksa koneksi...',
});

const showServerConfig = ref(false);

// Indonesian Date
const indonesianToday = computed(() => {
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const now = new Date();
  const dayName = days[now.getDay()];
  const date = now.getDate();
  const monthName = months[now.getMonth()];
  const year = now.getFullYear();
  return `${dayName}, ${date} ${monthName} ${year}`;
});

const checkApi = async () => {
  try {
    const res = await api.get('/ping', { timeout: 8000 });
    if (res.data && res.data.status === 'ok') {
      apiStatus.value = {
        online: true,
        message: res.data.message || 'Terhubung',
      };
    }
  } catch (err) {
    console.warn('API ping warning:', err.message);
    apiStatus.value = {
      online: false,
      message: 'Server tidak terjangkau — periksa koneksi WiFi & alamat server',
    };
  }
};

onMounted(() => {
  checkApi();

  // Auto-show config dialog jika API belum ter-konfigurasi di HP asli
  if (isApiUnconfigured()) {
    apiStatus.value = {
      online: false,
      message: 'Belum dikonfigurasi — atur alamat server',
    };
    showServerConfig.value = true;
  }
});
</script>

<style scoped>
.home-container {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 1.25rem;
  padding-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Hero Card */
.hero-card {
  background-color: #14213D;
  border-radius: 1.5rem;
  padding: 1.5rem;
  color: #FFFFFF;
  box-shadow: 0 12px 32px rgba(10, 18, 14, 0.15);
  position: relative;
  overflow: hidden;
  border: 1px solid #2A4068;
}

.hero-spotlight-top {
  position: absolute;
  right: -2rem;
  top: -2rem;
  width: 11rem;
  height: 11rem;
  background-color: rgba(245, 165, 36, 0.1);
  border-radius: 9999px;
  filter: blur(48px);
  pointer-events: none;
}

.hero-spotlight-bottom {
  position: absolute;
  left: -2.5rem;
  bottom: -2.5rem;
  width: 11rem;
  height: 11rem;
  background-color: rgba(30, 55, 102, 0.3);
  border-radius: 9999px;
  filter: blur(32px);
  pointer-events: none;
}

.hero-badge {
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.25rem 0.75rem;
  border-radius: 9999px;
  background-color: rgba(255, 255, 255, 0.05);
  backdrop-filter: blur(12px);
  border: 1px solid rgba(255, 255, 255, 0.1);
  color: #F5A524;
  font-size: 0.625rem;
  font-weight: 600;
  letter-spacing: 0.14em;
  text-transform: uppercase;
  margin-bottom: 0.75rem;
}

.hero-subtitle {
  font-size: 0.6875rem;
  font-weight: 500;
  letter-spacing: 0.05em;
  color: #9DB0D0;
}

.hero-title {
  font-size: 1.5rem;
  font-weight: 700;
  letter-spacing: -0.025em;
  color: #FFFFFF;
  line-height: 1.25;
}

.hero-date-bar {
  margin-top: 1.25rem;
  padding-top: 0.875rem;
  border-top: 1px solid rgba(255, 255, 255, 0.1);
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.75rem;
  color: #9DB0D0;
}

/* Double-Bezel CTA */
.cta-bezel-outer {
  padding: 0.25rem;
  border-radius: 1.5rem;
  background-color: rgba(199, 210, 229, 0.6);
  border: 1px solid rgba(157, 176, 208, 0.6);
}

.cta-bezel-inner {
  display: block;
  position: relative;
  overflow: hidden;
  border-radius: 20px;
  background-color: #1B2C4D;
  padding: 1.25rem;
  color: #FFFFFF;
  box-shadow: 0 8px 20px rgba(20, 36, 28, 0.18);
  border: 1px solid #35507D;
  transition: all 0.2s ease;
  text-decoration: none;
}

.cta-bezel-inner:active {
  transform: scale(0.98);
}

.cta-eyebrow {
  font-size: 0.59375rem;
  font-weight: 700;
  text-transform: uppercase;
  letter-spacing: 0.16em;
  color: #F5A524;
}

.cta-heading {
  font-size: 1.125rem;
  font-weight: 700;
  letter-spacing: -0.025em;
  color: #FFFFFF;
  line-height: 1.375;
}

.cta-description {
  font-size: 0.75rem;
  color: #9DB0D0;
  margin-top: 0.125rem;
}

.cta-icon-box {
  width: 3rem;
  height: 3rem;
  border-radius: 1rem;
  background-color: rgba(255, 255, 255, 0.1);
  backdrop-filter: blur(12px);
  display: flex;
  align-items: center;
  justify-content: center;
  color: #F5A524;
  border: 1px solid rgba(255, 255, 255, 0.1);
  box-shadow: inset 0 2px 4px 0 rgba(0, 0, 0, 0.06);
}

/* Asymmetrical Bento Grid */
.bento-card {
  display: block;
  border-radius: 1rem;
  background-color: #FFFFFF;
  padding: 1rem;
  border: 1px solid #DDE0E6;
  box-shadow: 0 2px 8px rgba(28, 25, 23, 0.03);
  text-decoration: none;
  transition: border-color 0.15s ease, transform 0.15s ease;
}

.bento-card:active {
  transform: scale(0.98);
}

.bento-icon {
  width: 2.5rem;
  height: 2.5rem;
  border-radius: 0.75rem;
  display: flex;
  align-items: center;
  justify-content: center;
  margin-bottom: 0.75rem;
}

.bento-icon-navy {
  background-color: #E7ECF5;
  color: #1B2C4D;
  border: 1px solid #D0D8E8;
}

.bento-icon-amber {
  background-color: #FAF5E8;
  color: #84631D;
  border: 1px solid #ECE0C2;
}

.bento-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #14213D;
  letter-spacing: -0.025em;
}

.bento-desc {
  font-size: 0.6875rem;
  color: #4A6798;
  margin-top: 0.25rem;
  line-height: 1.375;
}

.bento-footer {
  margin-top: 0.75rem;
  padding-top: 0.625rem;
  border-top: 1px solid #E4EAF3;
  display: flex;
  align-items: center;
  justify-content: space-between;
  font-size: 0.625rem;
  font-weight: 600;
}

/* API Status Monitor Bar */
.status-bar {
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 0.625rem 0.875rem;
  border-radius: 0.75rem;
  font-size: 0.6875rem;
  border-width: 1px;
  border-style: solid;
  transition: all 0.2s ease;
}

.status-bar-online {
  background-color: #EAF3EC;
  color: #1B2C4D;
  border-color: #C8E0D0;
}

.status-bar-offline {
  background-color: #FDF0EE;
  color: #8C3A2E;
  border-color: #F2CAC4;
}

.status-btn {
  font-family: var(--font-mono, monospace);
  font-size: 0.625rem;
  font-weight: 600;
  padding: 0.125rem 0.5rem;
  border-radius: 0.375rem;
  cursor: pointer;
  border: none;
  transition: background-color 0.15s ease;
}

.status-btn-online {
  color: #1B2C4D;
  background-color: #CFDAEE;
}

.status-btn-online:hover {
  background-color: #BACAE7;
}

.status-btn-offline {
  color: #FFFFFF;
  background-color: #A6382C;
}

.status-btn-offline:hover {
  background-color: #8F2F24;
}
</style>
