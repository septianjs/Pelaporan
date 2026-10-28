<template>
  <div class="report-history-container">

    <!-- ── HEADER & KPI STATS ── -->
    <div class="history-header-card">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="header-icon-box">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
          </div>
          <div>
            <h2 class="header-title">Arsip Riwayat Laporan</h2>
            <p class="header-subtitle">Dokumentasi berkala kebersihan</p>
          </div>
        </div>
      </div>

      <!-- KPI counters -->
      <div class="kpi-grid">
        <div class="kpi-card kpi-card-total">
          <span class="kpi-label">Total Arsip</span>
          <span class="kpi-value text-navy-900">{{ stats.total || 0 }}</span>
        </div>
        <div class="kpi-card kpi-card-month">
          <span class="kpi-label text-[#162C56]">Bulan Ini</span>
          <span class="kpi-value text-[#14213D]">{{ stats.this_month || 0 }}</span>
        </div>
        <div class="kpi-card kpi-card-attention">
          <span class="kpi-label text-[#9A3428]">Perlu Evaluasi</span>
          <span class="kpi-value text-[#8A2B20]">{{ stats.needs_attention || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- ── SEARCH & FILTER ── -->
    <div class="filter-card">
      <!-- Search -->
      <div class="relative">
        <input
          type="text"
          v-model="searchQuery"
          @input="onSearchInput"
          placeholder="Cari nomor laporan, nama petugas…"
          class="search-input"
        />
        <svg class="w-4 h-4 text-navy-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <button
          v-if="searchQuery"
          type="button"
          @click="clearSearch"
          class="btn-clear-search"
        >
          ✕
        </button>
      </div>

      <!-- Filter chips -->
      <div class="filter-chips-row no-scrollbar">
        <button
          type="button"
          @click="setFilterStatus('all')"
          :class="['filter-chip', filterStatus === 'all' ? 'chip-active-all' : 'chip-idle']"
        >
          Semua ({{ stats.total || 0 }})
        </button>
        <button
          type="button"
          @click="setFilterStatus('clean')"
          :class="['filter-chip', filterStatus === 'clean' ? 'chip-active-clean' : 'chip-idle-clean']"
        >
          ✓ Semua Bersih
        </button>
        <button
          type="button"
          @click="setFilterStatus('needs_attention')"
          :class="['filter-chip', filterStatus === 'needs_attention' ? 'chip-active-attention' : 'chip-idle-attention']"
        >
          ⚠ Perlu Penanganan
        </button>
        <!-- Date filter -->
        <input
          type="date"
          v-model="filterDate"
          @change="fetchReports(1)"
          class="date-filter-input"
        />
        <!-- Reset -->
        <button
          v-if="hasActiveFilter"
          type="button"
          @click="resetFilters"
          class="btn-reset-filters"
        >
          × Reset
        </button>
      </div>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading && reports.length === 0" class="space-y-3">
      <div
        v-for="i in 3"
        :key="i"
        class="skeleton-report-card"
      >
        <div class="flex justify-between">
          <div class="h-4 bg-navy-200 rounded w-1/3"/>
          <div class="h-3 bg-navy-200 rounded w-1/4"/>
        </div>
        <div class="h-3 bg-navy-200 rounded w-2/3"/>
        <div class="h-8 bg-navy-100 rounded-xl"/>
      </div>
    </div>

    <!-- ── PENDING OFFLINE QUEUE CARDS ── -->
    <div v-if="pendingReports.length > 0" class="space-y-2">
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-[#C27D26] animate-ping"/>
          <h4 class="text-xs font-bold text-navy-700 uppercase tracking-wide">
            Antrian Offline ({{ pendingReports.length }})
          </h4>
        </div>
        <button
          v-if="isOnline"
          type="button"
          @click="syncPendingReports"
          :disabled="isSyncing"
          class="btn-sync-offline"
        >
          <svg v-if="isSyncing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <span>{{ isSyncing ? 'Sinkronisasi…' : 'Sinkronkan Sekarang' }}</span>
        </button>
      </div>

      <div
        v-for="qRep in pendingReports"
        :key="'outbox-' + qRep.id"
        class="outbox-card"
      >
        <div class="flex items-center justify-between">
          <span class="outbox-number-tag">
            {{ qRep.report_number }}
          </span>
          <span class="outbox-status-pill">
            <span class="w-1.5 h-1.5 rounded-full bg-[#C27D26]"/>
            <span>Menunggu Sinkronisasi</span>
          </span>
        </div>
        <div class="flex items-center justify-between text-xs text-navy-700">
          <span class="font-semibold">{{ qRep.officer_name }}</span>
          <span class="text-navy-500 text-[11px] font-mono tabular-nums">{{ qRep.inspection_date }}</span>
        </div>
      </div>
    </div>

    <!-- ── EMPTY STATE ── -->
    <div
      v-else-if="!loading && reports.length === 0"
      class="empty-card"
    >
      <div class="empty-icon-box">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
      </div>
      <div>
        <p class="empty-title">
          {{ hasActiveFilter ? 'Tidak ada laporan yang cocok' : 'Belum ada arsip laporan' }}
        </p>
        <p class="empty-subtitle">
          {{ hasActiveFilter ? 'Coba ubah kata kunci atau reset filter pencarian.' : 'Mulai buat inspeksi pertama Anda.' }}
        </p>
      </div>
      <button
        v-if="hasActiveFilter"
        type="button"
        @click="resetFilters"
        class="btn-empty-reset"
      >
        Reset Filter
      </button>
      <router-link
        v-else
        to="/create"
        class="btn-empty-create"
      >
        + Buat Laporan Baru
      </router-link>
    </div>

    <!-- ── REPORT CARDS ── -->
    <div v-else class="space-y-3">
      <div
        v-for="rep in reports"
        :key="rep.id"
        class="report-item-card"
      >
        <!-- Top row: number + date -->
        <div class="flex items-center justify-between">
          <span class="report-id-pill">
            {{ rep.report_number }}
          </span>
          <span class="report-date-pill">
            {{ rep.formatted_date || rep.inspection_date }}
          </span>
        </div>

        <!-- Middle row: avatar + meta -->
        <div class="flex items-start gap-3">
          <div class="officer-avatar-box">
            <img
              v-if="rep.officer_photo_url"
              :src="rep.officer_photo_url"
              alt="Foto Petugas"
              class="w-full h-full object-cover"
            />
            <div v-else class="w-full h-full flex items-center justify-center text-navy-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0 text-xs space-y-0.5">
            <p class="font-bold text-navy-900 truncate">{{ rep.officer_name }}</p>
            <p class="text-[11px] text-navy-500">
              Tema: <span class="text-[#1B2C4D] font-semibold">{{ rep.template?.name || 'Default' }}</span>
            </p>

            <!-- Badges -->
            <div class="pt-1 flex items-center gap-1.5 flex-wrap">
              <span
                v-if="countAttentionLocations(rep) === 0"
                class="badge-clean"
              >
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                <span>5/5 Bersih &amp; Rapi</span>
              </span>
              <span
                v-else
                class="badge-attention"
              >
                ⚠ {{ countAttentionLocations(rep) }} Perlu Penanganan
              </span>

              <span
                v-if="rep.pdf_path"
                class="badge-pdf-check"
              >
                PDF ✓
              </span>
            </div>
          </div>
        </div>

        <!-- ── ACTION BUTTONS ── -->
        <div class="pt-2.5 border-t border-navy-100 space-y-2">
          <!-- Row 1: Preview + PDF + Share -->
          <div class="flex items-center gap-1.5">
            <!-- Preview -->
            <router-link
              :to="`/preview/${rep.id}`"
              class="btn-action-preview"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              <span>Pratinjau</span>
            </router-link>

            <!-- PDF: download or generate -->
            <button
              v-if="rep.pdf_path"
              type="button"
              @click="openPdf(rep)"
              class="btn-action-pdf-ready"
            >
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              <span>PDF</span>
            </button>
            <button
              v-else
              type="button"
              @click="generatePdf(rep)"
              :disabled="generatingId === rep.id + '_pdf'"
              class="btn-action-pdf-generate"
            >
              <svg v-if="generatingId === rep.id + '_pdf'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
              </svg>
              <span>{{ generatingId === rep.id + '_pdf' ? 'PDF…' : '+ PDF' }}</span>
            </button>

            <!-- Share (Native Sheet) -->
            <button
              type="button"
              @click="shareReport(rep)"
              :disabled="sharingId === rep.id"
              class="btn-action-share"
            >
              <svg v-if="sharingId === rep.id" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/>
              </svg>
              <span>{{ sharingId === rep.id ? 'Membagikan…' : 'Bagikan' }}</span>
            </button>
          </div>

          <!-- Delete -->
          <div class="flex justify-end">
            <button
              type="button"
              @click="confirmDelete(rep)"
              class="btn-delete-report"
            >
              Hapus Laporan
            </button>
          </div>
        </div>
      </div>

      <!-- Load more -->
      <div v-if="currentPage < totalPages" class="flex justify-center pt-2">
        <button
          type="button"
          @click="loadMore"
          :disabled="loadingMore"
          class="btn-load-more"
        >
          <svg v-if="loadingMore" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <span>{{ loadingMore ? 'Memuat…' : `Muat Lebih Banyak (${totalPages - currentPage} halaman lagi)` }}</span>
        </button>
      </div>
    </div>

    <!-- ── DELETE CONFIRMATION SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div
          v-if="showDeleteSheet"
          class="modal-backdrop"
          @click="showDeleteSheet = false"
        />
      </Transition>
      <Transition name="slide-up">
        <div
          v-if="showDeleteSheet"
          class="delete-modal-sheet"
        >
          <div class="modal-handle-bar">
            <div class="modal-handle-pill"/>
          </div>
          <div class="delete-modal-body">
            <div class="delete-icon-box">
              <svg class="w-6 h-6 text-[#A6382C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>
            <div class="text-center">
              <p class="delete-modal-title">Hapus Dokumen Laporan?</p>
              <p class="delete-modal-desc">
                Nomor <strong class="font-mono text-[#14213D]">{{ reportToDelete?.report_number }}</strong> beserta seluruh berkas foto dan PDF terkait akan dihapus secara permanen.
              </p>
            </div>
            <div class="space-y-2">
              <button
                type="button"
                :disabled="isDeleting"
                @click="deleteReport"
                class="btn-delete-confirm"
              >
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                <span>{{ isDeleting ? 'Menghapus Data…' : 'Ya, Hapus Permanen' }}</span>
              </button>
              <button
                type="button"
                @click="showDeleteSheet = false"
                class="btn-cancel-modal"
              >
                Batalkan
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="toast">
        <div
          v-if="toast.show"
          class="toast-pill"
          :class="toast.type === 'success' ? 'toast-success' : 'toast-error'"
        >
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0 text-[#F5A524]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          <span>{{ toast.message }}</span>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../services/api.js';
import { sharePdfDocument } from '../services/reportShare.js';
import {
  getQueuedReports,
  syncOfflineQueue,
  isOnline,
  isSyncing,
} from '../services/offlineStorage.js';

// State
const reports      = ref([]);
const loading      = ref(true);
const loadingMore  = ref(false);
const searchQuery  = ref('');
const filterStatus = ref('all');
const filterDate   = ref('');
const currentPage  = ref(1);
const totalPages   = ref(1);
const generatingId = ref('');
let   searchTimer  = null;

const stats = reactive({ total: 0, this_month: 0, needs_attention: 0 });

// Delete
const showDeleteSheet = ref(false);
const reportToDelete  = ref(null);
const isDeleting      = ref(false);

// Share
const sharingId = ref('');

// Offline Outbox
const pendingReports = ref([]);

const loadPendingReports = async () => {
  try {
    pendingReports.value = await getQueuedReports();
  } catch {
    pendingReports.value = [];
  }
};

const syncPendingReports = async () => {
  await syncOfflineQueue(api);
  await loadPendingReports();
  await fetchReports(1);
};

// Toast
const toast = reactive({ show: false, message: '', type: 'success' });
let toastTimer = null;

const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer);
  toast.message = message;
  toast.type = type;
  toast.show = true;
  toastTimer = setTimeout(() => { toast.show = false; }, 3000);
};

// Computed
const hasActiveFilter = computed(() =>
  searchQuery.value || filterStatus.value !== 'all' || filterDate.value
);

const countAttentionLocations = (rep) =>
  rep.report_locations?.filter(l => l.condition === 'needs_attention').length ?? 0;

const pdfUrl = (rep) => new URL(`${api.defaults.baseURL}/reports/${rep.id}/pdf`, window.location.origin).href;

// Fetch
const fetchReports = async (page = 1) => {
  if (page === 1) { loading.value = true; reports.value = []; }
  currentPage.value = page;
  try {
    const params = {
      page,
      search: searchQuery.value || undefined,
      condition_status: filterStatus.value !== 'all' ? filterStatus.value : undefined,
      date: filterDate.value || undefined,
    };
    const res = await api.get('/reports', { params });
    if (res.data?.data) {
      reports.value = page === 1
        ? (res.data.data.data || [])
        : [...reports.value, ...(res.data.data.data || [])];
      totalPages.value = res.data.data.last_page || 1;
    }
    if (res.data?.stats) Object.assign(stats, res.data.stats);
  } catch (err) {
    showToast('Gagal memuat riwayat laporan.', 'error');
  } finally {
    loading.value = false;
    loadingMore.value = false;
  }
};

const loadMore = async () => {
  if (loadingMore.value || currentPage.value >= totalPages.value) return;
  loadingMore.value = true;
  await fetchReports(currentPage.value + 1);
};

const onSearchInput = () => {
  clearTimeout(searchTimer);
  searchTimer = setTimeout(() => fetchReports(1), 350);
};
const clearSearch = () => { searchQuery.value = ''; fetchReports(1); };
const setFilterStatus = (s) => { filterStatus.value = s; fetchReports(1); };
const resetFilters = () => {
  searchQuery.value = '';
  filterStatus.value = 'all';
  filterDate.value = '';
  fetchReports(1);
};

// Generate PDF
const generatePdf = async (rep) => {
  generatingId.value = rep.id + '_pdf';
  try {
    const res = await api.post(`/reports/${rep.id}/generate-pdf`);
    const idx = reports.value.findIndex(r => r.id === rep.id);
    if (idx !== -1 && res.data?.data) reports.value[idx] = res.data.data;
    showToast('PDF 16:9 berhasil dibuat!');
  } catch {
    showToast('Gagal membuat PDF.', 'error');
  } finally {
    generatingId.value = '';
  }
};

const openPdf = (rep) => window.open(`${pdfUrl(rep)}?inline=1`, '_blank');

// Share
const shareReport = async (rep) => {
  sharingId.value = rep.id;
  try {
    const result = await sharePdfDocument(rep);
    if (result.success) {
      if (result.method === 'native-file' || result.method === 'web-file') {
        showToast('Dokumen PDF berhasil dibagikan!');
      } else if (result.method === 'web-open') {
        showToast('PDF dibuka di tab baru untuk dibagikan.');
      }
    } else if (!result.cancelled) {
      showToast(result.error || 'Gagal membagikan laporan.', 'error');
    }
  } finally {
    sharingId.value = '';
  }
};

// Delete
const confirmDelete = (rep) => {
  reportToDelete.value = rep;
  showDeleteSheet.value = true;
};

const deleteReport = async () => {
  if (!reportToDelete.value) return;
  isDeleting.value = true;
  try {
    await api.delete(`/reports/${reportToDelete.value.id}`);
    reports.value = reports.value.filter(r => r.id !== reportToDelete.value.id);
    if (stats.total > 0) stats.total--;
    showDeleteSheet.value = false;
    reportToDelete.value = null;
    showToast('Laporan berhasil dihapus.');
  } catch (err) {
    showToast(err.response?.data?.message || 'Gagal menghapus laporan.', 'error');
  } finally {
    isDeleting.value = false;
  }
};

onMounted(async () => {
  await loadPendingReports();
  fetchReports(1);
});
</script>

<style scoped>
.report-history-container {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 1rem;
  padding-bottom: 7rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Header & KPI */
.history-header-card {
  background-color: #FFFFFF;
  border-radius: 1rem;
  padding: 1.125rem;
  border: 1px solid #DEE1E7;
  box-shadow: 0 2px 8px rgba(28, 25, 23, 0.03);
  display: flex;
  flex-direction: column;
  gap: 0.875rem;
}

.header-icon-box {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.75rem;
  background-color: #E7ECF5;
  color: #1B2C4D;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  border: 1px solid #D0D8E8;
}

.header-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #0B1428;
  letter-spacing: -0.025em;
}

.header-subtitle {
  font-size: 0.75rem;
  color: #4A6798;
}

.kpi-grid {
  display: grid;
  grid-template-columns: repeat(3, minmax(0, 1fr));
  gap: 0.5rem;
  text-align: center;
}

.kpi-card {
  border-radius: 0.75rem;
  padding: 0.625rem;
  border-width: 1px;
  border-style: solid;
}

.kpi-card-total {
  background-color: #F5F7FA;
  border-color: #DDE0E6;
}

.kpi-card-month {
  background-color: #ECEFF6;
  border-color: #CDD7E9;
}

.kpi-card-attention {
  background-color: #FDF3F1;
  border-color: #F2D7D3;
}

.kpi-label {
  font-size: 0.625rem;
  color: #4A6798;
  display: block;
  font-weight: 500;
}

.kpi-value {
  font-size: 1.125rem;
  font-weight: 700;
  font-variant-numeric: tabular-nums;
}

/* Filter Card */
.filter-card {
  background-color: #FFFFFF;
  border-radius: 1rem;
  padding: 0.875rem;
  border: 1px solid #DEE1E7;
  box-shadow: 0 2px 8px rgba(28, 25, 23, 0.03);
  display: flex;
  flex-direction: column;
  gap: 0.625rem;
}

.search-input {
  width: 100%;
  padding-left: 2.25rem;
  padding-right: 2rem;
  padding-top: 0.625rem;
  padding-bottom: 0.625rem;
  border-radius: 0.75rem;
  background-color: #F5F7FA;
  font-size: 0.75rem;
  color: #0B1428;
  border: 1px solid #CFD3DC;
  box-sizing: border-box;
  transition: all 0.15s ease;
}

.search-input:focus {
  background-color: #FFFFFF;
  border-color: #1B2C4D;
  outline: none;
  box-shadow: 0 0 0 2px rgba(27, 44, 77, 0.1);
}

.btn-clear-search {
  position: absolute;
  right: 0.625rem;
  top: 0.625rem;
  width: 1.25rem;
  height: 1.25rem;
  border-radius: 9999px;
  background-color: #C7D2E5;
  color: #35507D;
  display: flex;
  align-items: center;
  justify-content: center;
  font-size: 0.625rem;
  font-weight: 700;
  border: none;
  cursor: pointer;
}

.filter-chips-row {
  display: flex;
  align-items: center;
  gap: 0.375rem;
  overflow-x: auto;
  padding-bottom: 0.125rem;
  font-size: 0.6875rem;
}

.filter-chip {
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  font-weight: 600;
  white-space: nowrap;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
}

.chip-active-all {
  background-color: #14213D;
  color: #FFFFFF;
}

.chip-idle {
  background-color: #E9ECF2;
  color: #263B61;
}

.chip-idle:hover {
  background-color: #DFE2E9;
}

.chip-active-clean {
  background-color: #1B2C4D;
  color: #FFFFFF;
}

.chip-idle-clean {
  background-color: #EBEFF6;
  color: #1B2C4D;
  border: 1px solid #C9D3E7;
}

.chip-active-attention {
  background-color: #A84337;
  color: #FFFFFF;
}

.chip-idle-attention {
  background-color: #FDF0EE;
  color: #9A3428;
  border: 1px solid #F2CAC4;
}

.date-filter-input {
  padding: 0.375rem 0.5rem;
  border-radius: 0.5rem;
  background-color: #F5F7FA;
  color: #263B61;
  font-size: 0.6875rem;
  font-weight: 600;
  border: 1px solid #CFD3DC;
  flex-shrink: 0;
}

.btn-reset-filters {
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  font-weight: 600;
  white-space: nowrap;
  background-color: #C7D2E5;
  color: #263B61;
  border: none;
  cursor: pointer;
  flex-shrink: 0;
}

/* Skeletons */
.skeleton-report-card {
  background-color: #FFFFFF;
  border-radius: 1rem;
  padding: 1rem;
  border: 1px solid #DEE1E7;
  animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

/* Outbox / Offline */
.btn-sync-offline {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.25rem 0.625rem;
  border-radius: 0.5rem;
  background-color: #14213D;
  color: #F5A524;
  font-size: 0.6875rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-sync-offline:disabled {
  opacity: 0.5;
}

.outbox-card {
  background-color: #FAF5E8;
  border-radius: 1rem;
  padding: 0.875rem;
  border: 1px solid #E8DCBF;
  display: flex;
  flex-direction: column;
  gap: 0.5rem;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.outbox-number-tag {
  font-family: var(--font-mono, monospace);
  font-size: 0.75rem;
  font-weight: 700;
  color: #6B4F15;
  background-color: #F2E8CE;
  padding: 0.125rem 0.625rem;
  border-radius: 0.375rem;
  border: 1px solid #E2D4B2;
}

.outbox-status-pill {
  font-size: 0.625rem;
  font-weight: 600;
  color: #6B4F15;
  background-color: #EEDBBA;
  padding: 0.125rem 0.5rem;
  border-radius: 9999px;
  display: flex;
  align-items: center;
  gap: 0.25rem;
}

/* Empty State */
.empty-card {
  background-color: #FFFFFF;
  border-radius: 1rem;
  padding: 2rem;
  text-align: center;
  border: 1px solid #DEE1E7;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
}

.empty-icon-box {
  width: 3rem;
  height: 3rem;
  margin: 0 auto;
  border-radius: 0.75rem;
  background-color: #F5F7FA;
  color: #7089B5;
  display: flex;
  align-items: center;
  justify-content: center;
  border: 1px solid #C7D2E5;
}

.empty-title {
  font-size: 0.875rem;
  font-weight: 700;
  color: #1B2C4D;
}

.empty-subtitle {
  font-size: 0.75rem;
  color: #7089B5;
  margin-top: 0.125rem;
}

.btn-empty-reset {
  display: inline-flex;
  align-items: center;
  align-self: center;
  padding: 0.5rem 1rem;
  background-color: #E9ECF2;
  color: #263B61;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
}

.btn-empty-create {
  display: inline-flex;
  align-items: center;
  align-self: center;
  padding: 0.625rem 1rem;
  background-color: #14213D;
  color: #F5A524;
  border-radius: 0.75rem;
  font-size: 0.75rem;
  font-weight: 600;
  text-decoration: none;
}

/* Report Cards */
.report-item-card {
  background-color: #FFFFFF;
  border-radius: 1rem;
  padding: 1rem;
  border: 1px solid #DEE1E7;
  display: flex;
  flex-direction: column;
  gap: 0.75rem;
  box-shadow: 0 2px 8px rgba(28, 25, 23, 0.02);
  transition: border-color 0.15s ease;
}

.report-item-card:hover {
  border-color: rgba(20, 33, 61, 0.3);
}

.report-id-pill {
  font-family: var(--font-mono, monospace);
  font-size: 0.75rem;
  font-weight: 700;
  color: #1B2C4D;
  background-color: #E9ECF2;
  padding: 0.125rem 0.625rem;
  border-radius: 0.375rem;
  border: 1px solid rgba(199, 210, 229, 0.8);
}

.report-date-pill {
  font-size: 0.6875rem;
  color: #4A6798;
  font-weight: 500;
  font-variant-numeric: tabular-nums;
}

.officer-avatar-box {
  width: 2.75rem;
  height: 2.75rem;
  border-radius: 0.75rem;
  background-color: #E4EAF3;
  overflow: hidden;
  border: 1px solid #C7D2E5;
  flex-shrink: 0;
}

.badge-clean {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.625rem;
  font-weight: 600;
  color: #1E4826;
  background-color: #EAF3EC;
  padding: 0.125rem 0.5rem;
  border-radius: 0.375rem;
  border: 1px solid #CCE4D3;
}

.badge-attention {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  font-size: 0.625rem;
  font-weight: 600;
  color: #9A3428;
  background-color: #FDF0EE;
  padding: 0.125rem 0.5rem;
  border-radius: 0.375rem;
  border: 1px solid #F2CAC4;
}

.badge-pdf-check {
  font-size: 0.625rem;
  font-weight: 600;
  color: #1B2C4D;
  background-color: #EBEFF6;
  padding: 0.125rem 0.375rem;
  border-radius: 0.25rem;
  border: 1px solid #C9D3E7;
}

.btn-action-preview {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  background-color: #F5F7FA;
  color: #1B2C4D;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #C7D2E5;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-action-preview:hover {
  background-color: #E9ECF2;
}

.btn-action-pdf-ready {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  background-color: #FDF0EE;
  color: #8C2C22;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #F2CAC4;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-action-pdf-ready:hover {
  background-color: #F9E2DF;
}

.btn-action-pdf-generate {
  display: inline-flex;
  align-items: center;
  gap: 0.25rem;
  padding: 0.375rem 0.625rem;
  border-radius: 0.5rem;
  background-color: #8C2C22;
  color: #FFFFFF;
  font-size: 0.75rem;
  font-weight: 600;
  border: none;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-action-pdf-generate:disabled {
  opacity: 0.6;
}

.btn-action-share {
  margin-left: auto;
  display: inline-flex;
  align-items: center;
  gap: 0.375rem;
  padding: 0.375rem 0.75rem;
  border-radius: 0.5rem;
  background-color: #EBEFF6;
  color: #1B2C4D;
  font-size: 0.75rem;
  font-weight: 600;
  border: 1px solid #C9D3E7;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-action-share:hover {
  background-color: #DEE5F3;
}

.btn-action-share:disabled {
  opacity: 0.5;
}

.btn-delete-report {
  font-size: 0.6875rem;
  color: #7089B5;
  font-weight: 600;
  padding: 0.125rem 0.5rem;
  border-radius: 0.25rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: color 0.15s ease;
}

.btn-delete-report:hover {
  color: #E11D48;
}

.btn-load-more {
  display: inline-flex;
  align-items: center;
  gap: 0.5rem;
  padding: 0.625rem 1.25rem;
  border-radius: 0.75rem;
  background-color: #FFFFFF;
  border: 1px solid #CFD3DC;
  color: #263B61;
  font-size: 0.75rem;
  font-weight: 600;
  cursor: pointer;
  transition: all 0.15s ease;
}

.btn-load-more:disabled {
  opacity: 0.5;
}

/* Delete Modal */
.modal-backdrop {
  position: fixed;
  inset: 0;
  z-index: 50;
  background-color: rgba(11, 20, 40, 0.7);
  backdrop-filter: blur(4px);
}

.delete-modal-sheet {
  position: fixed;
  bottom: 0;
  left: 0;
  right: 0;
  z-index: 50;
  max-width: 480px;
  margin: 0 auto;
  background-color: #F5F7FA;
  border-top-left-radius: 1.5rem;
  border-top-right-radius: 1.5rem;
  box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
  border-top: 1px solid #D7DBE2;
  padding-bottom: max(1.5rem, env(safe-area-inset-bottom));
}

.modal-handle-bar {
  display: flex;
  justify-content: center;
  padding-top: 0.75rem;
  padding-bottom: 0.25rem;
}

.modal-handle-pill {
  width: 2.5rem;
  height: 0.25rem;
  border-radius: 9999px;
  background-color: #C9CED7;
}

.delete-modal-body {
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  padding-top: 0.5rem;
  padding-bottom: 1rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.delete-icon-box {
  width: 3.5rem;
  height: 3.5rem;
  border-radius: 1rem;
  background-color: #FDF0EE;
  border: 1px solid #F5C7C1;
  margin: 0 auto;
  display: flex;
  align-items: center;
  justify-content: center;
}

.delete-modal-title {
  font-size: 1rem;
  font-weight: 700;
  color: #14213D;
}

.delete-modal-desc {
  font-size: 0.75rem;
  color: #676F7F;
  margin-top: 0.25rem;
  line-height: 1.6;
}

.btn-delete-confirm {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border-radius: 1rem;
  background-color: #A6382C;
  color: #FFFFFF;
  font-weight: 700;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  transition: all 0.15s ease;
}

.btn-delete-confirm:hover {
  background-color: #8F2F24;
}

.btn-delete-confirm:disabled {
  opacity: 0.6;
  cursor: not-allowed;
}

.btn-cancel-modal {
  width: 100%;
  padding: 0.75rem;
  border-radius: 1rem;
  color: #676F7F;
  font-weight: 600;
  font-size: 0.875rem;
  background: transparent;
  border: none;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.btn-cancel-modal:hover {
  background-color: #E3E6EC;
}

/* Toast */
.toast-pill {
  position: fixed;
  left: 50%;
  transform: translateX(-50%);
  z-index: 60;
  padding: 0.75rem 1rem;
  border-radius: 1rem;
  box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1);
  font-size: 0.75rem;
  font-weight: 600;
  display: flex;
  align-items: center;
  gap: 0.5rem;
  min-width: 200px;
  max-width: 90vw;
  border-width: 1px;
  border-style: solid;
  top: max(1.5rem, env(safe-area-inset-top));
}

.toast-success {
  background-color: #14213D;
  color: #F5F7FA;
  border-color: #2A4068;
}

.toast-error {
  background-color: #A6382C;
  color: #FFFFFF;
  border-color: #8F2F24;
}

/* Animations */
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}

.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.22s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.toast-enter-active,
.toast-leave-active {
  transition: all 0.25s ease;
}
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-16px);
}

.no-scrollbar::-webkit-scrollbar {
  display: none;
}
.no-scrollbar {
  -ms-overflow-style: none;
  scrollbar-width: none;
}
</style>
