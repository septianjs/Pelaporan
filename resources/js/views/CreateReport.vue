<template>
  <div class="px-4 pt-4 pb-0 space-y-4">
    <!-- WIZARD STEP HEADER & PROGRESS (Architectural Minimalist) -->
    <div class="bg-white rounded-2xl p-4.5 border border-[#DEE1E7] shadow-[0_2px_8px_rgba(28,25,23,0.03)]">
      <div class="flex items-center justify-between mb-3">
        <div class="flex items-center gap-2">
          <span class="text-[10px] font-bold uppercase tracking-[0.16em] text-[#14213D] bg-[#E5EAF2] border border-[#C4CEE1] px-2.5 py-0.5 rounded-full">
            Langkah {{ currentStep }} / {{ totalSteps }}
          </span>
          <span class="text-[11px] text-navy-400 font-mono tabular-nums">
            {{ Math.round((currentStep / totalSteps) * 100) }}% Selesai
          </span>
        </div>
        <button
          type="button"
          @click="saveDraftManually"
          class="inline-flex items-center gap-1.5 text-[11px] font-semibold text-navy-600 hover:text-navy-900 bg-[#EEF1F5] hover:bg-[#E0E3EA] border border-navy-200/80 px-2.5 py-1 rounded-lg active-press transition"
        >
          <svg class="w-3.5 h-3.5 text-navy-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M17.593 3.322c1.1.128 1.907 1.077 1.907 2.185V21L12 17.25 4.5 21V5.507c0-1.108.806-2.057 1.907-2.185a48.507 48.507 0 0111.186 0z" />
          </svg>
          Simpan Draft
        </button>
      </div>

      <!-- Segmented-feel Progress Bar Indicator -->
      <div class="w-full bg-[#E1E4EA] h-1.5 rounded-full overflow-hidden mb-3.5">
        <div
          class="bg-[#14213D] h-full transition-all duration-300 rounded-full"
          :style="{ width: `${(currentStep / totalSteps) * 100}%` }"
        ></div>
      </div>

      <!-- Step Title & Description -->
      <div class="flex items-center justify-between">
        <div>
          <h2 class="text-base font-bold text-navy-900 leading-tight">
            {{ stepTitles[currentStep - 1] }}
          </h2>
          <p class="text-xs text-navy-500 mt-0.5">
            {{ stepDescriptions[currentStep - 1] }}
          </p>
        </div>
      </div>
    </div>

    <!-- DRAFT RESTORE ALERT -->
    <div
      v-if="hasSavedDraft && !draftRestored"
      class="bg-[#FAF5E8] border border-[#E8DCBF] rounded-2xl p-3.5 flex items-center justify-between shadow-[0_1px_4px_rgba(28,25,23,0.02)]"
    >
      <div class="flex items-center space-x-2.5">
        <div class="w-7 h-7 rounded-lg bg-[#F0E4C3] text-[#785918] flex items-center justify-center shrink-0">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
          </svg>
        </div>
        <span class="text-xs text-[#6B4F15] font-medium leading-snug">Ada draft formulir yang tersimpan sebelumnya.</span>
      </div>
      <div class="flex items-center space-x-1.5 shrink-0 ml-2">
        <button
          type="button"
          @click="restoreDraft"
          class="px-3 py-1 bg-[#7E5C17] text-white rounded-lg text-xs font-semibold active-press transition"
        >
          Pulihkan
        </button>
        <button
          type="button"
          @click="discardDraft"
          class="px-2 py-1 text-navy-500 text-xs font-semibold hover:text-navy-800"
        >
          Abaikan
        </button>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEP 1: INFORMASI LAPORAN                                       -->
    <!-- =============================================================== -->
    <div v-show="currentStep === 1" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 border border-[#DEE1E7] shadow-[0_2px_8px_rgba(28,25,23,0.03)] space-y-4">
        <!-- Nomor otomatis sebagai nilai awal; pengguna dapat mengedit sebelum disimpan -->
        <div>
          <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider mb-1">
            Nomor Registrasi Laporan <span class="text-rose-500">*</span>
          </label>
          <div class="relative">
            <input
              v-model="form.report_number"
              type="text"
              maxlength="100"
              required
              autocomplete="off"
              placeholder="Masukkan nomor laporan"
              class="w-full px-3.5 py-2.5 rounded-xl bg-[#F5F7FA] text-navy-900 font-mono text-xs font-semibold border border-[#CFD3DC] focus:bg-white focus:border-[#1B2C4D] focus:ring-2 focus:ring-[#1B2C4D]/10 transition"
            />
            <span class="absolute right-3 top-2.5 text-[9.5px] uppercase font-bold text-navy-500 bg-[#E5EAF2] px-2 py-0.5 rounded tracking-wide">
              Bisa diedit
            </span>
          </div>
          <p class="text-[11px] text-navy-400 mt-1">Nomor otomatis terisi sebagai saran awal. Kamu dapat mengubahnya; setiap nomor harus unik.</p>
        </div>

        <!-- Tanggal & Hari Otomatis -->
        <div>
          <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider mb-1">
            Tanggal Pemantauan <span class="text-rose-500">*</span>
          </label>
          <input
            type="date"
            v-model="form.inspection_date"
            @change="onDateChange"
            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F5F7FA] text-navy-900 text-xs font-semibold border border-[#CFD3DC] focus:bg-white focus:border-[#1B2C4D] focus:ring-2 focus:ring-[#1B2C4D]/10 transition"
          />
          <!-- Label Hari Otomatis -->
          <div class="mt-2 p-2.5 rounded-xl bg-[#EBEFF6] border border-[#C9D3E7] flex items-center space-x-2">
            <svg class="w-4 h-4 text-[#1B2C4D] shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            <div class="text-xs text-[#14213D]">
              <span class="font-bold">Hari:</span> {{ calculatedDayName }}
              <span class="text-navy-400 mx-1">•</span>
              <span class="text-[#142951] font-medium">{{ formattedFullDate }}</span>
            </div>
          </div>
        </div>

        <!-- Nama Petugas -->
        <div>
          <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider mb-1">
            Nama Petugas Pengawas <span class="text-rose-500">*</span>
          </label>
          <input
            type="text"
            v-model="form.officer_name"
            placeholder="Masukkan nama lengkap petugas..."
            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F5F7FA] text-navy-900 text-xs font-semibold border border-[#CFD3DC] focus:bg-white focus:border-[#1B2C4D] focus:ring-2 focus:ring-[#1B2C4D]/10 transition placeholder:text-navy-400 placeholder:font-normal"
          />
        </div>

        <!-- Foto Petugas -->
        <PhotoUploader
          label="Foto Petugas Pengawas"
          v-model="form.officer_photo"
          aspectRatio="square"
        />
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- STEPS 2 s/d 6: FORM 5 LOKASI                                    -->
    <!-- =============================================================== -->
    <div
      v-for="(loc, locIdx) in locations"
      :key="loc.id"
      v-show="currentStep === locIdx + 2"
      class="space-y-4"
    >
      <div class="bg-white rounded-2xl p-5 border border-[#DEE1E7] shadow-[0_2px_8px_rgba(28,25,23,0.03)] space-y-4">
        <!-- Location Header + Nama Lokasi (editable) -->
        <div class="p-4 rounded-xl bg-[#F5F7FA] border border-[#DDE0E6] space-y-3">
          <div class="flex items-center space-x-2.5">
            <div class="w-7 h-7 rounded-lg bg-[#14213D] text-[#F5A524] font-mono font-bold text-xs flex items-center justify-center shrink-0">
              0{{ locIdx + 1 }}
            </div>
            <span class="text-[10px] uppercase font-bold text-navy-500 tracking-[0.14em]">Area Pemantauan Ke-{{ locIdx + 1 }} dari 5</span>
          </div>
          <div>
            <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider mb-1">
              Nama Area Lokasi <span class="text-rose-500">*</span>
            </label>
            <input
              v-model="form.locations[locIdx].name"
              type="text"
              maxlength="150"
              placeholder="Mis. Halaman Depan Kantor"
              class="w-full px-3.5 py-2.5 rounded-xl bg-white text-navy-900 text-xs font-semibold border border-[#CFD3DC] focus:border-[#1B2C4D] focus:ring-2 focus:ring-[#1B2C4D]/10 transition placeholder:text-navy-400 placeholder:font-normal"
            />
            <p class="text-[11px] text-navy-400 mt-1">Dapat disesuaikan jika titik pemantauan memiliki nama spesifik di lapangan.</p>
          </div>
        </div>

        <!-- DOKUMENTASI 1 -->
        <PhotoUploader
          :label="`Dokumentasi Foto 1 — ${form.locations[locIdx].name || 'Lokasi ' + (locIdx + 1)}`"
          v-model="form.locations[locIdx].photo_1"
          aspectRatio="16:9"
        />

        <!-- DOKUMENTASI 2 -->
        <PhotoUploader
          :label="`Dokumentasi Foto 2 — ${form.locations[locIdx].name || 'Lokasi ' + (locIdx + 1)}`"
          v-model="form.locations[locIdx].photo_2"
          aspectRatio="16:9"
        />

        <!-- KONDISI KEBERSIHAN (Muted Editorial Radio Cards) -->
        <div class="space-y-2 pt-1">
          <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider">
            Status Kondisi Fisik Lokasi <span class="text-rose-500">*</span>
          </label>
          <div class="grid grid-cols-1 gap-2.5">
            <!-- Pilihan 1: clean -->
            <label
              :class="[
                'flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer active-press transition',
                form.locations[locIdx].condition === 'clean'
                  ? 'border-[#1E3869] bg-[#EBEFF6] shadow-[0_1px_4px_rgba(32,70,44,0.06)]'
                  : 'border-[#DBDEE5] hover:border-navy-300 bg-white'
              ]"
            >
              <input
                type="radio"
                :name="`cond_${loc.id}`"
                value="clean"
                v-model="form.locations[locIdx].condition"
                class="w-4 h-4 text-[#1B2C4D] focus:ring-[#1B2C4D]"
              />
              <div class="flex-1">
                <p class="text-xs font-bold text-navy-900">Kondisi Bersih, Tertib & Rapi</p>
                <p class="text-[11px] text-navy-500">Area terpelihara sesuai standar operasional lingkungan</p>
              </div>
              <span class="w-6 h-6 rounded-full bg-[#D6DFEF] text-[#13264A] flex items-center justify-center text-xs font-bold">
                ✓
              </span>
            </label>

            <!-- Pilihan 2: needs_attention -->
            <label
              :class="[
                'flex items-center space-x-3 p-3.5 rounded-xl border cursor-pointer active-press transition',
                form.locations[locIdx].condition === 'needs_attention'
                  ? 'border-[#B8574B] bg-[#FDF0EE] shadow-[0_1px_4px_rgba(184,87,75,0.06)]'
                  : 'border-[#DBDEE5] hover:border-navy-300 bg-white'
              ]"
            >
              <input
                type="radio"
                :name="`cond_${loc.id}`"
                value="needs_attention"
                v-model="form.locations[locIdx].condition"
                class="w-4 h-4 text-[#A84337] focus:ring-[#A84337]"
              />
              <div class="flex-1">
                <p class="text-xs font-bold text-navy-900">Perlu Penanganan / Perbaikan Khusus</p>
                <p class="text-[11px] text-navy-500">Ditemukan sampah, kerusakan sarana, atau kotor</p>
              </div>
              <span class="w-6 h-6 rounded-full bg-[#F6DCD8] text-[#9A3428] flex items-center justify-center text-xs font-bold">
                !
              </span>
            </label>
          </div>
        </div>

        <!-- FORM DINAMIS: JIKA CLEAN -> CATATAN PEMANTAUAN -->
        <div v-if="form.locations[locIdx].condition === 'clean'" class="space-y-1 pt-1 transition-all">
          <label class="block text-xs font-bold text-navy-700 uppercase tracking-wider">
            Catatan Pemantauan Tambahan
          </label>
          <textarea
            v-model="form.locations[locIdx].notes"
            rows="3"
            placeholder="Contoh: Kondisi area sangat bersih dan harum. Semua perlengkapan tersusun rapi."
            class="w-full px-3.5 py-2.5 rounded-xl bg-[#F5F7FA] text-navy-900 text-xs border border-[#CFD3DC] focus:bg-white focus:border-[#1B2C4D] focus:ring-2 focus:ring-[#1B2C4D]/10 transition"
          ></textarea>
        </div>

        <!-- FORM DINAMIS: JIKA NEEDS_ATTENTION -> PERMASALAHAN & SOLUSI -->
        <div v-else-if="form.locations[locIdx].condition === 'needs_attention'" class="space-y-3 pt-1 transition-all">
          <div>
            <label class="block text-xs font-bold text-[#8C3227] uppercase tracking-wider mb-1">
              Permasalahan Temuan <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.locations[locIdx].problem"
              rows="2"
              placeholder="Jelaskan kendala atau bagian yang belum memenuhi standar kebersihan..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-[#FDF7F6] text-navy-900 text-xs border border-[#E9C8C4] focus:bg-white focus:border-[#A84337] focus:ring-2 focus:ring-[#A84337]/15 transition"
            ></textarea>
          </div>

          <div>
            <label class="block text-xs font-bold text-[#7E5714] uppercase tracking-wider mb-1">
              Rencana Tindak Lanjut / Solusi <span class="text-rose-500">*</span>
            </label>
            <textarea
              v-model="form.locations[locIdx].solution"
              rows="2"
              placeholder="Tindakan pembersihan, perapihan atau koordinasi yang dilakukan..."
              class="w-full px-3.5 py-2.5 rounded-xl bg-[#F1F4FA] text-navy-900 text-xs border border-[#EADBBD] focus:bg-white focus:border-[#86601D] focus:ring-2 focus:ring-[#86601D]/15 transition"
            ></textarea>
          </div>
        </div>
      </div>
    </div>

        <!-- STEP 7: PILIH TEMPLATE -->
    <div v-show="currentStep === 7" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 border border-[#DEE1E7] shadow-[0_2px_8px_rgba(28,25,23,0.03)]">
        <h3 class="text-sm font-bold text-navy-900 mb-1">Pilih Desain PDF Widescreen 16:9</h3>
        <p class="text-xs text-navy-500 mb-4">
          PDF 6 halaman akan dibuat otomatis berdasarkan desain template terpilih.
        </p>

        <div class="space-y-3">
          <div
            v-for="tpl in templates"
            :key="tpl.id"
            @click="form.template_id = tpl.id"
            :class="[
              'p-3.5 rounded-2xl border cursor-pointer active-press transition flex flex-col space-y-2.5',
              form.template_id === tpl.id
                ? 'border-[#14213D] bg-[#F2F6F3] ring-1 ring-[#14213D]/20 shadow-sm'
                : 'border-[#DEE1E7] hover:border-navy-300 bg-white'
            ]"
          >
            <div class="relative">
              <TemplateThumb :name="tpl.name" :preview="tpl.preview_url || ''" />
              <div
                v-if="form.template_id === tpl.id"
                class="absolute top-2 right-2 px-2.5 py-1 rounded-full bg-[#14213D] text-[#F5A524] text-[10px] font-bold shadow flex items-center gap-1"
              >
                <span>Aktif Terpilih</span>
                <svg class="w-3 h-3" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
              </div>
            </div>

            <div>
              <h4 class="text-xs font-bold text-navy-900">{{ tpl.name }}</h4>
              <p class="text-[11px] text-navy-500">{{ tpl.description }}</p>
            </div>
          </div>
        </div>
      </div>
    </div>



    <!-- =============================================================== -->
    <!-- STEP 8: PREVIEW & REVIEW SEBELUM SUBMIT                         -->
    <!-- =============================================================== -->
    <div v-show="currentStep === 8" class="space-y-4">
      <div class="bg-white rounded-2xl p-5 border border-[#DEE1E7] shadow-[0_2px_8px_rgba(28,25,23,0.03)] space-y-4">
        <div class="border-b border-navy-100 pb-3 flex items-center justify-between">
          <div>
            <h3 class="text-sm font-bold text-navy-900">Ringkasan Verifikasi Data</h3>
            <p class="text-xs text-navy-500">Periksa kelengkapan seluruh titik pemantauan</p>
          </div>
          <span class="px-2.5 py-1 rounded-lg bg-[#E9ECF2] text-navy-700 text-xs font-semibold font-mono border border-navy-200">
            {{ form.report_number }}
          </span>
        </div>

        <!-- Info Petugas & Tanggal -->
        <div class="p-3 bg-[#F5F7FA] rounded-xl border border-[#DEE1E7] space-y-2 text-xs">
          <div class="flex justify-between">
            <span class="text-navy-500">Tanggal:</span>
            <span class="font-bold text-navy-900">{{ calculatedDayName }}, {{ form.inspection_date }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-navy-500">Petugas:</span>
            <span class="font-bold text-navy-900">{{ form.officer_name || '-' }}</span>
          </div>
          <div class="flex justify-between">
            <span class="text-navy-500">Desain Template:</span>
            <span class="font-bold text-[#1B2C4D]">{{ selectedTemplateName }}</span>
          </div>
        </div>

        <!-- 5 Lokasi Status Checklist -->
        <div class="space-y-2">
          <h4 class="text-xs font-bold uppercase tracking-wider text-navy-600">
            Status 5 Area Lokasi:
          </h4>
          <div
            v-for="(loc, idx) in locations"
            :key="loc.id"
            class="p-2.5 rounded-xl border border-[#DEE1E7] flex items-center justify-between text-xs"
          >
            <div class="flex items-center space-x-2">
              <span class="w-5 h-5 rounded-md bg-[#E4E7ED] text-navy-700 font-mono font-bold flex items-center justify-center text-[10px]">
                {{ idx + 1 }}
              </span>
              <span class="font-medium text-navy-800">{{ form.locations[idx].name || loc.name }}</span>
            </div>
            <span
              :class="[
                'px-2 py-0.5 rounded-full font-semibold text-[10px]',
                form.locations[idx].condition === 'clean'
                  ? 'bg-[#DFE6F3] text-[#13284E]'
                  : 'bg-[#F9E6E4] text-[#8C2C22]'
              ]"
            >
              {{ form.locations[idx].condition === 'clean' ? 'Bersih & Rapi' : 'Perlu Penanganan' }}
            </span>
          </div>
        </div>

        <!-- Simpan & Selesai Banner -->
        <div class="p-3 rounded-xl bg-[#EBEFF6] border border-[#C9D3E7] text-xs text-[#14213D]">
          Setelah data diverifikasi dan disimpan, sistem akan langsung membuat dokumen PDF 16:9.
        </div>
      </div>
    </div>

    <!-- =============================================================== -->
    <!-- WIZARD NAVIGATION BUTTONS (Architectural Floating Controls)     -->
    <!-- =============================================================== -->
    <div class="sticky -bottom-4 -mb-4 z-20 -mx-4 px-4 pt-6 pb-3 bg-gradient-to-t from-[#F5F7FA] from-65% to-transparent pointer-events-none">
      <div class="pointer-events-auto bg-white/95 backdrop-blur-md p-2.5 rounded-2xl shadow-[0_8px_24px_rgba(28,25,23,0.08)] border border-[#DEE1E7] flex items-center gap-2">
        <!-- Tombol Kembali -->
        <button
          v-if="currentStep > 1"
          type="button"
          @click="prevStep"
          class="flex-1 py-2.5 px-3 rounded-xl bg-[#E9ECF2] hover:bg-[#DDE0E6] text-navy-700 text-xs font-semibold active-press transition"
        >
          ← Kembali
        </button>

        <!-- Tombol Lanjut -->
        <button
          v-if="currentStep < totalSteps"
          type="button"
          @click="nextStep"
          class="flex-[2] py-2.5 px-4 rounded-xl bg-[#14213D] hover:bg-[#263B61] text-white text-xs font-semibold shadow-sm active-press transition flex items-center justify-center gap-1.5"
        >
          <span>Lanjutkan</span>
          <svg class="w-3.5 h-3.5 text-[#F5A524]" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
          </svg>
        </button>

        <!-- Tombol Simpan Laporan (Step 8) -->
        <button
          v-if="currentStep === totalSteps"
          type="button"
          :disabled="isSubmitting"
          @click="submitReport"
          class="flex-[2] py-2.5 px-4 rounded-xl bg-[#14213D] hover:bg-[#263B61] text-[#EADBBA] text-xs font-bold shadow-md active-press transition disabled:opacity-50 flex items-center justify-center gap-2"
        >
          <span v-if="isSubmitting" class="w-4 h-4 border-2 border-[#EADBBA] border-t-transparent rounded-full animate-spin"></span>
          <span>{{ isSubmitting ? 'Memproses Dokumen...' : 'Simpan & Terbitkan Laporan' }}</span>
        </button>
      </div>
    </div>

    <!-- ── OFFLINE QUEUE SUCCESS MODAL ── -->
    <Teleport to="body">
      <Transition name="fade">
        <div v-if="showOfflineSuccessModal" class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm" />
      </Transition>
      <Transition name="slide-up">
        <div v-if="showOfflineSuccessModal"
          class="fixed bottom-0 left-0 right-0 z-50 bg-[#F5F7FA] rounded-t-3xl shadow-2xl p-6 space-y-4 border-t border-navy-200"
          style="max-width: 480px; margin: 0 auto; padding-bottom: max(2rem, env(safe-area-inset-bottom));">
          <div class="flex justify-center -mt-2 mb-2">
            <div class="w-10 h-1 rounded-full bg-navy-300"/>
          </div>
          <div class="w-12 h-12 rounded-2xl bg-[#FAF3E6] text-[#865E1E] flex items-center justify-center mx-auto border border-[#EBDEC0]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/>
            </svg>
          </div>
          <div class="text-center space-y-1">
            <h3 class="text-base font-bold text-navy-900">Laporan Diamankan di Antrian Offline</h3>
            <p class="text-xs text-navy-500 leading-relaxed">
              Perangkat sedang tidak memiliki koneksi data. Seluruh formulir dan berkas foto tersimpan aman di penyimpanan internal dan akan <strong>disinkronkan secara otomatis</strong> segera setelah koneksi pulih.
            </p>
          </div>
          <div class="pt-2">
            <button
              type="button"
              @click="router.push('/history')"
              class="w-full py-3 rounded-xl bg-[#14213D] text-[#EADBBA] font-bold text-xs shadow-md active-press transition"
            >
              Buka Arsip Riwayat & Antrian
            </button>
          </div>
        </div>
      </Transition>
    </Teleport>

    <!-- ── TOAST NOTIFICATION ── -->
    <Teleport to="body">
      <Transition name="toast">
        <div v-if="toast.show"
          class="fixed top-safe left-1/2 -translate-x-1/2 z-[60] px-4 py-3 rounded-2xl shadow-xl text-xs font-semibold flex items-center gap-2 min-w-[200px] max-w-[90vw]"
          :class="toast.type === 'success' ? 'bg-[#14213D] text-[#F5A524] border border-[#35507D]' : 'bg-[#7C271E] text-white'"
          style="top: max(1.5rem, env(safe-area-inset-top));">
          <svg v-if="toast.type === 'success'" class="w-4 h-4 shrink-0 text-[#34D399]" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
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
import { useRouter, useRoute } from 'vue-router';
import api, { getApiUrl } from '../services/api';
import PhotoUploader from '../components/PhotoUploader.vue';
import TemplateThumb from '../components/TemplateThumb.vue';
import {
  saveFormDraft,
  getFormDraft,
  clearFormDraft,
  cacheMasterData,
  getCachedMasterData,
  queueOfflineReport,
  isOnline,
} from '../services/offlineStorage.js';

const router = useRouter();
const route = useRoute();

const currentStep = ref(1);
const totalSteps = 8;
const isSubmitting = ref(false);
const hasSavedDraft = ref(false);
const draftRestored = ref(false);
const showOfflineSuccessModal = ref(false);

// Toast (mengganti window.alert — konsisten dengan ReportHistory & ReportPreview)
const toast = reactive({ show: false, message: '', type: 'success' });
let toastTimer = null;
const showToast = (message, type = 'success') => {
  clearTimeout(toastTimer);
  toast.message = message;
  toast.type = type;
  toast.show = true;
  toastTimer = setTimeout(() => { toast.show = false; }, 3000);
};

const stepTitles = [
  '1. Informasi Laporan',
  '2. Lokasi 1 — Halaman Depan',
  '3. Lokasi 2 — Ruang Pelayanan',
  '4. Lokasi 3 — Ruang Kerja',
  '5. Lokasi 4 — Toilet',
  '6. Lokasi 5 — Area Parkir',
  '7. Pemilihan Template',
  '8. Pratinjau & Konfirmasi',
];

const stepDescriptions = [
  'Nomor laporan, tanggal & hari otomatis, identitas petugas.',
  'Dokumentasi dan evaluasi kebersihan Halaman Depan Kantor.',
  'Dokumentasi dan evaluasi kebersihan Ruang Pelayanan.',
  'Dokumentasi dan evaluasi kebersihan Ruang Kerja.',
  'Dokumentasi dan evaluasi kebersihan Toilet.',
  'Dokumentasi dan evaluasi kebersihan Area Parkir.',
  'Pilih salah satu desain PDF widescreen 16:9.',
  'Tinjau kembali data laporan sebelum penyimpanan final.',
];

const locations = ref([
  { id: 1, name: 'Halaman Depan Kantor' },
  { id: 2, name: 'Ruang Pelayanan' },
  { id: 3, name: 'Ruang Kerja' },
  { id: 4, name: 'Toilet' },
  { id: 5, name: 'Area Parkir' },
]);

const templates = ref([]);

// Today's date string YYYY-MM-DD
const todayIso = () => {
  const d = new Date();
  const year = d.getFullYear();
  const month = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  return `${year}-${month}-${day}`;
};

const form = reactive({
  report_number: `LAP-BPSDM-${new Date().getFullYear()}-OFFLINE-${Date.now()}`,
  inspection_date: todayIso(),
  officer_name: '',
  officer_photo: null,
  template_id: 1,
  locations: [
    { location_id: 1, name: 'Halaman Depan Kantor', condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 2, name: 'Ruang Pelayanan', condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 3, name: 'Ruang Kerja', condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 4, name: 'Toilet', condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
    { location_id: 5, name: 'Area Parkir', condition: 'clean', notes: '', problem: '', solution: '', photo_1: null, photo_2: null },
  ],
});

// Hitung hari otomatis berdasarkan tanggal Bahasa Indonesia (Section 10)
const calculatedDayName = computed(() => {
  if (!form.inspection_date) return '';
  const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
  const d = new Date(form.inspection_date + 'T00:00:00');
  return days[d.getDay()] || '';
});

const formattedFullDate = computed(() => {
  if (!form.inspection_date) return '';
  const months = [
    'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
    'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
  ];
  const d = new Date(form.inspection_date + 'T00:00:00');
  return `${d.getDate()} ${months[d.getMonth()]} ${d.getFullYear()}`;
});

const selectedTemplateName = computed(() => {
  const tpl = templates.value.find(t => t.id === form.template_id);
  return tpl ? tpl.name : '-';
});

// Fetch Initial Data
const loadInitialData = async () => {
  try {
    // 1. Fetch next report number if online
    if (isOnline.value) {
      try {
        const numRes = await api.get('/reports/next-number');
        if (numRes.data?.report_number) {
          form.report_number = numRes.data.report_number;
        }
      } catch {}
    } else {
      form.report_number = `LAP-BPSDM-${new Date().getFullYear()}-OFFLINE-${Date.now()}`;
    }

    // 2. Fetch locations (with IndexedDB cache fallback)
    try {
      const locRes = await api.get('/locations');
      if (locRes.data?.data && locRes.data.data.length > 0) {
        locations.value = locRes.data.data;
        await cacheMasterData('locations', locRes.data.data);
      }
    } catch {
      const cachedLocs = await getCachedMasterData('locations');
      if (cachedLocs && cachedLocs.length > 0) {
        locations.value = cachedLocs;
      }
    }

    // Sync locations structure. `name` diisi dari master sebagai saran awal,
    // tapi tetap field milik form ini sendiri sehingga bisa diedit bebas oleh user.
    form.locations = locations.value.map((loc, i) => ({
      location_id: loc.id,
      name: form.locations[i]?.name || loc.name,
      condition: 'clean',
      notes: '',
      problem: '',
      solution: '',
      photo_1: null,
      photo_2: null,
    }));

    // 3. Fetch templates (with IndexedDB cache fallback)
    try {
      const tplRes = await api.get('/templates');
      if (tplRes.data?.data && tplRes.data.data.length > 0) {
        templates.value = tplRes.data.data;
        await cacheMasterData('templates', tplRes.data.data);
      }
    } catch {
      const cachedTpls = await getCachedMasterData('templates');
      if (cachedTpls && cachedTpls.length > 0) {
        templates.value = cachedTpls;
      }
    }

    if (route.query.template_id) {
      form.template_id = parseInt(route.query.template_id);
    } else if (templates.value.length > 0) {
      form.template_id = templates.value[0].id;
    }
  } catch (err) {
    console.warn('Initial load warning:', err.message);
  }

  // Check saved draft from IndexedDB
  await checkSavedDraft();
};

const checkSavedDraft = async () => {
  const draft = await getFormDraft();
  if (draft) {
    hasSavedDraft.value = true;
  }
};

const saveDraftManually = async () => {
  const success = await saveFormDraft(form);
  if (success) {
    showToast('Draft dan foto dokumentasi tersimpan di perangkat ini.');
  } else {
    showToast('Draft gagal disimpan. Periksa ruang penyimpanan perangkat.', 'error');
  }
};

const restoreDraft = async () => {
  const draft = await getFormDraft();
  if (draft) {
    form.report_number = draft.report_number || form.report_number;
    form.inspection_date = draft.inspection_date || form.inspection_date;
    form.officer_name = draft.officer_name || '';
    form.officer_photo = draft.officer_photo || null;
    form.template_id = draft.template_id || form.template_id;

    if (Array.isArray(draft.locations)) {
      draft.locations.forEach((dLoc, i) => {
        if (form.locations[i]) {
          form.locations[i].name = dLoc.name || form.locations[i].name;
          form.locations[i].condition = dLoc.condition || 'clean';
          form.locations[i].notes = dLoc.notes || '';
          form.locations[i].problem = dLoc.problem || '';
          form.locations[i].solution = dLoc.solution || '';
          form.locations[i].photo_1 = dLoc.photo_1 || null;
          form.locations[i].photo_2 = dLoc.photo_2 || null;
        }
      });
    }
    draftRestored.value = true;
    hasSavedDraft.value = false;
  }
};

const discardDraft = async () => {
  await clearFormDraft();
  hasSavedDraft.value = false;
};

// Wizard Navigation
// <main id="app-scroll"> adalah area scroll aplikasi (lihat App.vue); window ikut dijaga untuk mode web biasa.
const scrollToTop = () => {
  document.getElementById('app-scroll')?.scrollTo({ top: 0, behavior: 'smooth' });
  window.scrollTo({ top: 0, behavior: 'smooth' });
};

const nextStep = () => {
  if (currentStep.value === 1) {
    if (!form.officer_name.trim()) {
      showToast('Mohon masukkan nama petugas pengawas.', 'error');
      return;
    }
  } else if (currentStep.value >= 2 && currentStep.value <= 6) {
    const locIdx = currentStep.value - 2;
    const locItem = form.locations[locIdx];
    if (!locItem.name?.trim()) {
      showToast('Mohon isi nama lokasi untuk titik pemantauan ini.', 'error');
      return;
    }
    if (locItem.condition === 'needs_attention') {
      if (!locItem.problem?.trim() || !locItem.solution?.trim()) {
        showToast('Untuk kondisi perlu penanganan, mohon isi permasalahan dan solusinya.', 'error');
        return;
      }
    }
  }

  // Auto-save draft on step progression
  saveFormDraft(form);

  if (currentStep.value < totalSteps) {
    currentStep.value++;
    scrollToTop();
  }
};

const prevStep = () => {
  if (currentStep.value > 1) {
    currentStep.value--;
    scrollToTop();
  }
};

// Submit Report (Online or Offline Queue)
const submitReport = async () => {
  isSubmitting.value = true;

  // 1. If currently offline, queue immediately
  if (!isOnline.value || !navigator.onLine) {
    try {
      await queueOfflineReport(form);
      await clearFormDraft();
      showOfflineSuccessModal.value = true;
    } catch (err) {
      showToast('Gagal menyimpan ke antrian offline: ' + err.message, 'error');
    } finally {
      isSubmitting.value = false;
    }
    return;
  }

  // 2. If online, attempt API submission
  try {
    const formData = new FormData();
    formData.append('report_number', form.report_number);
    formData.append('inspection_date', form.inspection_date);
    formData.append('officer_name', form.officer_name);
    formData.append('template_id', form.template_id);
    
    console.log('[DEBUG FOTO PETUGAS]', {
      adaFoto: !!form.officer_photo,
      tipe: form.officer_photo?.constructor?.name,
      instanceBlob: form.officer_photo instanceof Blob,
      instanceFile: form.officer_photo instanceof File,
      ukuran: form.officer_photo?.size,
      tipeFile: form.officer_photo?.type,
    });

    if (form.officer_photo instanceof Blob) {
      formData.append(
        'officer_photo',
        form.officer_photo,
        form.officer_photo instanceof File
          ? (form.officer_photo.name || 'officer.jpg')
          : 'officer.jpg'
      );
    }

    form.locations.forEach((loc, idx) => {
      formData.append(`locations[${idx}][location_id]`, loc.location_id);
      formData.append(`locations[${idx}][name]`, (loc.name || '').trim());
      formData.append(`locations[${idx}][condition]`, loc.condition);
      if (loc.condition === 'clean') {
        formData.append(`locations[${idx}][notes]`, loc.notes || '');
      } else {
        formData.append(`locations[${idx}][problem]`, loc.problem || '');
        formData.append(`locations[${idx}][solution]`, loc.solution || '');
      }

      if (loc.photo_1 instanceof Blob || loc.photo_1 instanceof File) {
        formData.append(`locations[${idx}][photo_1]`, loc.photo_1, `loc_${loc.location_id}_p1.jpg`);
      }
      if (loc.photo_2 instanceof Blob || loc.photo_2 instanceof File) {
        formData.append(`locations[${idx}][photo_2]`, loc.photo_2, `loc_${loc.location_id}_p2.jpg`);
      }
    });

    const res = await api.post('/reports', formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    });

    if (res.data && res.data.status === 'success') {
      await clearFormDraft();
      const newReportId = res.data.data.id;
      // Navigasi langsung ke halaman preview laporan yang baru dibuat —
      // melihat laporannya sendiri adalah konfirmasi yang lebih jelas
      // daripada dialog alert() yang harus ditutup dulu secara manual.
      router.push(`/preview/${newReportId}`);
    }
  } catch (err) {
    console.error('Error saat menyimpan laporan:', err);
    // Tidak ada respons dari server = koneksi putus ATAU server tidak terjangkau
    // (alamat API salah, timeout, CORS, mixed content). Bedakan keduanya supaya
    // pengguna tidak mengira "offline" padahal internet aktif.
    const noServerResponse = !err.response;
    const deviceOffline = !navigator.onLine;

    if (noServerResponse || deviceOffline) {
      // Graceful fallback to offline queue on network drop
      try {
        await queueOfflineReport(form);
        await clearFormDraft();
        showOfflineSuccessModal.value = true;
        if (!deviceOffline) {
          console.warn('Server tidak terjangkau:', err.code || err.message, getApiUrl());
          showToast(`Internet aktif, tapi server tidak terjangkau (${err.code || err.message}). Alamat: ${getApiUrl()}`, 'error');
        }
      } catch (qErr) {
        showToast('Koneksi terputus dan gagal menyimpan offline: ' + qErr.message, 'error');
      }
    } else {
      const msg = err.response?.data?.message || err.message || 'Terjadi kesalahan sistem.';
      showToast('Gagal menyimpan laporan: ' + msg, 'error');
    }
  } finally {
    isSubmitting.value = false;
  }
};

onMounted(() => {
  loadInitialData();
});
</script>

<style scoped>
.toast-enter-active,
.toast-leave-active { transition: all 0.25s ease; }
.toast-enter-from,
.toast-leave-to {
  opacity: 0;
  transform: translateX(-50%) translateY(-16px);
}

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
