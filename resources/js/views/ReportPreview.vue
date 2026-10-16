<template>
  <div class="px-4 py-4 space-y-4 pb-28">

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-200 animate-pulse space-y-3">
        <div class="h-5 bg-slate-200 rounded w-1/3"/>
        <div class="h-3 bg-slate-200 rounded w-2/3"/>
        <div class="aspect-video bg-slate-200 rounded-2xl w-full"/>
      </div>
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 animate-pulse space-y-3">
        <div class="h-4 bg-slate-200 rounded w-1/4"/>
        <div class="h-12 bg-slate-200 rounded-xl"/>
        <div class="h-12 bg-slate-200 rounded-xl"/>
      </div>
    </div>

    <!-- ── ERROR STATE ── -->
    <div v-else-if="errorMessage" class="bg-rose-50 border border-rose-200 rounded-2xl p-6 text-center space-y-3">
      <div class="w-14 h-14 mx-auto rounded-full bg-rose-100 text-rose-600 flex items-center justify-center">
        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <p class="text-sm font-bold text-rose-800">{{ errorMessage }}</p>
      <router-link to="/history"
        class="inline-flex items-center px-4 py-2 rounded-xl bg-slate-800 text-white text-xs font-semibold">
        ← Kembali ke Riwayat
      </router-link>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div v-else-if="report" class="space-y-4">

      <!-- ── HEADER METADATA ── -->
      <div class="bg-white rounded-2xl px-4 py-3.5 shadow-sm border border-slate-200">
        <div class="flex items-center justify-between mb-2">
          <span class="font-mono text-xs font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-md border border-blue-100">
            {{ report.report_number }}
          </span>
          <span
            :class="[
              'text-[10px] font-bold px-2 py-0.5 rounded-full',
              report.status === 'completed' ? 'bg-emerald-100 text-emerald-700' : 'bg-amber-100 text-amber-700'
            ]"
          >
            {{ report.status === 'completed' ? 'Selesai' : 'Draft' }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-x-4 gap-y-1 text-xs text-slate-600">
          <div>
            <span class="text-slate-400 text-[10px] uppercase font-bold block">Tanggal</span>
            <span class="font-semibold text-slate-800">{{ report.formatted_date || report.inspection_date }}</span>
          </div>
          <div>
            <span class="text-slate-400 text-[10px] uppercase font-bold block">Petugas</span>
            <span class="font-semibold text-slate-800">{{ report.officer_name }}</span>
          </div>
          <div class="col-span-2">
            <span class="text-slate-400 text-[10px] uppercase font-bold block">Template</span>
            <span class="font-semibold text-indigo-600">{{ report.template?.name || 'Default' }}</span>
          </div>
        </div>
      </div>

      <!-- ── SLIDE VIEWER (16:9 CAROUSEL) ── -->
      <div class="bg-white rounded-2xl p-3 shadow-md border border-slate-200 space-y-3">

        <!-- Top navigator -->
        <div class="flex items-center justify-between px-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-blue-500 animate-pulse"/>
            <h3 class="text-xs font-bold text-slate-700 uppercase tracking-wide">
              Slide {{ activeSlideIndex + 1 }}/{{ totalSlides }}
            </h3>
            <span class="text-[10px] text-slate-400 font-medium">{{ activeSlideTitle }}</span>
          </div>
          <div class="flex items-center gap-1">
            <button type="button" @click="prevSlide" :disabled="activeSlideIndex === 0"
              class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active:scale-90">
              <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button type="button" @click="nextSlide" :disabled="activeSlideIndex === totalSlides - 1"
              class="w-8 h-8 rounded-lg bg-slate-100 hover:bg-slate-200 disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active:scale-90">
              <svg class="w-4 h-4 text-slate-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 16:9 Slide Canvas — with touch swipe support -->
        <div
          ref="slideCanvas"
          class="relative w-full aspect-video rounded-xl overflow-hidden border border-slate-200 shadow-inner select-none touch-pan-y"
          :style="{ backgroundColor: themeColors.bg }"
          @touchstart="onTouchStart"
          @touchend="onTouchEnd"
        >
          <!-- ── SLIDE 0: COVER ── -->
          <Transition name="slide-fade" mode="out-in">
            <div
              v-if="activeSlideIndex === 0"
              key="cover"
              class="w-full h-full flex flex-col justify-between p-3"
            >
              <!-- Header bar -->
              <div class="rounded-lg px-3 py-2 text-white shadow-sm flex items-center justify-between"
                :style="{ backgroundColor: themeColors.primary }">
                <div>
                  <p class="text-[7px] font-bold tracking-widest text-amber-300 uppercase">
                    BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR
                  </p>
                  <h4 class="text-[11px] font-black leading-tight mt-0.5">
                    LAPORAN PENGAWASAN LINGKUNGAN KANTOR
                  </h4>
                </div>
                <span class="text-[8px] font-mono font-bold bg-white/20 px-2 py-0.5 rounded shrink-0">16:9</span>
              </div>

              <!-- Body -->
              <div class="grid grid-cols-12 gap-2 my-1 items-center">
                <!-- Meta card -->
                <div class="col-span-7 bg-white/95 rounded-lg p-2 border border-slate-200 space-y-1.5 shadow-sm">
                  <div>
                    <span class="text-[7px] font-bold uppercase text-slate-400 block">Nomor Laporan</span>
                    <p class="font-mono font-bold text-blue-700 text-[10px]">{{ report.report_number }}</p>
                  </div>
                  <div>
                    <span class="text-[7px] font-bold uppercase text-slate-400 block">Hari & Tanggal</span>
                    <p class="font-semibold text-slate-800 text-[9px] leading-snug">{{ report.formatted_date }}</p>
                  </div>
                  <div>
                    <span class="text-[7px] font-bold uppercase text-slate-400 block">Petugas Pengawas</span>
                    <p class="font-bold text-slate-900 text-[10px]">{{ report.officer_name }}</p>
                  </div>
                </div>

                <!-- Officer photo -->
                <div class="col-span-5 flex flex-col items-center justify-center gap-1">
                  <div class="w-16 h-16 rounded-xl overflow-hidden border-2 border-white shadow-md bg-slate-200">
                    <img v-if="report.officer_photo_url" :src="report.officer_photo_url"
                      alt="Petugas" class="w-full h-full object-cover"/>
                    <div v-else class="w-full h-full flex items-center justify-center">
                      <svg class="w-7 h-7 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                      </svg>
                    </div>
                  </div>
                  <span class="text-[7px] font-bold text-slate-500 uppercase">Petugas Pengawas</span>
                </div>
              </div>

              <!-- Footer -->
              <div class="text-[7px] text-slate-400 border-t border-slate-200/60 pt-1 flex justify-between">
                <span>Sistem Pelaporan Pengawasan Lingkungan BPSDM</span>
                <span>Cover Laporan</span>
              </div>
            </div>

            <!-- ── SLIDES 1-5: LOCATION SLIDES ── -->
            <div
              v-else-if="currentLocation"
              :key="'loc-' + activeSlideIndex"
              class="w-full h-full flex flex-col justify-between p-3"
            >
              <!-- Header -->
              <div class="rounded-lg px-2.5 py-1.5 text-white shadow-sm flex items-center justify-between"
                :style="{ backgroundColor: themeColors.primary }">
                <div class="min-w-0 mr-2">
                  <p class="text-[7px] font-bold tracking-widest text-amber-300 uppercase">
                    TITIK PEMANTAUAN {{ activeSlideIndex }} DARI 5
                  </p>
                  <h4 class="text-[11px] font-bold leading-tight truncate">
                    {{ currentLocation.location?.name || ('Lokasi ' + activeSlideIndex) }}
                  </h4>
                </div>
                <span :class="[
                    'text-[8px] font-bold px-2 py-0.5 rounded-full shrink-0',
                    currentLocation.condition === 'clean'
                      ? 'bg-emerald-500 text-white'
                      : 'bg-rose-500 text-white'
                  ]">
                  {{ currentLocation.condition === 'clean' ? '✓ Bersih' : '⚠ Perlu Penanganan' }}
                </span>
              </div>

              <!-- 2 Photos -->
              <div class="grid grid-cols-2 gap-1.5 my-1 flex-1 min-h-0">
                <div class="flex flex-col gap-0.5 min-h-0">
                  <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 bg-slate-200 min-h-0">
                    <img v-if="getLocationPhoto(currentLocation, 'photo_1')"
                      :src="getLocationPhoto(currentLocation, 'photo_1')"
                      alt="Dokumentasi 1" class="w-full h-full object-cover"/>
                    <div v-else class="w-full h-full flex items-center justify-center text-[8px] text-slate-400 font-bold">
                      [ DOKUMENTASI 1 ]
                    </div>
                  </div>
                  <span class="text-[7px] font-bold text-slate-500 text-center uppercase">Dokumentasi 1</span>
                </div>
                <div class="flex flex-col gap-0.5 min-h-0">
                  <div class="flex-1 rounded-lg overflow-hidden border border-slate-200 bg-slate-200 min-h-0">
                    <img v-if="getLocationPhoto(currentLocation, 'photo_2')"
                      :src="getLocationPhoto(currentLocation, 'photo_2')"
                      alt="Dokumentasi 2" class="w-full h-full object-cover"/>
                    <div v-else class="w-full h-full flex items-center justify-center text-[8px] text-slate-400 font-bold">
                      [ DOKUMENTASI 2 ]
                    </div>
                  </div>
                  <span class="text-[7px] font-bold text-slate-500 text-center uppercase">Dokumentasi 2</span>
                </div>
              </div>

              <!-- Findings -->
              <div class="bg-white/95 rounded-lg p-1.5 border border-slate-200 shadow-sm">
                <template v-if="currentLocation.condition === 'clean'">
                  <p class="text-[8px] leading-snug">
                    <strong class="text-emerald-700">Catatan: </strong>
                    <span class="text-slate-700">{{ currentLocation.notes || 'Area terpantau bersih dan terawat.' }}</span>
                  </p>
                </template>
                <template v-else>
                  <p class="text-[8px] leading-snug">
                    <strong class="text-rose-700">Permasalahan: </strong>
                    <span class="text-slate-800 font-semibold">{{ currentLocation.problem || '-' }}</span>
                  </p>
                  <p class="text-[8px] leading-snug mt-0.5">
                    <strong class="text-blue-700">Solusi: </strong>
                    <span class="text-slate-700">{{ currentLocation.solution || '-' }}</span>
                  </p>
                </template>
              </div>

              <!-- Footer -->
              <div class="text-[7px] text-slate-400 border-t border-slate-200/60 pt-0.5 flex justify-between">
                <span>{{ report.report_number }} • {{ report.officer_name }}</span>
                <span>Slide {{ activeSlideIndex + 1 }}/{{ totalSlides }}</span>
              </div>
            </div>
          </Transition>
        </div>

        <!-- Dot navigation -->
        <div class="flex items-center justify-center gap-1.5">
          <button v-for="(_, idx) in totalSlides" :key="idx" type="button"
            @click="activeSlideIndex = idx"
            class="h-2 rounded-full transition-all duration-300"
            :class="activeSlideIndex === idx ? 'w-6 bg-blue-600' : 'w-2 bg-slate-300 hover:bg-slate-400'"/>
        </div>

        <!-- Swipe hint (only show on mobile) -->
        <p class="text-center text-[10px] text-slate-400 font-medium">
          ← Geser untuk navigasi slide →
        </p>
      </div>

      <!-- ── DOKUMEN STATUS CARD ── -->
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Status Dokumen</h4>

        <div class="space-y-2">
          <!-- PDF status -->
          <div class="flex items-center justify-between p-2.5 rounded-xl border"
            :class="hasPdf ? 'bg-rose-50 border-rose-200' : 'bg-slate-50 border-slate-200'">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                :class="hasPdf ? 'bg-rose-600' : 'bg-slate-300'">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800">Dokumen PDF 16:9</p>
                <p class="text-[10px]" :class="hasPdf ? 'text-rose-600 font-semibold' : 'text-slate-400'">
                  {{ hasPdf ? 'Siap diunduh' : 'Belum digenerate' }}
                </p>
              </div>
            </div>
            <span v-if="hasPdf" class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              Tersedia
            </span>
          </div>

          <!-- PPTX status -->
          <div class="flex items-center justify-between p-2.5 rounded-xl border"
            :class="hasPptx ? 'bg-indigo-50 border-indigo-200' : 'bg-slate-50 border-slate-200'">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                :class="hasPptx ? 'bg-indigo-600' : 'bg-slate-300'">
                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-slate-800">Presentasi PPTX</p>
                <p class="text-[10px]" :class="hasPptx ? 'text-indigo-600 font-semibold' : 'text-slate-400'">
                  {{ hasPptx ? 'Siap diunduh' : 'Belum digenerate' }}
                </p>
              </div>
            </div>
            <span v-if="hasPptx" class="text-[10px] text-emerald-600 font-bold flex items-center gap-1">
              <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              Tersedia
            </span>
          </div>
        </div>
      </div>

      <!-- ── AKSI EKSPOR ── -->
      <div class="bg-white rounded-2xl p-4 shadow-sm border border-slate-200 space-y-3">
        <h4 class="text-xs font-bold uppercase tracking-wider text-slate-500">Ekspor & Bagikan</h4>

        <!-- Generate PDF button (jika belum ada) -->
        <button v-if="!hasPdf" type="button" @click="doGeneratePdf" :disabled="generatingPdf"
          class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl bg-rose-600 text-white font-bold text-sm shadow-sm active:scale-[0.98] transition disabled:opacity-60">
          <svg v-if="generatingPdf" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          {{ generatingPdf ? 'Membuat PDF 16:9…' : 'Generate PDF 16:9' }}
        </button>

        <!-- PDF action buttons (jika sudah ada) -->
        <div v-else class="grid grid-cols-2 gap-2">
          <button type="button" @click="openPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-rose-600 text-white font-bold text-xs shadow-sm active:scale-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Buka PDF
          </button>
          <button type="button" @click="downloadPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-rose-50 border border-rose-300 text-rose-700 font-bold text-xs active:scale-95 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Unduh PDF
          </button>
        </div>

        <!-- Generate PPTX button (jika belum ada) -->
        <button v-if="!hasPptx" type="button" @click="doGeneratePptx" :disabled="generatingPptx"
          class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl bg-indigo-600 text-white font-bold text-sm shadow-sm active:scale-[0.98] transition disabled:opacity-60">
          <svg v-if="generatingPptx" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <svg v-else class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
          </svg>
          {{ generatingPptx ? 'Membuat Presentasi…' : 'Generate PPTX' }}
        </button>

        <!-- Download PPTX (jika sudah ada) -->
        <button v-else type="button" @click="downloadPptx"
          class="w-full flex items-center justify-center gap-2 py-3.5 rounded-xl bg-indigo-50 border border-indigo-300 text-indigo-700 font-bold text-sm active:scale-[0.98] transition">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
          </svg>
          Unduh PPTX
        </button>

        <!-- Share / WhatsApp / Email -->
        <div class="grid grid-cols-3 gap-2 pt-1 border-t border-slate-100">
          <!-- Share via native share sheet -->
          <button type="button" @click="shareReport" :disabled="!hasPdf || sharing"
            class="flex items-center justify-center gap-1 py-2.5 rounded-xl bg-blue-600 text-white font-bold text-xs shadow-sm active:scale-95 transition disabled:opacity-40">
            <svg v-if="sharing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <span class="truncate">{{ sharing ? 'Bagi…' : 'Bagikan' }}</span>
          </button>

          <!-- WhatsApp -->
          <button type="button" @click="shareWhatsApp" :disabled="!hasPdf"
            class="flex items-center justify-center gap-1 py-2.5 rounded-xl bg-emerald-600 text-white font-bold text-xs shadow-sm active:scale-95 transition disabled:opacity-40">
            <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
              <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
            </svg>
            <span class="truncate">WhatsApp</span>
          </button>

          <!-- Email -->
          <button type="button" @click="openEmailModal"
            class="flex items-center justify-center gap-1 py-2.5 rounded-xl bg-violet-600 text-white font-bold text-xs shadow-sm active:scale-95 transition">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="truncate">Email</span>
          </button>
        </div>

        <!-- Error message for generate -->
        <p v-if="generateError" class="text-[11px] text-rose-600 font-medium text-center">
          {{ generateError }}
        </p>
      </div>

      <!-- ── NAVIGATION ── -->
      <div class="flex items-center justify-between text-xs px-1">
        <router-link to="/history"
          class="text-slate-500 hover:text-slate-800 font-semibold flex items-center gap-1">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Riwayat Laporan
        </router-link>
        <router-link to="/create"
          class="text-blue-600 hover:text-blue-800 font-bold flex items-center gap-1">
          + Buat Laporan Baru
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
      </div>

    </div>

    <!-- ── EMAIL BOTTOM SHEET MODAL ── -->
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
                <h3 class="text-sm font-bold text-slate-800">Kirim Laporan via Email</h3>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ report?.report_number }}</p>
              </div>
              <span class="text-[10px] font-bold text-violet-700 bg-violet-50 px-2 py-0.5 rounded-full border border-violet-200">
                PDF 16:9 Otomatis Terlampir
              </span>
            </div>

            <!-- Email form -->
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-bold text-slate-700 mb-1">
                  Alamat Email Penerima <span class="text-rose-500">*</span>
                </label>
                <div class="relative">
                  <input
                    v-model="emailForm.recipient"
                    type="email"
                    placeholder="nama@bpsdm.kemenag.go.id"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500 font-medium"
                    @keyup.enter="sendEmailReport"
                  />
                </div>
                <!-- Quick email suggestions -->
                <div class="flex items-center gap-1.5 mt-1.5 flex-wrap">
                  <span class="text-[10px] text-slate-400 font-medium">Saran:</span>
                  <button
                    v-for="suggest in ['pimpinan@bpsdm.go.id', 'sekretariat@bpsdm.go.id', 'arsip.bpsdm@gmail.com']"
                    :key="suggest"
                    type="button"
                    @click="emailForm.recipient = suggest"
                    class="text-[10px] text-violet-600 bg-violet-50 hover:bg-violet-100 px-2 py-0.5 rounded-md font-medium transition"
                  >
                    {{ suggest }}
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
                  placeholder="Contoh: Mohon arahan dan tindak lanjut terkait pemantauan hari ini..."
                  class="w-full px-3.5 py-2 rounded-xl border border-slate-300 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-violet-500 focus:border-violet-500"
                ></textarea>
              </div>

              <!-- Error message if any -->
              <p v-if="emailError" class="text-xs text-rose-600 font-medium">
                {{ emailError }}
              </p>

              <!-- Action buttons -->
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
      <Transition name="fade">
        <div v-if="toastMessage"
          class="fixed bottom-20 left-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold"
          :class="toastType === 'success' ? 'bg-emerald-600 text-white' : 'bg-rose-600 text-white'"
          style="max-width: 440px; margin-left: auto; margin-right: auto;">
          <svg v-if="toastType === 'success'" class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
          </svg>
          <svg v-else class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
          </svg>
          <span class="flex-1">{{ toastMessage }}</span>
        </div>
      </Transition>
    </Teleport>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import { useRoute } from 'vue-router';
import api from '../services/api.js';
import { sharePdfDocument, openWhatsApp } from '../services/reportShare.js';

const route = useRoute();
const reportId = route.params.id;

// ── State ──
const loading = ref(true);
const errorMessage = ref('');
const report = ref(null);
const activeSlideIndex = ref(0);
const totalSlides = ref(6);

const generatingPdf = ref(false);
const generatingPptx = ref(false);
const sharing = ref(false);
const generateError = ref('');

// Touch swipe state
let touchStartX = 0;

// ── Theme palette ──
const themeColors = computed(() => {
  const key = report.value?.template?.template_key || '';
  const palettes = {
    'corporate-blue':       { primary: '#1E3A8A', bg: '#F8FAFC' },
    'government-formal':    { primary: '#065F46', bg: '#FAFBF9' },
    'modern-clean':         { primary: '#0F172A', bg: '#F1F5F9' },
    'minimal-professional': { primary: '#18181B', bg: '#FFFFFF' },
    'executive-report':     { primary: '#1E1B4B', bg: '#F8FAFC' },
    'photo-focus':          { primary: '#27272A', bg: '#FAFAFA' },
    'elegant-report':       { primary: '#312E81', bg: '#FDF4FF' },
    'bpsdm-classic':        { primary: '#1D4ED8', bg: '#F0F9FF' },
    'modern-government':    { primary: '#0E7490', bg: '#F8FAFC' },
    'clean-documentation':  { primary: '#334155', bg: '#F8FAFC' },
  };
  return palettes[key] || { primary: '#1E3A8A', bg: '#F8FAFC' };
});

// ── Computed ──
const hasPdf  = computed(() => !!report.value?.pdf_path);
const hasPptx = computed(() => !!report.value?.pptx_path);

const activeSlideTitle = computed(() => {
  if (activeSlideIndex.value === 0) return 'Cover Laporan';
  return currentLocation.value?.location?.name || `Lokasi ${activeSlideIndex.value}`;
});

const currentLocation = computed(() => {
  if (activeSlideIndex.value === 0) return null;
  return report.value?.report_locations?.[activeSlideIndex.value - 1] || null;
});

// ── Slide navigation ──
const nextSlide = () => {
  if (activeSlideIndex.value < totalSlides.value - 1) activeSlideIndex.value++;
};
const prevSlide = () => {
  if (activeSlideIndex.value > 0) activeSlideIndex.value--;
};

// Touch swipe support
const onTouchStart = (e) => {
  touchStartX = e.touches[0].clientX;
};
const onTouchEnd = (e) => {
  const diff = touchStartX - e.changedTouches[0].clientX;
  if (Math.abs(diff) > 40) {
    if (diff > 0) nextSlide();
    else prevSlide();
  }
};

// ── Helper: get photo URL ──
const getLocationPhoto = (loc, type) => {
  const photo = loc?.report_photos?.find(p => p.photo_type === type);
  return photo?.photo_url || null;
};

// ── Base URL for API ──
const baseUrl = api.defaults.baseURL;

// ── Fetch Report ──
const fetchReport = async () => {
  loading.value = true;
  errorMessage.value = '';
  try {
    let id = reportId;
    if (!id) {
      const listRes = await api.get('/reports', { params: { per_page: 1 } });
      const first = listRes.data?.data?.data?.[0];
      if (first) {
        id = first.id;
      } else {
        errorMessage.value = 'Belum ada laporan yang dibuat.';
        loading.value = false;
        return;
      }
    }
    const res = await api.get(`/reports/${id}`);
    if (res.data?.data) {
      report.value = res.data.data;
      totalSlides.value = 1 + (report.value.report_locations?.length || 5);
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat pratinjau laporan.';
  } finally {
    loading.value = false;
  }
};

// ── Generate PDF ──
const doGeneratePdf = async () => {
  generatingPdf.value = true;
  generateError.value = '';
  try {
    const res = await api.post(`/reports/${report.value.id}/generate-pdf`);
    if (res.data?.data) report.value = res.data.data;
  } catch (err) {
    generateError.value = err.response?.data?.message || 'Gagal membuat PDF.';
  } finally {
    generatingPdf.value = false;
  }
};

// ── Generate PPTX ──
const doGeneratePptx = async () => {
  generatingPptx.value = true;
  generateError.value = '';
  try {
    const res = await api.post(`/reports/${report.value.id}/generate-pptx`);
    if (res.data?.data) report.value = res.data.data;
  } catch (err) {
    generateError.value = err.response?.data?.message || 'Gagal membuat PPTX.';
  } finally {
    generatingPptx.value = false;
  }
};

// ── Open / Download ──
const openPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf?inline=1`, '_blank');
};
const downloadPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf`, '_blank');
};
const downloadPptx = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pptx`, '_blank');
};

// ── Share (Capacitor native PDF File Share) ──
const shareReport = async () => {
  if (!report.value) return;
  sharing.value = true;
  generateError.value = '';
  try {
    const result = await sharePdfDocument(report.value);
    if (result.method === 'native-file') {
      showToast('Dokumen PDF berhasil dibagikan!', 'success');
    }
  } catch (err) {
    if (!err.message?.includes('cancelled') && !err.message?.includes('Cancel')) {
      generateError.value = 'Gagal membagikan laporan: ' + (err.message || 'Error');
    }
  } finally {
    sharing.value = false;
  }
};

// ── WhatsApp Share (Rich Formatted) ──
const shareWhatsApp = () => {
  if (!report.value) return;
  openWhatsApp(report.value);
};

// ── Email State ──
const showEmailSheet = ref(false);
const sendingEmail = ref(false);
const emailError = ref('');
const emailForm = ref({ recipient: '', notes: '' });

// ── Toast State ──
const toastMessage = ref('');
const toastType = ref('success');
let toastTimer = null;

const showToast = (message, type = 'success') => {
  toastMessage.value = message;
  toastType.value = type;
  if (toastTimer) clearTimeout(toastTimer);
  toastTimer = setTimeout(() => {
    toastMessage.value = '';
  }, 3500);
};

const openEmailModal = () => {
  emailError.value = '';
  if (!emailForm.value.recipient) {
    const saved = localStorage.getItem('last_recipient_email');
    if (saved) emailForm.value.recipient = saved;
  }
  showEmailSheet.value = true;
};

const sendEmailReport = async () => {
  if (!emailForm.value.recipient) {
    emailError.value = 'Silakan masukkan alamat email penerima.';
    return;
  }
  if (!emailForm.value.recipient.includes('@') || !emailForm.value.recipient.includes('.')) {
    emailError.value = 'Format alamat email tidak valid.';
    return;
  }

  sendingEmail.value = true;
  emailError.value = '';

  try {
    const payload = {
      email: emailForm.value.recipient,
      notes: emailForm.value.notes || undefined,
    };
    const res = await api.post(`/reports/${report.value.id}/email`, payload);
    localStorage.setItem('last_recipient_email', emailForm.value.recipient);
    showEmailSheet.value = false;
    showToast(res.data?.message || `Laporan berhasil dikirim ke ${emailForm.value.recipient}!`, 'success');
  } catch (err) {
    emailError.value = err.response?.data?.message || 'Gagal mengirim email laporan.';
  } finally {
    sendingEmail.value = false;
  }
};

onMounted(() => {
  fetchReport();
});
</script>

<style scoped>
/* Slide transition */
.slide-fade-enter-active,
.slide-fade-leave-active {
  transition: all 0.2s ease;
}
.slide-fade-enter-from {
  opacity: 0;
  transform: translateX(18px);
}
.slide-fade-leave-to {
  opacity: 0;
  transform: translateX(-18px);
}

/* Modal Bottom Sheet Transitions */
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.25s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}

.slide-up-enter-active,
.slide-up-leave-active {
  transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), opacity 0.25s ease;
}
.slide-up-enter-from,
.slide-up-leave-to {
  transform: translateY(100%);
  opacity: 0;
}
</style>
