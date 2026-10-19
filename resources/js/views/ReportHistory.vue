<template>
  <div class="px-4 py-4 space-y-4 pb-28">

    <!-- ── HEADER & KPI STATS ── -->
    <div class="bg-white rounded-2xl p-4.5 border border-[#E7E5DE] shadow-[0_2px_8px_rgba(28,25,23,0.03)] space-y-3.5">
      <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
          <div class="w-9 h-9 rounded-xl bg-[#EAF2ED] text-[#1B382B] flex items-center justify-center shrink-0 border border-[#D5E3DA]">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round"
                d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
            </svg>
          </div>
          <div>
            <h2 class="text-sm font-bold text-stone-900 tracking-tight">Arsip Riwayat Laporan</h2>
            <p class="text-xs text-stone-500">Dokumentasi berkala kebersihan BPSDM</p>
          </div>
        </div>
        <router-link to="/create"
          class="inline-flex items-center gap-1 px-3 py-1.5 rounded-xl bg-[#14251D] text-[#D8C79C] text-xs font-semibold shadow-sm hover:bg-[#1E372A] active-press transition">
          + Buat Baru
        </router-link>
      </div>

      <!-- KPI counters -->
      <div class="grid grid-cols-3 gap-2 text-center">
        <div class="bg-[#FAF9F5] rounded-xl p-2.5 border border-[#E7E4DC]">
          <span class="text-[10px] text-stone-500 block font-medium">Total Arsip</span>
          <span class="text-lg font-bold text-stone-900 tabular-nums">{{ stats.total || 0 }}</span>
        </div>
        <div class="bg-[#EEF4F0] rounded-xl p-2.5 border border-[#D3E3D8]">
          <span class="text-[10px] text-[#224A35] block font-medium">Bulan Ini</span>
          <span class="text-lg font-bold text-[#14251D] tabular-nums">{{ stats.this_month || 0 }}</span>
        </div>
        <div class="bg-[#FDF3F1] rounded-xl p-2.5 border border-[#F2D7D3]">
          <span class="text-[10px] text-[#9A3428] block font-medium">Perlu Evaluasi</span>
          <span class="text-lg font-bold text-[#8A2B20] tabular-nums">{{ stats.needs_attention || 0 }}</span>
        </div>
      </div>
    </div>

    <!-- ── SEARCH & FILTER ── -->
    <div class="bg-white rounded-2xl p-3.5 border border-[#E7E5DE] shadow-[0_2px_8px_rgba(28,25,23,0.03)] space-y-2.5">
      <!-- Search -->
      <div class="relative">
        <input type="text" v-model="searchQuery" @input="onSearchInput"
          placeholder="Cari nomor laporan, nama petugas…"
          class="w-full pl-9 pr-8 py-2.5 rounded-xl bg-[#FAF9F5] text-xs text-stone-900 placeholder:text-stone-400 border border-[#DDD9CE] focus:bg-white focus:border-[#1A3428] focus:ring-2 focus:ring-[#1A3428]/10 focus:outline-none transition"/>
        <svg class="w-4 h-4 text-stone-400 absolute left-3 top-3" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-5.197-5.197m0 0A7.5 7.5 0 105.196 5.196a7.5 7.5 0 0010.607 10.607z"/>
        </svg>
        <button v-if="searchQuery" type="button" @click="clearSearch"
          class="absolute right-2.5 top-2.5 w-5 h-5 rounded-full bg-stone-200 text-stone-600 flex items-center justify-center text-[10px] font-bold">
          ✕
        </button>
      </div>

      <!-- Filter chips -->
      <div class="flex items-center gap-1.5 overflow-x-auto pb-0.5 no-scrollbar text-[11px]">
        <button type="button" @click="setFilterStatus('all')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap active-press transition',
            filterStatus === 'all' ? 'bg-[#14251D] text-white' : 'bg-[#F2F0E8] text-stone-700 hover:bg-[#EAE8DE]']">
          Semua ({{ stats.total || 0 }})
        </button>
        <button type="button" @click="setFilterStatus('clean')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap active-press transition',
            filterStatus === 'clean' ? 'bg-[#1B382B] text-white' : 'bg-[#EDF4EE] text-[#1B382B] border border-[#CFE1D5]']">
          ✓ Semua Bersih
        </button>
        <button type="button" @click="setFilterStatus('needs_attention')"
          :class="['px-3 py-1.5 rounded-lg font-semibold whitespace-nowrap active-press transition',
            filterStatus === 'needs_attention' ? 'bg-[#A84337] text-white' : 'bg-[#FDF0EE] text-[#9A3428] border border-[#F2CAC4]']">
          ⚠ Perlu Penanganan
        </button>
        <!-- Date filter -->
        <input type="date" v-model="filterDate" @change="fetchReports(1)"
          class="px-2 py-1.5 rounded-lg bg-[#FAF9F5] text-stone-700 text-[11px] font-semibold border border-[#DDD9CE] focus:outline-none shrink-0"/>
        <!-- Reset -->
        <button v-if="hasActiveFilter" type="button" @click="resetFilters"
          class="px-2.5 py-1.5 rounded-lg font-semibold whitespace-nowrap bg-stone-200 text-stone-700 hover:bg-stone-300 active-press transition shrink-0">
          × Reset
        </button>
      </div>
    </div>

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading && reports.length === 0" class="space-y-3">
      <div v-for="i in 3" :key="i"
        class="bg-white rounded-2xl p-4 border border-[#E7E5DE] animate-pulse space-y-3">
        <div class="flex justify-between">
          <div class="h-4 bg-stone-200 rounded w-1/3"/>
          <div class="h-3 bg-stone-200 rounded w-1/4"/>
        </div>
        <div class="h-3 bg-stone-200 rounded w-2/3"/>
        <div class="h-8 bg-stone-100 rounded-xl"/>
      </div>
    </div>

    <!-- ── PENDING OFFLINE QUEUE CARDS ── -->
    <div v-if="pendingReports.length > 0" class="space-y-2">
      <div class="flex items-center justify-between px-1">
        <div class="flex items-center gap-1.5">
          <span class="w-2 h-2 rounded-full bg-[#C27D26] animate-ping"/>
          <h4 class="text-xs font-bold text-stone-700 uppercase tracking-wide">
            Antrian Offline ({{ pendingReports.length }})
          </h4>
        </div>
        <button
          v-if="isOnline"
          type="button"
          @click="syncPendingReports"
          :disabled="isSyncing"
          class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-[#14251D] text-[#D8C79C] text-[11px] font-semibold active-press transition disabled:opacity-50"
        >
          <svg v-if="isSyncing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          {{ isSyncing ? 'Sinkronisasi…' : 'Sinkronkan Sekarang' }}
        </button>
      </div>

      <div v-for="qRep in pendingReports" :key="'outbox-' + qRep.id"
        class="bg-[#FAF5E8] rounded-2xl p-3.5 border border-[#E8DCBF] space-y-2 shadow-sm">
        <div class="flex items-center justify-between">
          <span class="font-mono text-xs font-bold text-[#6B4F15] bg-[#F2E8CE] px-2.5 py-0.5 rounded-md border border-[#E2D4B2]">
            {{ qRep.report_number }}
          </span>
          <span class="text-[10px] font-semibold text-[#6B4F15] bg-[#EEDBBA] px-2 py-0.5 rounded-full flex items-center gap-1">
            <span class="w-1.5 h-1.5 rounded-full bg-[#C27D26]"/>
            Menunggu Sinkronisasi
          </span>
        </div>
        <div class="flex items-center justify-between text-xs text-stone-700">
          <span class="font-semibold">{{ qRep.officer_name }}</span>
          <span class="text-stone-500 text-[11px] font-mono tabular-nums">{{ qRep.inspection_date }}</span>
        </div>
      </div>
    </div>

    <!-- ── EMPTY STATE ── -->
    <div v-else-if="!loading && reports.length === 0"
      class="bg-white rounded-2xl p-8 text-center border border-[#E7E5DE] shadow-sm space-y-3">
      <div class="w-12 h-12 mx-auto rounded-xl bg-[#FAF9F5] text-stone-400 flex items-center justify-center border border-stone-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round"
            d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
        </svg>
      </div>
      <div>
        <p class="text-sm font-bold text-stone-800">
          {{ hasActiveFilter ? 'Tidak ada laporan yang cocok' : 'Belum ada arsip laporan' }}
        </p>
        <p class="text-xs text-stone-400 mt-0.5">
          {{ hasActiveFilter ? 'Coba ubah kata kunci atau reset filter pencarian.' : 'Mulai buat inspeksi pertama Anda.' }}
        </p>
      </div>
      <button v-if="hasActiveFilter" type="button" @click="resetFilters"
        class="inline-flex items-center px-4 py-2 bg-[#F2F0E8] text-stone-700 rounded-xl text-xs font-semibold hover:bg-[#EAE8DE] active-press transition">
        Reset Filter
      </button>
      <router-link v-else to="/create"
        class="inline-flex items-center px-4 py-2.5 bg-[#14251D] text-[#D8C79C] rounded-xl text-xs font-semibold hover:bg-[#1E372A] active-press transition">
        + Buat Laporan Baru
      </router-link>
    </div>

    <!-- ── REPORT CARDS ── -->
    <div v-else class="space-y-3">
      <div v-for="rep in reports" :key="rep.id"
        class="bg-white rounded-2xl p-4 border border-[#E7E5DE] space-y-3 hover:border-[#14251D]/30 shadow-[0_2px_8px_rgba(28,25,23,0.02)] transition">

        <!-- Top row: number + date -->
        <div class="flex items-center justify-between">
          <span class="font-mono text-xs font-bold text-stone-800 bg-[#F2F0E9] px-2.5 py-0.5 rounded-md border border-stone-200/80">
            {{ rep.report_number }}
          </span>
          <span class="text-[11px] text-stone-500 font-medium tabular-nums">
            {{ rep.formatted_date || rep.inspection_date }}
          </span>
        </div>

        <!-- Middle row: avatar + meta -->
        <div class="flex items-start gap-3">
          <div class="w-11 h-11 rounded-xl bg-stone-100 overflow-hidden border border-stone-200 shrink-0">
            <img v-if="rep.officer_photo_url" :src="rep.officer_photo_url"
              alt="Foto Petugas" class="w-full h-full object-cover"/>
            <div v-else class="w-full h-full flex items-center justify-center text-stone-400">
              <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
              </svg>
            </div>
          </div>

          <div class="flex-1 min-w-0 text-xs space-y-0.5">
            <p class="font-bold text-stone-900 truncate">{{ rep.officer_name }}</p>
            <p class="text-[11px] text-stone-500">
              Tema: <span class="text-[#1B382B] font-semibold">{{ rep.template?.name || 'Default' }}</span>
            </p>

            <!-- Badges -->
            <div class="pt-1 flex items-center gap-1.5 flex-wrap">
              <span v-if="countAttentionLocations(rep) === 0"
                class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#1E4826] bg-[#EAF3EC] px-2 py-0.5 rounded-md border border-[#CCE4D3]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
                </svg>
                5/5 Bersih &amp; Rapi
              </span>
              <span v-else
                class="inline-flex items-center gap-1 text-[10px] font-semibold text-[#9A3428] bg-[#FDF0EE] px-2 py-0.5 rounded-md border border-[#F2CAC4]">
                ⚠ {{ countAttentionLocations(rep) }} Perlu Penanganan
              </span>

              <!-- Dokumen badges -->
              <span v-if="rep.pptx_path"
                class="text-[10px] font-semibold text-[#865E1E] bg-[#FAF5E8] px-1.5 py-0.5 rounded border border-[#E8DCBF]">
                PPTX ✓
              </span>
              <span v-if="rep.pdf_path"
                class="text-[10px] font-semibold text-[#1B382B] bg-[#EDF4EE] px-1.5 py-0.5 rounded border border-[#CFE1D5]">
                PDF ✓
              </span>
            </div>
          </div>
        </div>

        <!-- ── ACTION BUTTONS ── -->
        <div class="pt-2.5 border-t border-stone-100 space-y-2">
          <!-- Row 1: Preview + PDF + PPTX -->
          <div class="flex items-center gap-1.5">
            <!-- Preview -->
            <router-link :to="`/preview/${rep.id}`"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#FAF9F5] hover:bg-[#F2EFE8] text-stone-800 text-xs font-semibold active-press transition border border-stone-200">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
              </svg>
              Pratinjau
            </router-link>

            <!-- PDF: download or generate -->
            <button v-if="rep.pdf_path" type="button" @click="openPdf(rep)"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#FDF0EE] hover:bg-[#F9E2DF] text-[#8C2C22] text-xs font-semibold active-press transition border border-[#F2CAC4]">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z"/>
              </svg>
              PDF
            </button>
            <button v-else type="button" @click="generatePdf(rep)" :disabled="generatingId === rep.id + '_pdf'"
              class="inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#8C2C22] text-white text-xs font-semibold active-press transition disabled:opacity-60">
              <svg v-if="generatingId === rep.id + '_pdf'" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
              </svg>
              <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/>
              </svg>
              {{ generatingId === rep.id + '_pdf' ? 'PDF…' : '+ PDF' }}
            </button>


            <!-- Share / WhatsApp -->
            <button type="button" @click="openShareSheet(rep)"
              class="ml-auto inline-flex items-center gap-1 px-2.5 py-1.5 rounded-lg bg-[#EDF4EE] hover:bg-[#E2EFE4] text-[#1B382B] text-xs font-semibold active-press transition border border-[#CFE1D5]">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round"
                  d="M7.217 10.907a2.25 2.25 0 100 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186l9.566-5.314m-9.566 7.5l9.566 5.314m0 0a2.25 2.25 0 103.935 2.186 2.25 2.25 0 00-3.935-2.186zm0-12.814a2.25 2.25 0 103.933-2.185 2.25 2.25 0 00-3.933 2.185z"/>
              </svg>
              Bagikan
            </button>
          </div>

          <!-- Delete -->
          <div class="flex justify-end">
            <button type="button" @click="confirmDelete(rep)"
              class="text-[11px] text-stone-400 hover:text-rose-600 font-semibold px-2 py-0.5 rounded transition">
              Hapus Laporan
            </button>
          </div>
        </div>
      </div>

      <!-- Load more -->
      <div v-if="currentPage < totalPages" class="flex justify-center pt-2">
        <button type="button" @click="loadMore" :disabled="loadingMore"
          class="inline-flex items-center gap-2 px-5 py-2.5 rounded-xl bg-white border border-[#DDD9CE] text-stone-700 text-xs font-semibold hover:bg-stone-50 active-press transition disabled:opacity-50">
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
        <div v-if="showShareSheet" class="fixed inset-0 z-50 bg-[#0E1A14]/70 backdrop-blur-sm"
          @click="showShareSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showShareSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#FAF9F5] rounded-t-3xl shadow-2xl border-t border-[#E3E0D6]"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-[#D8D4C8]"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-3">
            <div>
              <p class="text-sm font-bold text-[#14251D]">Bagikan Dokumen Laporan</p>
              <p class="text-xs text-[#6F7771] font-mono mt-0.5 tabular-nums">{{ shareTarget?.report_number }}</p>
            </div>

            <!-- WhatsApp -->
            <button type="button" @click="shareWhatsApp"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-[#1A3428] text-[#FAF9F5] font-semibold text-sm hover:bg-[#14251D] active-press transition shadow-sm">
              <span class="w-9 h-9 rounded-xl bg-white/10 flex items-center justify-center shrink-0 text-[#D8C79C]">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                </svg>
              </span>
              Kirim via WhatsApp
            </button>

            <!-- Kirim via Email -->
            <button type="button" @click="openEmailFromShare"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-[#ECEAE3] text-[#14251D] border border-[#DCD7CA] font-semibold text-sm hover:bg-[#E3DFD4] active-press transition">
              <span class="w-9 h-9 rounded-xl bg-white flex items-center justify-center shrink-0 text-[#14251D] border border-[#DCD7CA]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
              </span>
              Kirim via Email (PDF Terlampir)
            </button>

            <!-- Native Share -->
            <button type="button" @click="nativeShare"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-white text-[#14251D] border border-[#DCD7CA] font-semibold text-sm hover:bg-[#F4F3EE] active-press transition">
              <span class="w-9 h-9 rounded-xl bg-[#FAF9F5] border border-[#DCD7CA] flex items-center justify-center shrink-0 text-[#14251D]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                </svg>
              </span>
              Bagikan ke Aplikasi Lain
            </button>

            <!-- Salin Link PDF -->
            <button type="button" @click="copyPdfLink"
              class="w-full flex items-center gap-3 px-4 py-3.5 rounded-2xl bg-transparent text-[#59635D] font-semibold text-sm border border-[#DCD7CA] hover:bg-[#F2EFE8] active-press transition">
              <span class="w-9 h-9 rounded-xl bg-[#ECEAE3] flex items-center justify-center shrink-0 text-[#59635D]">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
              </span>
              {{ linkCopied ? '✓ Link Berhasil Disalin!' : 'Salin Tautan PDF' }}
            </button>

            <button type="button" @click="showShareSheet = false"
              class="w-full py-3 rounded-2xl text-[#6F7771] font-semibold text-sm hover:bg-[#ECEAE3] transition">
              Tutup
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── DELETE CONFIRMATION SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showDeleteSheet" class="fixed inset-0 z-50 bg-[#0E1A14]/70 backdrop-blur-sm"
          @click="showDeleteSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showDeleteSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#FAF9F5] rounded-t-3xl shadow-2xl border-t border-[#E3E0D6]"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-[#D8D4C8]"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-4">
            <div class="w-14 h-14 rounded-2xl bg-[#FDF0EE] border border-[#F5C7C1] mx-auto flex items-center justify-center">
              <svg class="w-6 h-6 text-[#A6382C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
                  d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
              </svg>
            </div>
            <div class="text-center">
              <p class="text-base font-bold text-[#14251D]">Hapus Dokumen Laporan?</p>
              <p class="text-xs text-[#6F7771] mt-1 leading-relaxed">
                Nomor <strong class="font-mono text-[#14251D]">{{ reportToDelete?.report_number }}</strong> beserta seluruh berkas foto, PDF, dan PPTX terkait akan dihapus secara permanen.
              </p>
            </div>
            <div class="space-y-2">
              <button type="button" :disabled="isDeleting" @click="deleteReport"
                class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-[#A6382C] text-white font-bold text-sm hover:bg-[#8F2F24] active-press transition disabled:opacity-60 shadow-sm">
                <svg v-if="isDeleting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                  <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                  <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                </svg>
                {{ isDeleting ? 'Menghapus Data…' : 'Ya, Hapus Permanen' }}
              </button>
              <button type="button" @click="showDeleteSheet = false"
                class="w-full py-3 rounded-2xl text-[#6F7771] font-semibold text-sm hover:bg-[#ECEAE3] transition">
                Batalkan
              </button>
            </div>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── EMAIL BOTTOM SHEET ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showEmailSheet" class="fixed inset-0 z-50 bg-[#0E1A14]/70 backdrop-blur-sm"
          @click="showEmailSheet = false"/>
      </Transition>
      <Transition name="slide-up">
        <div v-if="showEmailSheet"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#FAF9F5] rounded-t-3xl shadow-2xl border-t border-[#E3E0D6]"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(1.5rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center pt-3 pb-1">
            <div class="w-10 h-1 rounded-full bg-[#D8D4C8]"/>
          </div>
          <div class="px-5 pt-2 pb-4 space-y-4">
            <div class="flex items-center justify-between">
              <div>
                <p class="text-sm font-bold text-[#14251D]">Kirim Dokumen via Email</p>
                <p class="text-xs text-[#6F7771] font-mono mt-0.5 tabular-nums">{{ emailTarget?.report_number }}</p>
              </div>
              <span class="text-[10px] font-bold text-[#14251D] bg-[#EAF3EC] px-2.5 py-1 rounded-full border border-[#C8E0D0]">
                PDF 16:9 Otomatis Terlampir
              </span>
            </div>

            <!-- Email Form -->
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-[#14251D] mb-1">
                  Alamat Email Penerima <span class="text-[#A6382C]">*</span>
                </label>
                <input
                  v-model="emailForm.recipient"
                  type="email"
                  placeholder="pimpinan@bpsdm.kemenag.go.id"
                  class="w-full px-3.5 py-2.5 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D] font-medium"
                  @keyup.enter="sendEmailReport"
                />
                <!-- Suggestions -->
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                  <span class="text-[10px] text-[#808983] font-medium">Saran:</span>
                  <button
                    v-for="sug in ['pimpinan@bpsdm.go.id', 'sekretariat@bpsdm.go.id', 'arsip.bpsdm@gmail.com']"
                    :key="sug"
                    type="button"
                    @click="emailForm.recipient = sug"
                    class="text-[10px] text-[#14251D] bg-[#EAF3EC] hover:bg-[#D8EADB] px-2 py-0.5 rounded-md font-medium border border-[#C8E0D0] transition"
                  >
                    {{ sug }}
                  </button>
                </div>
              </div>

              <div>
                <label class="block text-xs font-semibold text-[#14251D] mb-1">
                  Catatan Tambahan (Opsional)
                </label>
                <textarea
                  v-model="emailForm.notes"
                  rows="2"
                  placeholder="Tambahkan catatan resmi untuk penerima email..."
                  class="w-full px-3.5 py-2 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D]"
                ></textarea>
              </div>

              <p v-if="emailError" class="text-xs text-[#A6382C] font-medium">
                {{ emailError }}
              </p>

              <div class="pt-2 flex items-center gap-2">
                <button
                  type="button"
                  @click="showEmailSheet = false"
                  class="flex-1 py-3 rounded-xl border border-[#DCD7CA] text-[#6F7771] font-semibold text-xs hover:bg-[#ECEAE3] active-press transition"
                >
                  Batal
                </button>
                <button
                  type="button"
                  @click="sendEmailReport"
                  :disabled="sendingEmail || !emailForm.recipient"
                  class="flex-1 flex items-center justify-center gap-2 py-3 rounded-xl bg-[#14251D] text-[#FAF9F5] font-bold text-xs shadow-sm hover:bg-[#1C362A] active-press transition disabled:opacity-50"
                >
                  <svg v-if="sendingEmail" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
                  </svg>
                  <svg v-else class="w-4 h-4 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
          class="fixed top-safe left-1/2 -translate-x-1/2 z-[60] px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2 min-w-[200px] max-w-[90vw] border"
          :class="toast.type === 'success' ? 'bg-[#14251D] text-[#FAF9F5] border-[#254234]' : 'bg-[#A6382C] text-white border-[#8F2F24]'"
          style="top: max(1.5rem, env(safe-area-inset-top));">
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
const generatingId = ref(''); // current PDF action
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

// Selalu URL absolut (path relatif "/api/..." tidak bisa dibuka saat ditempel di tempat lain).
const pdfUrl = (rep) => new URL(`${api.defaults.baseURL}/reports/${rep.id}/pdf`, window.location.origin).href;

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

// ── Open / Download ───────────────────────────────────────────────────────────
const openPdf     = (rep) => window.open(`${pdfUrl(rep)}?inline=1`, '_blank');

// ── Share Sheet ───────────────────────────────────────────────────────────────
const openShareSheet = (rep) => {
  shareTarget.value = rep;
  linkCopied.value = false;
  showShareSheet.value = true;
};

const shareWhatsApp = () => {
  const rep = shareTarget.value;
  if (!rep) return;
  const { linkIncluded } = openWhatsApp(rep);
  showShareSheet.value = false;
  if (!linkIncluded) {
    showToast('Tautan PDF tidak disertakan (server bukan alamat publik). Kirim file PDF lewat tombol Bagikan.');
  }
};

const nativeShare = async () => {
  const rep = shareTarget.value;
  if (!rep) return;
  const result = await sharePdfDocument(rep);
  if (result.success) {
    if (result.method === 'native-file' || result.method === 'web-file') {
      showToast('Dokumen PDF berhasil dibagikan!');
    } else if (result.method === 'web-open') {
      showToast('PDF dibuka di tab baru untuk dibagikan manual.');
    }
    showShareSheet.value = false;
  } else if (!result.cancelled) {
    showToast(result.error || 'Gagal membagikan.', 'error');
  }
  // result.cancelled === true: user backed out, keep the sheet open silently.
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
