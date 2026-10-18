<template>
  <div class="px-4 py-5 space-y-4">
    <!-- Header Hero Card — Architectural Institutional -->
    <div class="bg-[#14251D] rounded-3xl p-6 text-white shadow-[0_12px_32px_rgba(10,18,14,0.15)] relative overflow-hidden border border-[#233F31]">
      <!-- Subtle ambient warm spotlight -->
      <div class="absolute -right-8 -top-8 w-44 h-44 bg-[#D4C497]/10 rounded-full blur-3xl pointer-events-none"></div>
      <div class="absolute -left-10 -bottom-10 w-44 h-44 bg-[#2C5841]/30 rounded-full blur-2xl pointer-events-none"></div>

      <!-- Institutional Eyebrow Badge -->
      <div class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-white/5 backdrop-blur-md border border-white/10 text-[#D8C79C] text-[10px] font-semibold tracking-[0.14em] uppercase mb-3">
        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M12 21v-8.25M15.75 21v-8.25M8.25 21v-8.25M3 9l9-6 9 6m-1.5 12V10.5M4.5 21V10.5" />
        </svg>
        <span>BPSDM KEMENTERIAN AGAMA</span>
      </div>

      <!-- Title Block -->
      <div class="space-y-1">
        <p class="text-[11px] font-medium tracking-wider text-stone-300">
          Sistem Pengawasan Sarana & Prasarana
        </p>
        <h1 class="text-2xl font-bold tracking-tight text-white leading-tight">
          Pengawasan Kebersihan & Lingkungan
        </h1>
      </div>

      <!-- Real-time Indonesian Date Bar -->
      <div class="mt-5 pt-3.5 border-t border-white/10 flex items-center justify-between text-xs text-stone-300">
        <div class="flex items-center space-x-2">
          <svg class="w-4 h-4 text-[#D8C79C]" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
          </svg>
          <span class="font-medium tabular-nums">{{ indonesianToday }}</span>
        </div>
        <span class="inline-flex items-center gap-1.5 text-[11px] font-medium text-[#A3E0BA]">
          <span class="w-2 h-2 rounded-full bg-[#34D399] animate-pulse"></span>
          Siap Bertugas
        </span>
      </div>
    </div>

    <!-- MAIN ACTION: Double-Bezel CTA (Outer shell + Inner interactive card) -->
    <div class="p-1 rounded-3xl bg-stone-200/60 border border-stone-300/60">
      <router-link
        to="/create"
        class="block group relative overflow-hidden rounded-[20px] bg-[#1A3428] p-5 text-white shadow-[0_8px_20px_rgba(20,36,28,0.18)] active-press transition-all duration-200 border border-[#2D5441]"
      >
        <div class="flex items-center justify-between">
          <div class="flex items-center space-x-3.5">
            <div class="w-12 h-12 rounded-2xl bg-white/10 backdrop-blur-md flex items-center justify-center text-[#D8C79C] border border-white/10 shadow-inner">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
              </svg>
            </div>
            <div>
              <span class="text-[9.5px] font-bold uppercase tracking-[0.16em] text-[#CDB987]">Inspeksi Hari Ini</span>
              <h2 class="text-lg font-bold tracking-tight text-white leading-snug">
                Buat Laporan Baru
              </h2>
              <p class="text-xs text-stone-300 mt-0.5">
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
        class="block rounded-2xl bg-white p-4 border border-[#E7E4DC] hover:border-[#14251D]/30 shadow-[0_2px_8px_rgba(28,25,23,0.03)] active-press transition"
      >
        <div class="w-10 h-10 rounded-xl bg-[#EAF2ED] text-[#1B382B] flex items-center justify-center border border-[#D5E3DA] mb-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
          </svg>
        </div>
        <h3 class="text-sm font-bold text-[#1C1B18] tracking-tight">
          Arsip Riwayat
        </h3>
        <p class="text-[11px] text-stone-500 mt-1 leading-snug">
          Daftar laporan, ekspor PDF & presentasi PPTX
        </p>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[10px] font-semibold text-[#1A3428]">
          <span>Buka Arsip</span>
          <span>→</span>
        </div>
      </router-link>

      <!-- BENTO 2: Desain Template -->
      <router-link
        to="/templates"
        class="block rounded-2xl bg-white p-4 border border-[#E7E4DC] hover:border-[#14251D]/30 shadow-[0_2px_8px_rgba(28,25,23,0.03)] active-press transition"
      >
        <div class="w-10 h-10 rounded-xl bg-[#FAF5E8] text-[#84631D] flex items-center justify-center border border-[#ECE0C2] mb-3">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
          </svg>
        </div>
        <h3 class="text-sm font-bold text-[#1C1B18] tracking-tight">
          Format Template
        </h3>
        <p class="text-[11px] text-stone-500 mt-1 leading-snug">
          10 template bawaan 16:9 & unggah tema kustom
        </p>
        <div class="mt-3 pt-2.5 border-t border-stone-100 flex items-center justify-between text-[10px] font-semibold text-[#84631D]">
          <span>Katalog</span>
          <span>→</span>
        </div>
      </router-link>
    </div>

    <!-- SOP Minimalist Document Card -->
    <div class="rounded-2xl bg-white border border-[#E7E4DC] p-4 space-y-2.5 shadow-[0_1px_4px_rgba(28,25,23,0.02)]">
      <div class="flex items-center space-x-2 text-[#14251D] font-bold text-xs">
        <div class="w-1.5 h-1.5 rounded-full bg-[#1A3428]"></div>
        <span class="tracking-wide uppercase text-[10.5px]">Titik Pemantauan Terstandar</span>
      </div>
      <p class="text-xs text-stone-600 leading-relaxed">
        Setiap pelaporan mencakup 5 area pengawasan wajib dengan dokumentasi kondisi visual sebelum dan sesudah tindak lanjut:
      </p>
      <!-- Pill Tags of 5 Locations -->
      <div class="flex flex-wrap gap-1.5 pt-1">
        <span class="px-2.5 py-1 rounded-md bg-[#F2F1EC] text-stone-700 text-[10.5px] font-medium border border-stone-200/80">1. Halaman Depan</span>
        <span class="px-2.5 py-1 rounded-md bg-[#F2F1EC] text-stone-700 text-[10.5px] font-medium border border-stone-200/80">2. Ruang Pelayanan</span>
        <span class="px-2.5 py-1 rounded-md bg-[#F2F1EC] text-stone-700 text-[10.5px] font-medium border border-stone-200/80">3. Ruang Kerja</span>
        <span class="px-2.5 py-1 rounded-md bg-[#F2F1EC] text-stone-700 text-[10.5px] font-medium border border-stone-200/80">4. Toilet</span>
        <span class="px-2.5 py-1 rounded-md bg-[#F2F1EC] text-stone-700 text-[10.5px] font-medium border border-stone-200/80">5. Area Parkir</span>
      </div>
    </div>

    <!-- API Server Status Monitor -->
    <div class="flex items-center justify-between px-3.5 py-2 bg-[#F2F1EC] rounded-xl text-[11px] text-stone-500 border border-stone-200/60">
      <span class="flex items-center gap-2">
        <span
          class="w-2 h-2 rounded-full"
          :class="apiStatus.online ? 'bg-[#34D399]' : 'bg-[#EF4444]'"
        ></span>
        <span>{{ apiStatus.online ? 'Layanan API Aktif' : 'Memeriksa Layanan...' }}</span>
      </span>
      <span class="font-mono text-[10px] text-stone-400">v1.2 • BPSDM</span>
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

