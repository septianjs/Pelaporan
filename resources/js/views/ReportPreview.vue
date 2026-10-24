<template>
  <div class="px-4 py-4 space-y-4 pb-28">

    <!-- ── LOADING SKELETON ── -->
    <div v-if="loading" class="space-y-4">
      <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] animate-pulse space-y-3">
        <div class="h-5 bg-[#E8E5DC] rounded w-1/3"/>
        <div class="h-3 bg-[#E8E5DC] rounded w-2/3"/>
        <div class="aspect-video bg-[#E8E5DC] rounded-xl w-full"/>
      </div>
      <div class="bg-[#FAF9F5] rounded-2xl p-4 border border-[#E3E0D6] animate-pulse space-y-3">
        <div class="h-4 bg-[#E8E5DC] rounded w-1/4"/>
        <div class="h-12 bg-[#E8E5DC] rounded-xl"/>
        <div class="h-12 bg-[#E8E5DC] rounded-xl"/>
      </div>
    </div>

    <!-- ── ERROR STATE ── -->
    <div v-else-if="errorMessage" class="bg-[#FDF0EE] border border-[#F5C7C1] rounded-2xl p-6 text-center space-y-3">
      <div class="w-14 h-14 mx-auto rounded-2xl bg-[#FBF1EF] border border-[#F3D5CF] text-[#A6382C] flex items-center justify-center">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8"
            d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
      </div>
      <p class="text-sm font-bold text-[#14251D]">{{ errorMessage }}</p>
      <router-link to="/history"
        class="inline-flex items-center px-4 py-2 rounded-xl bg-[#14251D] text-[#FAF9F5] text-xs font-semibold active-press transition">
        ← Kembali ke Riwayat
      </router-link>
    </div>

    <!-- ── MAIN CONTENT ── -->
    <div v-else-if="report" class="space-y-4">

      <!-- ── HEADER METADATA ── -->
      <div class="bg-[#FAF9F5] rounded-2xl px-4 py-3.5 border border-[#E3E0D6] shadow-sm">
        <div class="flex items-center justify-between mb-3">
          <span class="font-mono text-xs font-bold text-[#14251D] bg-[#ECE8DD] px-2.5 py-1 rounded-md border border-[#DCD7CA] tabular-nums">
            {{ report.report_number }}
          </span>
          <span
            :class="[
              'text-[10px] font-bold px-2.5 py-0.5 rounded-full border',
              report.status === 'completed'
                ? 'bg-[#EAF3EC] text-[#1A3428] border-[#C8E0D0]'
                : 'bg-[#FAF2DF] text-[#8C6D1F] border-[#EADBBD]'
            ]"
          >
            {{ report.status === 'completed' ? 'Tersimpan Resmi' : 'Draft' }}
          </span>
        </div>

        <div class="grid grid-cols-2 gap-x-4 gap-y-2 text-xs">
          <div>
            <span class="text-[#808983] text-[10px] uppercase font-bold tracking-wider block">Tanggal Pemeriksaan</span>
            <span class="font-semibold text-[#14251D] font-mono tabular-nums">{{ report.formatted_date || report.inspection_date }}</span>
          </div>
          <div>
            <span class="text-[#808983] text-[10px] uppercase font-bold tracking-wider block">Petugas Pengawas</span>
            <span class="font-semibold text-[#14251D]">{{ report.officer_name }}</span>
          </div>
          <div class="col-span-2 pt-1 border-t border-[#ECE8DD] flex items-center justify-between">
            <span class="text-[#808983] text-[10px] uppercase font-bold tracking-wider">Format Dokumen</span>
            <span class="font-semibold text-[#14251D] text-xs bg-[#ECE8DD] px-2 py-0.5 rounded border border-[#DCD7CA]">
              {{ report.template?.name || 'Standar BPSDM' }}
            </span>
          </div>
        </div>
      </div>

      <!-- ── SLIDE VIEWER (16:9 CAROUSEL) ── -->
      <div class="bg-[#FAF9F5] rounded-2xl p-3 border border-[#E3E0D6] shadow-sm space-y-3">

        <!-- Top navigator -->
        <div class="flex items-center justify-between px-1">
          <div class="flex items-center gap-2">
            <span class="w-2 h-2 rounded-full bg-[#C8B88A] animate-pulse"/>
            <h3 class="text-xs font-bold text-[#14251D] uppercase tracking-wider font-mono">
              Slide {{ activeSlideIndex + 1 }}/{{ totalSlides }}
            </h3>
            <span class="text-[10px] text-[#6F7771] font-medium">• {{ activeSlideTitle }}</span>
          </div>
          <div class="flex items-center gap-1.5">
            <button type="button" @click="prevSlide" :disabled="activeSlideIndex === 0"
              class="w-8 h-8 rounded-xl bg-white border border-[#DCD7CA] text-[#14251D] hover:bg-[#F2EFE8] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active-press">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
              </svg>
            </button>
            <button type="button" @click="nextSlide" :disabled="activeSlideIndex === totalSlides - 1"
              class="w-8 h-8 rounded-xl bg-white border border-[#DCD7CA] text-[#14251D] hover:bg-[#F2EFE8] disabled:opacity-30 disabled:cursor-not-allowed flex items-center justify-center transition active-press">
              <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
              </svg>
            </button>
          </div>
        </div>

        <!-- 16:9 Slide Canvas — desain template yang SAMA dengan PDF (touch swipe didukung) -->
        <div
          class="relative w-full rounded-xl overflow-hidden border border-slate-200 shadow-inner select-none touch-pan-y"
          @touchstart="onTouchStart"
          @touchend="onTouchEnd"
        >
          <Transition name="slide-fade" mode="out-in">
            <img
              v-if="pageImages[activeSlideIndex]"
              :key="activeSlideIndex"
              :src="pageImages[activeSlideIndex]"
              :alt="'Halaman ' + (activeSlideIndex + 1)"
              draggable="false"
              class="w-full block bg-white"
              style="aspect-ratio: 16 / 9; object-fit: contain;"
            />
            <div v-else :key="'ph'" class="w-full aspect-video bg-[#ECE8DD] flex items-center justify-center p-4 text-center">
              <div v-if="pagesError" class="space-y-2">
                <p class="text-xs font-semibold text-[#A6382C]">{{ pagesError }}</p>
                <button type="button" @click="renderPdfPages"
                  class="px-3 py-1.5 rounded-lg bg-[#14251D] text-[#FAF9F5] text-[11px] font-bold">Coba lagi</button>
              </div>
              <p v-else class="text-xs font-semibold text-[#6F7771] animate-pulse">
                Menyiapkan pratinjau dari template… (bisa sampai 40 detik)
              </p>
            </div>
          </Transition>
        </div>

        <!-- Dot navigation -->
        <div class="flex items-center justify-center gap-1.5 pt-1">
          <button v-for="(_, idx) in totalSlides" :key="idx" type="button"
            @click="activeSlideIndex = idx"
            class="h-1.5 rounded-full transition-all duration-300"
            :class="activeSlideIndex === idx ? 'w-6 bg-[#14251D]' : 'w-2 bg-[#D8D4C8] hover:bg-[#B3ADA0]'"/>
        </div>

        <!-- Swipe hint (only show on mobile) -->
        <p class="text-center text-[10px] text-[#808983] font-medium">
          ← Geser layar ke kiri atau kanan untuk berpindah slide →
        </p>
      </div>

      <!-- ── DOKUMEN STATUS CARD ── -->
      <div class="bg-[#FAF9F5] rounded-2xl p-4 border border-[#E3E0D6] shadow-sm space-y-3">
        <div class="flex items-center justify-between">
          <h4 class="text-xs font-bold uppercase tracking-wider text-[#14251D]">Status Berkas Dokumen</h4>
          <span class="text-[10px] text-[#808983] font-mono">Format 16:9 HD</span>
        </div>

        <div class="space-y-2">
          <!-- PDF status -->
          <div class="flex items-center justify-between p-3 rounded-xl border"
            :class="hasPdf ? 'bg-white border-[#DCD7CA]' : 'bg-[#F2EFE8] border-[#E0DCD0]'">
            <div class="flex items-center gap-2.5">
              <div class="w-8 h-8 rounded-lg flex items-center justify-center shrink-0"
                :class="hasPdf ? 'bg-[#14251D] text-[#D8C79C]' : 'bg-[#D8D4C8] text-[#808983]'">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                    d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
              </div>
              <div>
                <p class="text-xs font-bold text-[#14251D]">Dokumen PDF Resmi</p>
                <p class="text-[10px]" :class="hasPdf ? 'text-[#1A3428] font-medium' : 'text-[#808983]'">
                  {{ hasPdf ? 'Siap diunduh / dibagikan' : 'Belum digenerate' }}
                </p>
              </div>
            </div>
            <span v-if="hasPdf" class="text-[10px] text-[#1A3428] font-bold bg-[#EAF3EC] px-2 py-0.5 rounded-full border border-[#C8E0D0] flex items-center gap-1">
              <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
              </svg>
              Tersedia
            </span>
          </div>

        <!-- Generate PDF button (jika belum ada) -->
        <button v-if="!hasPdf" type="button" @click="doGeneratePdf" :disabled="generatingPdf"
          class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-[#14251D] text-[#FAF9F5] font-bold text-sm shadow-sm hover:bg-[#1C362A] active-press transition disabled:opacity-60">
          <svg v-if="generatingPdf" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24">
            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
          </svg>
          <svg v-else class="w-5 h-5 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
              d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
          </svg>
          {{ generatingPdf ? 'Memproses Berkas PDF 16:9…' : 'Generate Dokumen PDF 16:9' }}
        </button>

        <!-- PDF action buttons (jika sudah ada) -->
        <div v-else class="grid grid-cols-2 gap-2">
          <button type="button" @click="openPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-[#14251D] text-[#FAF9F5] font-bold text-xs shadow-sm hover:bg-[#1C362A] active-press transition">
            <svg class="w-4 h-4 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
            </svg>
            Buka PDF
          </button>
          <button type="button" @click="downloadPdf"
            class="flex items-center justify-center gap-1.5 py-3 rounded-xl bg-white border border-[#DCD7CA] text-[#14251D] font-bold text-xs hover:bg-[#F4F3EE] active-press transition">
            <svg class="w-4 h-4 text-[#14251D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Unduh PDF
          </button>
        </div>

        <!-- Share / WhatsApp / Email -->
        <div class="grid grid-cols-3 gap-2 pt-2 border-t border-[#ECE8DD]">
          <!-- Share via native share sheet -->
          <button type="button" @click="shareReport" :disabled="!hasPdf || sharing"
            class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-white border border-[#DCD7CA] text-[#14251D] font-bold text-xs hover:bg-[#F4F3EE] active-press transition disabled:opacity-40">
            <svg v-if="sharing" class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
              <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
              <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
            </svg>
            <svg v-else class="w-3.5 h-3.5 text-[#14251D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
            </svg>
            <span class="truncate">{{ sharing ? 'Memproses…' : 'Bagikan' }}</span>
          </button>

          <!-- WhatsApp -->
          <button type="button" @click="shareWhatsApp" :disabled="!hasPdf"
            class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-[#1A3428] text-[#FAF9F5] font-bold text-xs hover:bg-[#14251D] active-press transition disabled:opacity-40 shadow-sm">
            <span class="text-[#D8C79C]">
              <svg class="w-3.5 h-3.5 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
              </svg>
            </span>
            <span class="truncate">WhatsApp</span>
          </button>

          <!-- Email -->
          <button type="button" @click="openEmailModal"
            class="flex items-center justify-center gap-1.5 py-2.5 rounded-xl bg-[#2D4538] text-[#FAF9F5] font-bold text-xs hover:bg-[#23382D] active-press transition">
            <svg class="w-3.5 h-3.5 shrink-0 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
            </svg>
            <span class="truncate">Email</span>
          </button>
        </div>

        <!-- Error message for generate -->
        <p v-if="generateError" class="text-[11px] text-[#A6382C] font-medium text-center">
          {{ generateError }}
        </p>
      </div>
      </div>
      <!-- ── NAVIGATION ── -->
      <div class="flex items-center justify-between text-xs px-1">
        <router-link to="/history"
          class="text-[#6F7771] hover:text-[#14251D] font-semibold flex items-center gap-1 active-press transition">
          <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
          </svg>
          Riwayat Laporan
        </router-link>
        <router-link to="/create"
          class="text-[#14251D] hover:text-[#1A3428] font-bold flex items-center gap-1 bg-[#ECE8DD] px-3 py-1.5 rounded-xl border border-[#DCD7CA] active-press transition">
          + Laporan Baru
          <svg class="w-3.5 h-3.5 text-[#14251D]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
          </svg>
        </router-link>
      </div>

    </div>

    <!-- ── EMAIL BOTTOM SHEET MODAL ── -->
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
                <h3 class="text-sm font-bold text-[#14251D]">Kirim Laporan via Email</h3>
                <p class="text-xs text-[#6F7771] font-mono mt-0.5 tabular-nums">{{ report?.report_number }}</p>
              </div>
              <span class="text-[10px] font-bold text-[#14251D] bg-[#EAF3EC] px-2.5 py-1 rounded-full border border-[#C8E0D0]">
                PDF 16:9 Otomatis Terlampir
              </span>
            </div>

            <!-- Email form -->
            <div class="space-y-3">
              <div>
                <label class="block text-xs font-semibold text-[#14251D] mb-1">
                  Alamat Email Penerima <span class="text-[#A6382C]">*</span>
                </label>
                <div class="relative">
                  <input
                    v-model="emailForm.recipient"
                    type="email"
                    placeholder="nama@bpsdm.kemenag.go.id"
                    class="w-full px-3.5 py-2.5 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D] font-medium"
                    @keyup.enter="sendEmailReport"
                  />
                </div>
                <!-- Quick email suggestions -->
                <div class="flex items-center gap-1.5 mt-2 flex-wrap">
                  <span class="text-[10px] text-[#808983] font-medium">Saran:</span>
                  <button
                    v-for="suggest in ['pimpinan@bpsdm.go.id', 'sekretariat@bpsdm.go.id', 'arsip.bpsdm@gmail.com']"
                    :key="suggest"
                    type="button"
                    @click="emailForm.recipient = suggest"
                    class="text-[10px] text-[#14251D] bg-[#EAF3EC] hover:bg-[#D8EADB] px-2 py-0.5 rounded-md font-medium border border-[#C8E0D0] transition"
                  >
                    {{ suggest }}
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
                  placeholder="Contoh: Mohon arahan dan tindak lanjut terkait pemantauan hari ini..."
                  class="w-full px-3.5 py-2 rounded-xl border border-[#DCD7CA] bg-white text-xs text-[#14251D] placeholder-[#9CA39E] focus:outline-none focus:border-[#14251D] focus:ring-1 focus:ring-[#14251D]"
                ></textarea>
              </div>

              <!-- Error message if any -->
              <p v-if="emailError" class="text-xs text-[#A6382C] font-medium">
                {{ emailError }}
              </p>

              <!-- Action buttons -->
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
      <Transition name="fade">
        <div v-if="toastMessage"
          class="fixed bottom-20 left-4 right-4 z-50 flex items-center gap-2 px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold border"
          :class="toastType === 'success' ? 'bg-[#14251D] text-[#FAF9F5] border-[#254234]' : 'bg-[#A6382C] text-white border-[#8F2F24]'"
          style="max-width: 440px; margin-left: auto; margin-right: auto;">
          <svg v-if="toastType === 'success'" class="w-4 h-4 shrink-0 text-[#D8C79C]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
import { sharePdfDocument, openWhatsApp, formatWhatsAppMessage } from '../services/reportShare.js';
import * as pdfjsLib from 'pdfjs-dist/legacy/build/pdf.js';
import pdfWorkerUrl from 'pdfjs-dist/legacy/build/pdf.worker.min.js?url';


pdfjsLib.GlobalWorkerOptions.workerSrc = pdfWorkerUrl;
import { normalizeReportLocations } from '../services/reportSlides.js';

const route = useRoute();
const reportId = route.params.id;

// ── State ──
const loading = ref(true);
const errorMessage = ref('');
const report = ref(null);
const activeSlideIndex = ref(0);
const totalSlides = ref(6); // 1 sampul + 5 titik pemantauan, sama dengan PDF

const generatingPdf = ref(false);

const sharing = ref(false);
const generateError = ref('');

// Touch swipe state
let touchStartX = 0;

// ── Data slide: sama dengan yang dirender PDF (selalu 5 titik pemantauan) ──
const slideLocations = computed(() => normalizeReportLocations(report.value));
const slideReport = computed(() => ({
  report_number: report.value?.report_number,
  formatted_date: report.value?.formatted_date || report.value?.inspection_date,
  officer_name: report.value?.officer_name,
  officer_photo_url: report.value?.officer_photo_url || null,
}));

// ── Computed ──
const hasPdf  = computed(() => !!report.value?.pdf_path);


const activeSlideTitle = computed(() => {
  if (activeSlideIndex.value === 0) return 'Cover Laporan';
  return slideLocations.value[activeSlideIndex.value - 1]?.name || `Lokasi ${activeSlideIndex.value}`;
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
    }
  } catch (err) {
    errorMessage.value = err.response?.data?.message || 'Gagal memuat pratinjau laporan.';
  } finally {
    loading.value = false;
  }
};

// ── Pratinjau = halaman PDF hasil template PPTX ──
const pageImages = ref([]);
const pagesError = ref('');

const renderPdfPages = async () => {
  if (!report.value) return;
  pagesError.value = '';
  pageImages.value = [];
  try {
    // PDF belum ada -> buat dulu (memakai template yang dipilih)
    if (!report.value.pdf_path) {
      const g = await api.post(`/reports/${report.value.id}/generate-pdf`);
      if (g.data?.data) report.value = g.data.data;
    }
    const res = await api.get(`/reports/${report.value.id}/pdf`, {
      params: { inline: 1, t: Date.now() },
      responseType: 'arraybuffer',
    });
    const pdf = await pdfjsLib.getDocument({ data: new Uint8Array(res.data) }).promise;
    const imgs = [];
    for (let i = 1; i <= pdf.numPages; i++) {
      const page = await pdf.getPage(i);
      const base = page.getViewport({ scale: 1 });
      const vp = page.getViewport({ scale: Math.min(2, 1280 / base.width) });
      const canvas = document.createElement('canvas');
      canvas.width = Math.floor(vp.width);
      canvas.height = Math.floor(vp.height);
      await page.render({ canvasContext: canvas.getContext('2d'), viewport: vp }).promise;
      imgs.push(canvas.toDataURL('image/jpeg', 0.88));
    }
    pageImages.value = imgs;
    totalSlides.value = imgs.length;
    if (activeSlideIndex.value >= imgs.length) activeSlideIndex.value = 0;
  } catch (err) {
    pagesError.value = 'Gagal memuat pratinjau PDF: ' + (err.message || 'kesalahan tidak diketahui');
  }
};

// ── Generate PDF ──
const doGeneratePdf = async () => {
  generatingPdf.value = true;
  generateError.value = '';
  try {
    const res = await api.post(`/reports/${report.value.id}/generate-pdf`);
    if (res.data?.data) report.value = res.data.data;
    await renderPdfPages();
  } catch (err) {
    generateError.value = err.response?.data?.message || 'Gagal membuat PDF.';
  } finally {
    generatingPdf.value = false;
  }
};

// ── Open / Download ──
const openPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf?inline=1`, '_blank');
};
const downloadPdf = () => {
  window.open(`${baseUrl}/reports/${report.value.id}/pdf`, '_blank');
};

// ── Share (Capacitor native PDF File Share) ──
const shareReport = async () => {
  if (!report.value) return;
  sharing.value = true;
  generateError.value = '';
  try {
    const result = await sharePdfDocument(report.value);
    if (result.success) {
      if (result.method === 'native-file' || result.method === 'web-file') {
        showToast('Dokumen PDF berhasil dibagikan!', 'success');
      } else if (result.method === 'web-open') {
        showToast('PDF dibuka di tab baru untuk dibagikan manual.', 'success');
      }
      // native-url / web-url: the OS share sheet already shows its own confirmation.
    } else if (!result.cancelled) {
      generateError.value = 'Gagal membagikan laporan: ' + (result.error || 'Terjadi kesalahan.');
    }
    // result.cancelled === true: user backed out of the share sheet, nothing to show.
  } finally {
    sharing.value = false;
  }
};

// ── WhatsApp Share (Rich Formatted) ──
const shareWhatsApp = async () => {
  if (!report.value) return;
  sharing.value = true;
  try {
    const result = await sharePdfDocument(report.value, { text: formatWhatsAppMessage(report.value) });
    if (!result.success && !result.cancelled) {
      generateError.value = 'Gagal membagikan: ' + (result.error || 'Terjadi kesalahan.');
    }
  } finally {
    sharing.value = false;
  }
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

onMounted(async () => {
  await fetchReport();
  renderPdfPages();
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