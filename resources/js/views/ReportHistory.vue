<template>
  <div class="px-4 py-4 space-y-4">
    <!-- Header Card -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center space-x-3">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
            </svg>
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-800">Riwayat Laporan</h2>
            <p class="text-xs text-slate-500">Arsip inspeksi pengawasan lingkungan</p>
          </div>
        </div>
      </div>

      <!-- Search Input -->
      <div class="relative">
        <input
          type="text"
          v-model="searchQuery"
          @input="onSearchInput"
          placeholder="Cari nomor laporan, petugas..."
          class="w-full pl-9 pr-3.5 py-2 rounded-xl bg-slate-50 text-xs border border-slate-200 focus:border-blue-500 focus:bg-white focus:outline-none transition"
        />
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-2.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
        </svg>
      </div>
    </div>

    <!-- Loading Skeleton -->
    <div v-if="loading" class="space-y-3">
      <div v-for="i in 3" :key="i" class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 animate-pulse space-y-3">
        <div class="h-4 bg-slate-200 rounded w-1/3"></div>
        <div class="h-3 bg-slate-200 rounded w-2/3"></div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="reports.length === 0" class="bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm space-y-3">
      <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-slate-700">Belum ada laporan tersimpan</p>
        <p class="text-xs text-slate-400 mt-0.5">Mulai inspeksi pertama Anda sekarang.</p>
      </div>
      <router-link
        to="/create"
        class="inline-flex items-center px-4 py-2 bg-blue-700 text-white rounded-xl text-xs font-semibold hover:bg-blue-800 transition"
      >
        + Buat Laporan Baru
      </router-link>
    </div>

    <!-- Reports List -->
    <div v-else class="space-y-3">
      <div
        v-for="rep in reports"
        :key="rep.id"
        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3 hover:border-blue-300 transition"
      >
        <div class="flex items-center justify-between">
          <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2 py-0.5 rounded-md border border-blue-100">
            {{ rep.report_number }}
          </span>
          <span class="text-[11px] text-slate-400">
            {{ rep.formatted_date || rep.inspection_date }}
          </span>
        </div>

        <div class="text-xs text-slate-600 space-y-1">
          <p><span class="text-slate-400">Petugas:</span> <strong class="text-slate-800">{{ rep.officer_name }}</strong></p>
          <p><span class="text-slate-400">Template:</span> <span class="text-blue-600 font-medium">{{ rep.template?.name || '-' }}</span></p>
        </div>

        <!-- Action Buttons -->
        <div class="pt-2 border-t border-slate-100 flex items-center justify-between">
          <router-link
            :to="`/preview/${rep.id}`"
            class="inline-flex items-center gap-1 text-xs font-semibold text-blue-600 hover:text-blue-800"
          >
            <span>Lihat Detail</span>
            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
          </router-link>

          <button
            type="button"
            @click="deleteReport(rep.id)"
            class="text-xs text-rose-500 hover:text-rose-700 font-semibold p-1"
          >
            Hapus
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import api from '../services/api';

const reports = ref([]);
const loading = ref(true);
const searchQuery = ref('');
let searchTimer = null;

const fetchReports = async (search = '') => {
  loading.value = true;
  try {
    const res = await api.get('/reports', {
      params: { search },
    });
    if (res.data?.data?.data) {
      reports.value = res.data.data.data;
    }
  } catch (err) {
    console.error('Gagal mengambil riwayat:', err);
  } finally {
    loading.value = false;
  }
};

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => {
    fetchReports(searchQuery.value);
  }, 400);
};

const deleteReport = async (id) => {
  if (!confirm('Apakah Anda yakin ingin menghapus laporan ini?')) return;
  try {
    await api.delete(`/reports/${id}`);
    reports.value = reports.value.filter(r => r.id !== id);
  } catch (err) {
    alert('Gagal menghapus laporan: ' + err.message);
  }
};

onMounted(() => {
  fetchReports();
});
</script>
