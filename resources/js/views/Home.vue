<template>
  <div class="px-4 py-5 space-y-4">
    <!-- Header Hero Card — Architectural Institutional -->
    <div class="bg-[#14213D] rounded-3xl p-6 text-white shadow-[0_12px_32px_rgba(10,18,14,0.15)] relative overflow-hidden border border-[#2A4068]">
      <!-- Subtle ambient warm spotlight -->
      <div class="absolute -right-8 -top-8 w-44 h-44 bg-[#F5A524]/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-[#1E3766]/30 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Institutional Eyebrow Badge -->
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 backdrop-blur-md border border-white/10 text-[#F5A524] text-[10px] font-semibold tracking-[0.14em] uppercase mb-3">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5" />
        </svg>
        <span>BPSDM</span>
      </div>

      <!-- Title Block -->
      <div class="space-y-1">
        <p class="text-[11px] font-medium tracking-wider text-navy-300">
          Sistem Pengawasan Sarana & Prasarana
        </p>
        <h1 class="text-2xl font-bold tracking-tight text-white leading-tight">
          Pengawasan Kebersihan & Lingkungan
        </h1>
      </div>

      <!-- Real-time Indonesian Date Bar -->
      <div class="mt-5 pt-3.5 border-t border-white/10 flex items-center justify-between text-xs text-navy-300">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-[#F5A524]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
          <span class="font-medium tabular-nums">{{ indonesianToday }}</span>
        </div>
      </div>
    </div>

    <!-- MAIN ACTION: Double-Bezel CTA (Outer shell + Inner interactive card) -->
    <div class="p-1 rounded-3xl bg-navy-200/60 border border-navy-300/60">
      <router-link
        to="/create"
        class="block group relative overflow-hidden rounded-[20px] bg-[#1B2C4D] p-5 text-white shadow-[0_8px_20px_rgba(20,36,28,0.18)] active-press transition-all duration-200 border border-[#35507D]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-[#F5A524] border border-white/10 shadow-inner">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div>
              <span class="text-[9.5px] font-bold uppercase tracking-[0.16em] text-[#F5A524]">Inspeksi Hari Ini</span>
              <h2 class="text-lg font-bold tracking-tight text-white leading-snug">
                Buat Laporan Baru
              </h2>
              <p class="text-xs text-navy-300 mt-0.5">
                Pemeriksaan 5 titik & dokumentasi foto 16:9
              </p>
            </div>
          </div>
          <!-- Trailing button-in-button -->
          <div class="w-8 h-8 rounded-full bg-white/10 flex items-center justify-center text-white group-hover:translate-x-1 transition duration-200">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
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
        class="block rounded-2xl bg-white p-4 border border-[#DDE0E6] hover:border-[#14213D]/30 shadow-[0_2px_8px_rgba(28,25,23,0.03)] active-press transition"
      >
        <div class="w-10 h-10 rounded-xl bg-[#E7ECF5] text-[#1B2C4D] flex items-center justify-center border border-[#D0D8E8] mb-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-sm font-bold text-[#14213D] tracking-tight">
          Arsip Riwayat
        </h3>
        <p class="text-[11px] text-navy-500 mt-1 leading-snug">
            Daftar laporan, unduh & bagikan PDF
        </p>
        <div class="mt-3 pt-2.5 border-t border-navy-100 flex items-center justify-between text-[10px] font-semibold text-[#1B2C4D]">
          <span>Buka Arsip</span>
          <span>></span>
        </div>
      </router-link>

      <!-- BENTO 2: Desain Template -->
      <router-link
        to="/templates"
        class="block rounded-2xl bg-white p-4 border border-[#DDE0E6] hover:border-[#14213D]/30 shadow-[0_2px_8px_rgba(28,25,23,0.03)] active-press transition"
      >
        <div class="w-10 h-10 rounded-xl bg-[#FAF5E8] text-[#84631D] flex items-center justify-center border border-[#ECE0C2] mb-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
        </div>
        <h3 class="text-sm font-bold text-[#14213D] tracking-tight">
          Format Template
        </h3>
        <p class="text-[11px] text-navy-500 mt-1 leading-snug">
          Pilih desain PDF 16:9 atau buat template sendiri
        </p>
        <div class="mt-3 pt-2.5 border-t border-navy-100 flex items-center justify-between text-[10px] font-semibold text-[#84631D]">
          <span>Katalog</span>
          <span>></span>
        </div>
      </router-link>
    </div>

    <!-- API Server Status Monitor — with config action -->
    <div
      class="flex items-center justify-between px-3.5 py-2.5 rounded-xl text-[11px] border transition-all"
      :class="apiStatus.online
        ? 'bg-[#EAF3EC] text-[#1B2C4D] border-[#C8E0D0]'
        : 'bg-[#FDF0EE] text-[#8C3A2E] border-[#F2CAC4]'"
    >
      <span class="flex items-center gap-2">
        <span
          class="w-2 h-2 rounded-full"
          :class="apiStatus.online ? 'bg-[#34D399]' : 'bg-[#EF4444] animate-pulse'"
        ></span>
        <span class="font-medium">{{ apiStatus.online ? 'Server API Terhubung' : apiStatus.message || 'Server Tidak Terjangkau' }}</span>
      </span>
      <button type="button" @click="showServerConfig = true"
        class="font-mono text-[10px] font-semibold px-2 py-0.5 rounded-md transition"
        :class="apiStatus.online
          ? 'text-[#1B2C4D] bg-[#CFDAEE] hover:bg-[#BACAE7]'
          : 'text-white bg-[#A6382C] hover:bg-[#8F2F24]'"
      >
        {{ apiStatus.online ? 'Pengaturan' : 'Atur Server' }}
      </button>
    </div>

    <!-- SERVER CONFIG BOTTOM SHEET -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showServerConfig" class="fixed inset-0 z-50 bg-[#0B1428]/70 backdrop-blur-sm"
          @click="showServerConfig = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showServerConfig"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#F5F7FA] rounded-t-3xl shadow-2xl border-t border-[#D7DBE2]"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-[#C9CED7]"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-4">
            <div>
              <p class="text-sm font-bold text-[#14213D]">Konfigurasi Server API</p>
              <p class="text-xs text-[#676F7F] mt-0.5">
                Masukkan alamat IP komputer server Laravel Anda.
                HP dan komputer harus terhubung ke WiFi yang sama.
              </p>
            </div>

            <!-- Current status -->
            <div class="flex items-center gap-2 p-3 rounded-xl bg-[#ECEEF2] border border-[#CFD5DF]">
              <span class="w-2 h-2 rounded-full shrink-0"
                :class="apiStatus.online ? 'bg-[#34D399]' : 'bg-[#EF4444]'"></span>
              <span class="text-xs text-navy-600 font-mono truncate">{{ currentApiUrl }}</span>
            </div>

            <!-- URL Input -->
            <div>
              <label class="block text-xs font-semibold text-[#14213D] mb-1.5">
                Alamat Server <span class="text-[#A6382C]">*</span>
              </label>
              <input
                v-model="serverUrlInput"
                type="url"
                placeholder="http://192.168.1.100:8000/api"
                class="w-full px-3.5 py-2.5 rounded-xl border border-[#CBD1DB] bg-white text-xs text-[#14213D] font-mono placeholder-[#959CAA] focus:outline-none focus:border-[#14213D] focus:ring-1 focus:ring-[#14213D]"
                @keyup.enter="saveServerUrl"
              />
              <p class="text-[10px] text-navy-400 mt-1.5 leading-relaxed">
                Format: <code class="bg-navy-100 px-1 rounded">http://IP_KOMPUTER:8000/api</code><br>
                Jalankan <code class="bg-navy-100 px-1 rounded">php artisan serve --host=0.0.0.0</code> di komputer
              </p>
            </div>

            <p v-if="configError" class="text-xs text-[#A6382C] font-medium">{{ configError }}</p>
            <p v-if="configSuccess" class="text-xs text-[#1B2C4D] font-medium">{{ configSuccess }}</p>

            <!-- Actions -->
            <div class="flex items-center gap-2">
              <button type="button" @click="resetServerUrl"
                class="flex-1 py-3 rounded-xl border border-[#CBD1DB] text-[#676F7F] font-semibold text-xs hover:bg-[#E3E6EC] active-press transition">
                Reset Default
              </button>
              <button type="button" @click="saveServerUrl" :disabled="testingConnection"
                class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-[#14213D] text-[#F5F7FA] font-bold text-xs shadow-sm hover:bg-[#14213D] active-press transition disabled:opacity-50">
                <svg v-if="testingConnection" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                {{ testingConnection ? 'Menguji...' : 'Simpan & Uji' }}
              </button>
            </div>

            <button type="button" @click="showServerConfig = false"
              class="w-full py-2.5 rounded-xl text-[#676F7F] font-semibold text-xs hover:bg-[#E3E6EC] transition">
              Tutup
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api, { isApiUnconfigured, setCustomApiUrl, getApiUrl } from '../services/api';

const apiStatus = ref({
  online: false,
  message: 'Memeriksa koneksi...',
});

// Server config
const showServerConfig = ref(false);
const serverUrlInput = ref('');
const testingConnection = ref(false);
const configError = ref('');
const configSuccess = ref('');
const currentApiUrl = computed(() => getApiUrl());

// Calculate current date in Indonesian format (e.g., Kamis, 8 Oktober 2026)
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

const saveServerUrl = async () => {
  const url = serverUrlInput.value.trim();
  if (!url) {
    configError.value = 'Masukkan alamat server.';
    return;
  }
  // Basic validation
  if (!url.startsWith('http://') && !url.startsWith('https://')) {
    configError.value = 'URL harus diawali dengan http:// atau https://';
    return;
  }

  configError.value = '';
  configSuccess.value = '';
  testingConnection.value = true;

  // Save the URL first
  setCustomApiUrl(url);

  // Test connection
  try {
    const res = await api.get('/ping', { timeout: 8000 });
    if (res.data && res.data.status === 'ok') {
      apiStatus.value = { online: true, message: 'Terhubung' };
      configSuccess.value = 'Berhasil terhubung ke server!';
      configError.value = '';
      setTimeout(() => { showServerConfig.value = false; }, 1500);
    } else {
      throw new Error('Response tidak valid');
    }
  } catch (err) {
    apiStatus.value = { online: false, message: 'Gagal terhubung' };
        configError.value = "Gagal terhubung ke $url — pastikan server Laravel berjalan dan HP terhubung ke WiFi yang sama.";
  } finally {
    testingConnection.value = false;
  }
};

const resetServerUrl = () => {
  setCustomApiUrl(null);
  serverUrlInput.value = '';
  configError.value = '';
  configSuccess.value = 'URL direset ke default.';
  checkApi();
};

onMounted(() => {
  serverUrlInput.value = getApiUrl();
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
.slide-up-enter-active {
    transition: transform 0.28s ease;
}
</style>
