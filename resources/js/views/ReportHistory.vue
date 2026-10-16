<template>
  <div class="px-4 py-4 space-y-4 pb-28">

    <!-- ── HEADER & KPI STATS ── -->
    <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center shrink-0">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
            </svg>
          </div>
          <div>
            <h2 class="text-base font-bold text-slate-800">Riwayat Laporan</h2>
            <p class="text-xs text-slate-500">Arsip pengawasan lingkungan BPSDM</p>
          </div>
        </div>
        <router-link to="/create"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-blue-700 text-white text-xs font-semibold shadow-sm hover:bg-blue-800 active:scale-95 transition">
          + Buat Baru
        </router-link>
      </div>

      <!-- KPI counters -->
      <div class="grid grid-cols-3 gap-2 text-center">
        <div class="bg-slate-50 rounded-xl p-2.5 border border-slate-200">
          <span class="text-[10px] text-slate-500 block font-medium">Total</span>
          <span class="text-lg font-black text-slate-800">{{ stats.total || 0 }}</span>
        </div>
        <div class="bg-blue-50/70 rounded-xl p-2.5 border border-blue-100">
          <span class="text-[10px] text-blue-600 block font-medium">Bulan Ini</span>
          <span class="text-lg font-black text-blue-700">{{ stats.this_month || 0 }}</span>
        </div>
        <div class="bg-rose-50/70 rounded-xl p-2.5 border border-rose-100">
          <span class="text-[10px] text-rose-600 block font-medium">Perlu Evaluasi</span>
          <span class="text-lg font-black text-rose-700">{{ stats.needs_attention || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- ── SEARCH & FILTER ── -->
    <div class="bg-white rounded-2xl p-3.5 shadow-sm border border-slate-200 space-y-2.5">
      <!-- Search -->
      <div class="relative">
        <input type="text" v-model="searchQuery" @input="onSearchInput"
          placeholder="Cari nomor laporan, petugas…"
          class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-slate-50 text-xs text-slate-800 placeholder:text-slate-400 border border-slate-200 focus:border-blue-500 focus:bg-white focus:outline-none transition"/>
        <svg class="w-4 h-4 text-slate-400 absolute left-3 top-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
        </svg>
        <button v-if="searchQuery" type="button" @click="clearSearch"
          class="absolute right-2.5 top-2.5 w-5 h-5 rounded-full bg-slate-200 text-slate-600 flex items-center justify-center text-[10px] font-bold">
          ✕
        </button>
      </div>

      <!-- Filter chips -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar text-[11px]">
        <button type="button" @click="setFilterStatus('all')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition active:scale-95',
            filterStatus === 'all' ? 'bg-slate-800 text-white' : 'bg-slate-100 text-slate-600 hover:bg-slate-200']">
          Semua ({{ stats.total || 0 }})
        </button>
        <button type="button" @click="setFilterStatus('clean')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition active:scale-95',
            filterStatus === 'clean' ? 'bg-emerald-600 text-white' : 'bg-emerald-50 text-emerald-700 border border-emerald-200']">
          ✓ Semua Bersih
        </button>
        <button type="button" @click="setFilterStatus('needs_attention')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap transition active:scale-95',
            filterStatus === 'needs_attention' ? 'bg-rose-600 text-white' : 'bg-rose-50 text-rose-700 border border-rose-200']">
          ⚠ Perlu Penanganan
        </button>
        <!-- Date filter -->
        <input type="date" v-model="filterDate" @change="fetchReports(1)"
          class="px-2 py-1.5 rounded-lg bg-slate-100 text-slate-700 text-[11px] font-semibold border border-slate-200 focus:outline-none shrink-0"/>
        <!-- Reset -->
        <button v-if="hasActiveFilter" type="button" @click="resetFilters"
          class="px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap bg-slate-200 text-slate-600 hover:bg-slate-300 transition active:scale-95 shrink-0">
          × Reset
        </button>
      </div>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading && reports.length === 0" class="space-y-3">
      <div v-for="i in 3" :key="i"
        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 animate-pulse space-y-3">
        <div class="flex justify-between">
          <div class="h-4 bg-slate-200 rounded w-1/3"/>
          <div class="h-3 bg-slate-200 rounded w-1/4"/>
        </div>
        <div class="h-3 bg-slate-200 rounded w-2/3"/>
        <div class="h-8 bg-slate-100 rounded-xl"/>
      </div>
    </div>

    <!-- ── PENDING OFFLINE QUEUE CARDS ── -->
    <div v-if="pendingReports.length > 0" class="space-y-2">
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-amber-500 animate-ping"/>
          <h4 class="text-xs font-bold text-slate-700 uppercase tracking-wide">
            Antrian Offline ({{ pendingReports.length }})
          </h4>
        </div>
        <button
          v-if="isOnline"
          type="button"
          @click="syncPendingReports"
          :disabled="isSyncing"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-indigo-600 text-white text-[11px] font-bold active:scale-95 transition disabled:opacity-50"
        >
          <svg v-if="isSyncing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          {{ isSyncing ? 'Sinkronisasi…' : 'Sinkronkan Sekarang' }}
        </button>
      </div>

      <div v-for="qRep in pendingReports" :key="'outbox-' + qRep.id"
        class="bg-amber-50 rounded-2xl p-3.5 border border-amber-200 space-y-2 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="font-mono text-xs font-bold text-amber-900 bg-amber-100 px-2.5 py-0.5 rounded-md border border-amber-200">
            {{ qRep.report_number }}
          </span>
          <span class="text-[10px] font-bold text-amber-800 bg-amber-200/60 px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"/>
            Menunggu Sinkronisasi
          </span>
        </div>
        <div class="flex items-center justify-between text-xs text-slate-700">
          <span class="font-semibold">{{ qRep.officer_name }}</span>
          <span class="text-slate-500 text-[11px]">{{ qRep.inspection_date }}</span>
        </div>
      </div>
    </div>

    <!-- ── EMPTY STATE ── -->
    <div v-else-if="!loading && reports.length === 0"
      class="bg-white rounded-2xl p-8 text-center border border-slate-200 shadow-sm space-y-3">
      <div class="w-14 h-14 mx-auto rounded-full bg-slate-100 text-slate-400 flex items-center justify-center">
        <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
            d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-slate-700">
          {{ hasActiveFilter ? 'Tidak ada laporan yang cocok' : 'Belum ada laporan' }}
        </p>
        <p class="text-xs text-slate-400 mt-0.5">
          {{ hasActiveFilter ? 'Coba ubah kata kunci atau reset filter.' : 'Mulai buat laporan pertama Anda.' }}
        </p>
      </div>
      <button v-if="hasActiveFilter" type="button" @click="resetFilters"
        class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-xl text-xs font-semibold hover:bg-slate-200 transition">
        Reset Filter
      </button>
      <router-link v-else to="/create"
        class="inline-flex items-center px-4 py-2.5 bg-blue-700 text-white rounded-xl text-xs font-semibold hover:bg-blue-800 transition">
        + Buat Laporan Baru
      </router-link>
    </div>

    <!-- ── REPORT CARDS ── -->
    <div v-else class="space-y-3">
      <div v-for="rep in reports" :key="rep.id"
        class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3 hover:border-blue-300 transition">

        <!-- Top row: number + date -->
        <div class="flex items-center justify-between">
          <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-100">
            {{ rep.report_number }}
          </span>
          <span class="text-[11px] text-slate-500 font-medium">
            {{ rep.formatted_date || rep.inspection_date }}
          </span>
        </div>

        <!-- Middle row: avatar + meta -->
        <div class="flex items-start gap-3">
          <div class="w-11 h-11 rounded-xl bg-slate-100 overflow-hidden border border-slate-200 shrink-0">
            <img v-if="rep.officer_photo_url" :src="rep.officer_photo_url"
              alt="Foto Petugas" class="w-full h-full object-cover"/>
            <div v-else class="w-full h-full flex items-center justify-center">
              <svg class="w-5 h-5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                  d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0 text-xs space-y-0.5">
            <p class="font-bold text-slate-900 truncate">{{ rep.officer_name }}</p>
            <p class="text-[11px] text-slate-500">
              Template: <span class="text-blue-600 font-medium">{{ rep.template?.name || 'Default' }}</span>
            </p>

            <!-- Badges -->
            <div class="pt-1 flex items-center gap-1.5 flex-wrap">
              <span v-if="countAttentionLocations(rep) === 0"
                class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded-md border border-emerald-200">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                </svg>
                5/5 Bersih &amp; Rapi
              </span>
              <span v-else
                class="inline-flex items-center gap-1 text-[10px] font-bold text-rose-700 bg-rose-50 px-2 py-0.5 rounded-md border border-rose-200">
                ⚠ {{ countAttentionLocations(rep) }} Perlu Penanganan
              </span>

              <!-- Dokumen badges -->
              <span v-if="rep.pptx_path"
                class="text-[10px] font-bold text-indigo-700 bg-indigo-50 px-1.5 py-0.5 rounded border border-indigo-200">
                PPTX ✓
              </span>
              <span v-if="rep.pdf_path"
                class="text-[10px] font-bold text-rose-700 bg-rose-50 px-1.5 py-0.5 rounded border border-rose-200">
                PDF ✓
              </span>
            </div>
          </div>
        </div>

        <!-- ── ACTION BUTTONS ── -->
        <div class="pt-2.5 border-t border-slate-100 space-y-2">
          <!-- Row 1: Preview + PDF + PPTX -->
          <div class="flex items-center gap-1.5">
            <!-- Preview -->
            <router-link :to="`/preview/${rep.id}`"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-blue-50 hover:bg-blue-100 text-blue-700 text-xs font-semibold active:scale-95 transition border border-blue-200">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
              </svg>
              Preview
            </router-link>

            <!-- PDF: download or generate -->
            <button v-if="rep.pdf_path" type="button" @click="openPdf(rep)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-50 hover:bg-rose-100 text-rose-700 text-xs font-semibold active:scale-95 transition border border-rose-200">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
              </svg>
              PDF
            </button>
            <button v-else type="button" @click="generatePdf(rep)" :disabled="generatingId === rep.id + '_pdf'"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-rose-600 text-white text-xs font-semibold active:scale-95 transition border border-rose-600 disabled:opacity-60">
              <svg v-if="generatingId === rep.id + '_pdf'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4v16m8-8H4"/>
              </svg>
              {{ generatingId === rep.id + '_pdf' ? 'PDF…' : '+ PDF' }}
            </button>

            <!-- PPTX: download or generate -->
            <button v-if="rep.pptx_path" type="button" @click="downloadPptx(rep)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-50 hover:bg-indigo-100 text-indigo-700 text-xs font-semibold active:scale-95 transition border border-indigo-200">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
              </svg>
              PPTX
            </button>
            <button v-else type="button" @click="generatePptx(rep)" :disabled="generatingId === rep.id + '_pptx'"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-indigo-600 text-white text-xs font-semibold active:scale-95 transition border border-indigo-600 disabled:opacity-60">
              <svg v-if="generatingId === rep.id + '_pptx'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M12 4v16m8-8H4"/>
              </svg>
              {{ generatingId === rep.id + '_pptx' ? 'PPTX…' : '+ PPTX' }}
            </button>

            <!-- Share / WhatsApp -->
            <button type="button" @click="openShareSheet(rep)"
              class="ml-auto inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-emerald-50 hover:bg-emerald-100 text-emerald-700 text-xs font-semibold active:scale-95 transition border border-emerald-200">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
              </svg>
              Bagikan
            </button>
          </div>

          <!-- Delete -->
          <div class="flex justify-end">
            <button type="button" @click="confirmDelete(rep)"
              class="text-[11px] text-rose-500 hover:text-rose-700 font-semibold px-2 py-1 rounded-lg hover:bg-rose-50 transition">
              Hapus Laporan
            </button>
          </div>
        </div>
      </div>

      <!-- Load more -->
      <div v-if="currentPage < totalPages" class="flex justify-center pt-2">
        <button type="button" @click="loadMore" :disabled="loadingMore"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-slate-300 text-slate-700 text-xs font-semibold hover:bg-slate-50 active:scale-95 transition disabled:opacity-50">
          <svg v-if="loadingMore" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          {{ loadingMore ? 'Memuat…' : `Muat Lebih Banyak (${totalPages - currentPage} halaman lagi)` }}
        </button>
      </div>
    </div>

    <!-- ── SHARE BOTTOM SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showShareSheet" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
          @click="showShareSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showShareSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-3">
            <div>
              <p class="text-sm font-bold text-slate-800">Bagikan Laporan</p>
              <p class="text-xs text-slate-500 font-mono mt-0.5">{{ shareTarget?.report_number }}</p>
            </div>

            <!-- WhatsApp -->
            <button type="button" @click="shareWhatsApp"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-emerald-600 text-white font-semibold text-sm active:scale-[0.98] transition">
              <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </span>
              Kirim via WhatsApp
            </button>

            <!-- Kirim via Email -->
            <button type="button" @click="openEmailFromShare"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-violet-600 text-white font-semibold text-sm active:scale-[0.98] transition">
              <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </span>
              Kirim via Email (PDF Terlampir)
            </button>

            <!-- Native Share -->
            <button type="button" @click="nativeShare"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-blue-600 text-white font-semibold text-sm active:scale-[0.98] transition">
              <span class="w-9 h-9 rounded-xl bg-white/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
              </span>
              Bagikan ke Aplikasi Lain
            </button>

            <!-- Salin Link PDF -->
            <button type="button" @click="copyPdfLink"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-slate-100 text-slate-800 font-semibold text-sm border border-slate-200 active:scale-[0.98] transition">
              <span class="w-9 h-9 rounded-xl bg-slate-200 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
              </span>
              {{ linkCopied ? '✓ Link Tersalin!' : 'Salin Link PDF' }}
            </button>

            <button type="button" @click="showShareSheet = false"
              class="w-full py-3 rounded-2xl text-slate-500 font-semibold text-sm hover:bg-slate-100 transition">
              Batal
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── DELETE CONFIRMATION SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showDeleteSheet" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
          @click="showDeleteSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showDeleteSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-4">
            <div class="w-14 h-14 rounded-full bg-rose-100 mx-auto flex items-center justify-center">
              <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>
            <div class="text-center">
              <p class="text-base font-bold text-slate-800">Hapus Laporan?</p>
              <p class="text-sm text-slate-500 mt-1">
                "<strong>{{ reportToDelete?.report_number }}</strong>" beserta seluruh foto, PDF, dan PPTX akan dihapus permanen.
              </p>
            </div>
            <div class="space-y-2">
              <button type="button" :disabled="isDeleting" @click="deleteReport"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-rose-600 text-white font-bold text-sm active:scale-[0.98] transition disabled:opacity-60">
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                {{ isDeleting ? 'Menghapus…' : 'Ya, Hapus Laporan' }}
              </button>
              <button type="button" @click="showDeleteSheet = false"
                class="w-full py-3 rounded-2xl text-slate-500 font-semibold text-sm hover:bg-slate-100 transition">
                Batal
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── EMAIL BOTTOM SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showEmailSheet" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm"
          @click="showEmailSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showEmailSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-white rounded-t-3xl shadow-2xl"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-slate-300"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-bold text-slate-800">Kirim Laporan via Email</p>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ emailTarget?.report_number }}</p>
              </div>
              <span class="text-[10px] font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-200">
                PDF 16:9 Otomatis Terlampir
              </span>
            </div>

            <!-- Email Form -->
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Alamat Email Penerima <span class="text-rose-500">*</span>
                </label>
                <input
                  v-model="emailForm.recipient"
                  type="email"
                  placeholder="pimpinan@bpsdm.kemenag.go.id"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 font-medium"
                  @keyup.enter="sendEmailReport"
                />
                <!-- Suggestions -->
                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                  <span class="text-[10px] text-slate-400 font-medium">Saran:</span>
                  <button
                    v-for="sug in ['pimpinan@bpsdm.go.id', 'sekretariat@bpsdm.go.id', 'arsip.bpsdm@gmail.com']"
                    :key="sug"
                    type="button"
                    @click="emailForm.recipient = sug"
                    class="text-[10px] text-violet-600 bg-violet-50 hover:bg-violet-100 px-2 py-0.5 rounded-md font-medium transition"
                  >
                    {{ sug }}
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Catatan Tambahan (Opsional)
                </label>
                <textarea
                  v-model="emailForm.notes"
                  rows="2"
                  placeholder="Catatan untuk penerima email..."
                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500"
                ></textarea>
              </div>

              <p v-if="emailError" class="text-xs text-rose-600 font-medium">
                {{ emailError }}
              </p>

              <div class="pt-2 flex items-center gap-2">
                <button
                  type="button"
                  @click="showEmailSheet = false"
                  class="flex-1 py-3 rounded-xl border border-slate-300 text-slate-700 font-semibold text-xs hover:bg-slate-50 active:scale-95 transition"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="sendEmailReport"
                  :disabled="sendingEmail || !emailForm.recipient"
                  class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-violet-600 text-white font-bold text-xs shadow-md active:scale-95 transition disabled:opacity-50"
                >
                  <svg v-if="sendingEmail" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                  </svg>
                  <svg v-else class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                      d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                  </svg>
                  {{ sendingEmail ? 'Mengirim…' : 'Kirim Sekarang' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show"
          class="fixed top-safe left-1/2 -translate-x-1/2 z-[60] px-4 py-3 rounded-2xl shadow-xl text-sm font-semibold flex items-center gap-2 min-w-[200px] max-w-[90vw]"
          :class="toast.type === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
          style="top: max(1.5rem, env(safe-area-inset-top));">
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
          </svg>
          {{ toast.message }}
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue';
import api from '../services/api.js';
import { sharePdfDocument, openWhatsApp } from '../services/reportShare.js';
import {
  getQueuedReports,
  syncOfflineQueue,
  isOnline,
  isSyncing,
  queuedCount,
} from '../services/offlineStorage.js';

// ── State ──────────────────────────────────────────────────────────────────────
const reports      = ref([]);
const loading      = ref(true);
const loadingMore  = ref(false);
const searchQuery  = ref('');
const filterStatus = ref('all');
const filterDate   = ref('');
const currentPage  = ref(1);
const totalPages   = ref(1);
const generatingId = ref(''); // e.g. "5_pdf" or "5_pptx"
let   searchTimer  = null;

const stats = reactive({ total: 0, this_month: 0, needs_attention: 0 });

// Delete
const showDeleteSheet = ref(false);
const reportToDelete  = ref(null);
const isDeleting      = ref(false);

// Share
const showShareSheet = ref(false);
const shareTarget    = ref(null);
const linkCopied     = ref(false);

// Email
const showEmailSheet = ref(false);
const emailTarget    = ref(null);
const sendingEmail   = ref(false);
const emailError     = ref('');
const emailForm      = reactive({ recipient: '', notes: '' });

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

// ── Computed ───────────────────────────────────────────────────────────────────
const hasActiveFilter = computed(() =>
  searchQuery.value || filterStatus.value !== 'all' || filterDate.value
);

// ── Helpers ───────────────────────────────────────────────────────────────────
const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer);
  toast.message = message;
  toast.type = type;
  toast.show = true;
  toastTimer = setTimeout(() => { toast.show = false; }, 3000);
};

const countAttentionLocations = (rep) =>
  rep.report_locations?.filter(l => l.condition === 'needs_attention').length ?? 0;

const pdfUrl = (rep) => `${api.defaults.baseURL}/reports/${rep.id}/pdf`;

// ── Fetch ──────────────────────────────────────────────────────────────────────
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

// ── Generate ──────────────────────────────────────────────────────────────────
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

const generatePptx = async (rep) => {
  generatingId.value = rep.id + '_pptx';
  try {
    const res = await api.post(`/reports/${rep.id}/generate-pptx`);
    const idx = reports.value.findIndex(r => r.id === rep.id);
    if (idx !== -1 && res.data?.data) reports.value[idx] = res.data.data;
    showToast('Presentasi PPTX berhasil dibuat!');
  } catch {
    showToast('Gagal membuat PPTX.', 'error');
  } finally {
    generatingId.value = '';
  }
};

// ── Open / Download ───────────────────────────────────────────────────────────
const openPdf     = (rep) => window.open(`${pdfUrl(rep)}?inline=1`, '_blank');
const downloadPptx = (rep) => window.open(`${api.defaults.baseURL}/reports/${rep.id}/pptx`, '_blank');

// ── Share Sheet ───────────────────────────────────────────────────────────────
const openShareSheet = (rep) => {
  shareTarget.value = rep;
  linkCopied.value = false;
  showShareSheet.value = true;
};

const shareWhatsApp = () => {
  const rep = shareTarget.value;
  if (!rep) return;
  openWhatsApp(rep);
  showShareSheet.value = false;
};

const nativeShare = async () => {
  const rep = shareTarget.value;
  if (!rep) return;
  try {
    const result = await sharePdfDocument(rep);
    if (result.method === 'native-file') {
      showToast('Dokumen PDF berhasil dibagikan!');
    }
    showShareSheet.value = false;
  } catch (err) {
    if (!err.message?.includes('cancelled')) showToast('Gagal membagikan.', 'error');
  }
};

const copyPdfLink = async () => {
  const rep = shareTarget.value;
  if (!rep) return;
  try {
    await navigator.clipboard.writeText(pdfUrl(rep));
    linkCopied.value = true;
    showToast('Link PDF berhasil disalin!');
    setTimeout(() => { linkCopied.value = false; }, 3000);
  } catch {
    showToast('Gagal menyalin link.', 'error');
  }
};

// ── Email ─────────────────────────────────────────────────────────────────────
const openEmailFromShare = () => {
  emailTarget.value = shareTarget.value;
  showShareSheet.value = false;
  emailError.value = '';
  if (!emailForm.recipient) {
    const saved = localStorage.getItem('last_recipient_email');
    if (saved) emailForm.recipient = saved;
  }
  showEmailSheet.value = true;
};

const sendEmailReport = async () => {
  if (!emailTarget.value) return;
  if (!emailForm.recipient) {
    emailError.value = 'Silakan masukkan alamat email penerima.';
    return;
  }
  if (!emailForm.recipient.includes('@') || !emailForm.recipient.includes('.')) {
    emailError.value = 'Format alamat email tidak valid.';
    return;
  }

  sendingEmail.value = true;
  emailError.value = '';

  try {
    const payload = {
      email: emailForm.recipient,
      notes: emailForm.notes || undefined,
    };
    const res = await api.post(`/reports/${emailTarget.value.id}/email`, payload);
    localStorage.setItem('last_recipient_email', emailForm.recipient);
    showEmailSheet.value = false;
    showToast(res.data?.message || `Laporan berhasil dikirim ke ${emailForm.recipient}!`);
  } catch (err) {
    emailError.value = err.response?.data?.message || 'Gagal mengirim email laporan.';
  } finally {
    sendingEmail.value = false;
  }
};

// ── Delete ────────────────────────────────────────────────────────────────────
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
.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.28s cubic-bezier(0.32, 0.72, 0, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
}
.fade-enter-active,
.fade-leave-active { transition: opacity 0.22s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* Toast slide-down */
.toast-enter-active,
.toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-16px);
}

.no-scrollbar::-webkit-scrollbar { display: none; }
.no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }

.top-safe {
  top: max(1.5rem, env(safe-area-inset-top));
}
</style>
