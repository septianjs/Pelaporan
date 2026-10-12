<template>
  <div class="px-4 py-5 space-y-5">
    <!-- Header Hero Card -->
    <div class="bg-gradient-to-br from-blue-900 via-blue-800 to-indigo-900 rounded-3xl p-6 text-white shadow-xl relative overflow-hidden border border-blue-700/50">
      <!-- Decorative background circles -->
      <div class="absolute -right-10 -bottom-10 w-40 h-40 bg-blue-500/10 rounded-full blur-2xl pointer-events-none"></div>
      <div class="absolute right-4 top-4 w-20 h-20 bg-amber-400/10 rounded-full blur-xl pointer-events-none"></div>

      <!-- Government Badge -->
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-amber-300 text-xs font-semibold mb-3">
        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
        </svg>
        <span>BPSDM PROVINSI</span>
      </div>

      <!-- App Title -->
      <div class="space-y-0.5">
        <h1 class="text-xs uppercase font-extrabold tracking-widest text-blue-200">
          PELAPORAN
        </h1>
        <h2 class="text-2xl font-black tracking-tight leading-tight text-white">
          PENGAWASAN LINGKUNGAN
        </h2>
        <h3 class="text-sm font-semibold tracking-wide text-amber-300 uppercase">
          KANTOR BPSDM
        </h3>
      </div>

      <!-- Indonesian Date Card -->
      <div class="mt-4 pt-3 border-t border-white/15 flex items-center justify-between text-xs text-blue-100">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-amber-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
          </svg>
          <span class="font-medium">{{ indonesianToday }}</span>
        </div>
        <span class="inline-flex items-center gap-1 text-[11px] font-semibold text-emerald-300">
          <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
          Siap Bertugas
        </span>
      </div>
    </div>

    <!-- MAIN ACTION: [ + BUAT LAPORAN ] -->
    <router-link
      to="/create"
      class="block group relative overflow-hidden rounded-3xl bg-gradient-to-r from-blue-700 to-indigo-600 p-5 text-white shadow-lg shadow-blue-900/20 active:scale-[0.98] transition-all duration-200 border border-blue-500/30"
    >
      <div class="flex items-center justify-between">
        <div class="flex items-center space-x-4">
          <div class="w-14 h-14 rounded-2xl bg-white/20 backdrop-blur-md flex items-center justify-center text-white shadow-inner">
            <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4" />
            </svg>
          </div>
          <div>
            <span class="text-[11px] font-bold uppercase tracking-wider text-amber-300">Langkah Cepat</span>
            <h3 class="text-xl font-black tracking-tight leading-tight text-white">
              BUAT LAPORAN
            </h3>
            <p class="text-xs text-blue-100 mt-0.5">
              Inspeksi kebersihan 5 titik lokasi kantor
            </p>
          </div>
        </div>
        <div class="w-9 h-9 rounded-full bg-white/15 flex items-center justify-center text-white group-hover:translate-x-1 transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </div>
    </router-link>

    <!-- ACTION CARDS: [ RIWAYAT LAPORAN ] & [ TEMPLATE ] -->
    <div class="space-y-3">
      <!-- RIWAYAT LAPORAN -->
      <router-link
        to="/history"
        class="block rounded-2xl bg-white p-4 shadow-sm border border-slate-200/80 hover:border-blue-400 active:bg-slate-50 transition"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center border border-amber-200/50">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
              </svg>
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-800">
                RIWAYAT LAPORAN
              </h4>
              <p class="text-xs text-slate-500">
                Daftar arsip laporan, preview & unduh PDF
              </p>
            </div>
          </div>
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </router-link>

      <!-- TEMPLATE -->
      <router-link
        to="/templates"
        class="block rounded-2xl bg-white p-4 shadow-sm border border-slate-200/80 hover:border-blue-400 active:bg-slate-50 transition"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-700 flex items-center justify-center border border-indigo-200/50">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
              </svg>
            </div>
            <div>
              <h4 class="text-base font-bold text-slate-800">
                TEMPLATE
              </h4>
              <p class="text-xs text-slate-500">
                10 template bawaan siap pakai & upload PPTX 16:9
              </p>
            </div>
          </div>
          <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
          </svg>
        </div>
      </router-link>
    </div>

    <!-- Informasi Alur Pengawasan -->
    <div class="rounded-2xl bg-blue-50/70 border border-blue-100 p-4 space-y-2">
      <div class="flex items-center space-x-2 text-blue-900 font-bold text-xs">
        <svg class="w-4 h-4 text-blue-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <span>STANDAR OPERASIONAL PENGAWASAN</span>
      </div>
      <p class="text-xs text-blue-800 leading-relaxed">
        Setiap pelaporan mencakup 5 titik lokasi pantau: Halaman Depan, Ruang Pelayanan, Ruang Kerja, Toilet, dan Area Parkir dengan 2 dokumentasi foto per lokasi.
      </p>
    </div>

    <!-- API Server Status Monitor -->
    <div class="flex items-center justify-between px-3 py-2 bg-slate-100 rounded-xl text-[11px] text-slate-500">
      <span class="flex items-center gap-1.5">
        <span
          class="w-2 h-2 rounded-full"
          :class="apiStatus.online ? 'bg-emerald-500' : 'bg-rose-500'"
        ></span>
        {{ apiStatus.online ? 'Backend API Terhubung' : 'Memeriksa Backend API...' }}
      </span>
      <span class="font-mono text-[10px] text-slate-400">Laravel 12 + Capacitor</span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue';
import api from '../services/api';

const apiStatus = ref({
  online: false,
  message: '',
});

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
    const res = await api.get('/ping');
    if (res.data && res.data.status === 'ok') {
      apiStatus.value = {
        online: true,
        message: res.data.message || 'Connected',
      };
    }
  } catch (err) {
    console.warn('API ping warning:', err.message);
    apiStatus.value = {
      online: false,
      message: 'Koneksi ke backend belum siap',
    };
  }
};

onMounted(() => {
  checkApi();
});
</script>

