<template>
  <div class="px-4 py-5 space-y-4 pb-24">
    <!-- Header -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm">
      <div class="flex items-center gap-3">
        <router-link to="/templates" class="w-9 h-9 rounded-xl bg-[#ECE8DD] text-[#14251D] flex items-center justify-center shrink-0 active-press" aria-label="Kembali">
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
        </router-link>
        <div>
          <h2 class="text-base font-bold text-[#14251D]">Buat Template</h2>
          <p class="text-xs text-[#59635D]">Unggah desain PowerPoint sebagai template laporan.</p>
        </div>
      </div>
    </div>

    <!-- Syarat template -->
    <div class="bg-[#FAF9F5] rounded-2xl border border-[#E3E0D6] shadow-sm overflow-hidden">
      <button type="button" @click="showReq = !showReq"
        class="w-full flex items-center justify-between px-5 py-4 text-left">
        <span class="text-sm font-bold text-[#14251D]">Syarat membuat template</span>
        <svg class="w-4 h-4 text-[#6F7771] transition-transform" :class="showReq ? 'rotate-180' : ''" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
      </button>

      <div v-show="showReq" class="px-5 pb-5 space-y-4 text-xs text-[#3F4A44] leading-relaxed">
        <div>
          <p class="font-bold text-[#14251D] mb-1">Wajib (file ditolak jika tidak terpenuhi)</p>
          <ul class="list-disc pl-4 space-y-1">
            <li>Berformat <b>.pptx</b>, maksimal 20 MB, tanpa makro.</li>
            <li>Ukuran slide <b>Widescreen 16:9</b>.</li>
            <li>Slide pertama adalah <b>sampul</b>.</li>
            <li>Untuk <b>setiap lokasi 1 sampai 5</b> harus ada minimal satu slide <b>kondisi normal</b> yang berisi penanda lokasi tersebut (angka lokasi ada di penandanya, mis. <code>&lt;&lt;Nama Tempat 1&gt;&gt;</code>).</li>
            <li>Hanya memakai penanda yang dikenal (daftar di bawah). Penanda yang salah ketik akan ditolak.</li>
            <li>Kotak foto harus berupa <b>bentuk (shape/kotak teks)</b> yang berisi teks penanda foto saja, dengan ukuran sendiri.</li>
          </ul>
        </div>

        <div>
          <p class="font-bold text-[#14251D] mb-1">Sangat disarankan</p>
          <ul class="list-disc pl-4 space-y-1">
            <li>Slide <b>kondisi tidak normal</b> untuk tiap lokasi: salin slide normal, lalu tambahkan teks <code>[[tidak1]]</code> (sesuai nomor lokasi). Tanpa slide ini, lokasi yang berstatus "perlu penanganan" tidak muncul di PDF.</li>
            <li>Penanda pada slide normal boleh ditandai <code>[[normal1]]</code>. Penanda <code>[[...]]</code> dihapus otomatis saat laporan dibuat.</li>
          </ul>
        </div>

        <div>
          <p class="font-bold text-[#14251D] mb-1">Penanda yang tersedia</p>
          <div class="rounded-xl border border-[#E3E0D6] overflow-hidden">
            <table class="w-full text-[11px]">
              <thead class="bg-[#ECE8DD] text-[#14251D]">
                <tr><th class="text-left px-3 py-2">Penanda</th><th class="text-left px-3 py-2">Diisi dengan</th></tr>
              </thead>
              <tbody>
                <tr v-for="t in tokens" :key="t[0]" class="border-t border-[#ECE8DD]">
                  <td class="px-3 py-1.5 font-mono whitespace-nowrap">{{ t[0] }}</td>
                  <td class="px-3 py-1.5">{{ t[1] }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="mt-2 text-[#6F7771]">Ganti angka 1 dengan nomor lokasi 1 sampai 5. Penulisan tidak membedakan huruf besar/kecil. Satu penanda sebaiknya diketik utuh dalam satu kali ketikan agar tidak terpecah.</p>
        </div>

        <div>
          <p class="font-bold text-[#14251D] mb-1">Thumbnail</p>
          <ul class="list-disc pl-4 space-y-1">
            <li>PNG, JPG, atau WEBP, maksimal 2 MB.</li>
            <li>Disarankan rasio 16:9 (mis. 1280 x 720), berupa tangkapan layar slide sampul.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="bg-[#FAF9F5] rounded-2xl p-5 border border-[#E3E0D6] shadow-sm space-y-4">
      <div>
        <label class="block text-xs font-bold text-[#14251D] mb-1.5" for="tpl-name">Nama template</label>
        <input id="tpl-name" v-model.trim="form.name" maxlength="60" type="text" placeholder="Contoh: Template Hijau Elegan"
          class="w-full rounded-xl border border-[#DCD7CA] bg-white px-3.5 py-2.5 text-sm text-[#14251D] focus:outline-none focus:border-[#14251D]" />
        <p v-if="fieldError('name')" class="mt-1 text-[11px] text-[#A6382C]">{{ fieldError('name') }}</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-[#14251D] mb-1.5" for="tpl-desc">Deskripsi <span class="font-normal text-[#808983]">(opsional)</span></label>
        <input id="tpl-desc" v-model.trim="form.description" maxlength="255" type="text" placeholder="Keterangan singkat desain"
          class="w-full rounded-xl border border-[#DCD7CA] bg-white px-3.5 py-2.5 text-sm text-[#14251D] focus:outline-none focus:border-[#14251D]" />
      </div>

      <div>
        <label class="block text-xs font-bold text-[#14251D] mb-1.5" for="tpl-pptx">File desain (.pptx)</label>
        <input id="tpl-pptx" ref="pptxInput" type="file"
          accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation"
          @change="onPptx"
          class="block w-full text-xs text-[#3F4A44] file:mr-3 file:rounded-xl file:border-0 file:bg-[#14251D] file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-[#FAF9F5]" />
        <p v-if="form.pptx" class="mt-1 text-[11px] text-[#6F7771]">{{ form.pptx.name }} ({{ sizeLabel(form.pptx.size) }})</p>
      </div>

      <div>
        <label class="block text-xs font-bold text-[#14251D] mb-1.5" for="tpl-thumb">Thumbnail</label>
        <input id="tpl-thumb" ref="thumbInput" type="file" accept="image/png,image/jpeg,image/webp" @change="onThumb"
          class="block w-full text-xs text-[#3F4A44] file:mr-3 file:rounded-xl file:border-0 file:bg-[#14251D] file:px-3.5 file:py-2 file:text-xs file:font-bold file:text-[#FAF9F5]" />
        <p v-if="fieldError('thumbnail')" class="mt-1 text-[11px] text-[#A6382C]">{{ fieldError('thumbnail') }}</p>
        <img v-if="thumbUrl" :src="thumbUrl" alt="Pratinjau thumbnail"
          class="mt-2 w-full aspect-video object-cover rounded-xl border border-[#E3E0D6]" />
      </div>

      <label class="flex items-start gap-2.5 text-xs text-[#3F4A44] cursor-pointer">
        <input v-model="form.agree" type="checkbox" class="mt-0.5 h-4 w-4 accent-[#14251D]" />
        <span>Saya sudah membaca syarat di atas dan template saya memakai penanda yang benar.</span>
      </label>

      <!-- Hasil validasi -->
      <div v-if="pptxErrors.length" class="rounded-xl border border-[#F5C7C1] bg-[#FDF0EE] p-3.5">
        <p class="text-xs font-bold text-[#A6382C] mb-1.5">File PPTX belum memenuhi syarat:</p>
        <ul class="list-disc pl-4 space-y-1 text-[11px] text-[#8F2F24]">
          <li v-for="(e, i) in pptxErrors" :key="i">{{ e }}</li>
        </ul>
      </div>
      <div v-if="warnings.length" class="rounded-xl border border-[#E8D9A8] bg-[#FBF6E4] p-3.5">
        <p class="text-xs font-bold text-[#7A6320] mb-1.5">Peringatan (template tetap bisa disimpan):</p>
        <ul class="list-disc pl-4 space-y-1 text-[11px] text-[#6B5719]">
          <li v-for="(w, i) in warnings" :key="i">{{ w }}</li>
        </ul>
      </div>
      <p v-if="generalError" class="text-xs text-[#A6382C]">{{ generalError }}</p>

      <button type="button" :disabled="!canSubmit || submitting" @click="submit"
        class="w-full flex items-center justify-center gap-2 py-3.5 rounded-2xl bg-[#14251D] text-[#FAF9F5] font-bold text-sm active-press transition disabled:opacity-50">
        <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        {{ submitting ? 'Memeriksa & menyimpan…' : 'Simpan Template' }}
      </button>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onBeforeUnmount } from 'vue';
import { useRouter } from 'vue-router';
import api from '../services/api.js';

const router = useRouter();
const MAX_PPTX = 20 * 1024 * 1024;
const MAX_THUMB = 2 * 1024 * 1024;

// Daftar ini mencerminkan PptxReportService::classify(). Ubah keduanya bersamaan.
const tokens = [
  ['<<Hari>>', 'Nama hari pemantauan (sampul)'],
  ['<<Tanggal>>', 'Tanggal pemantauan (sampul)'],
  ['<<Tim Pengawasan>>', 'Nama petugas / tim (sampul)'],
  ['<<Image:Foto Petugas>>', 'Foto petugas (sampul)'],
  ['<<Nama Tempat 1>>', 'Nama lokasi 1 (angka 1 sampai 5)'],
  ['<<Kondisi Kebersihan 1>>', 'Kondisi lokasi'],
  ['<<Catatan Pemantauan Lokasi 1>>', 'Catatan (kata "catatan" + nomor lokasi)'],
  ['<<Permasalahan Lokasi 1>>', 'Permasalahan (kata "permasalahan" + nomor)'],
  ['<<Solusi Lokasi 1>>', 'Solusi (kata "solusi" + nomor)'],
  ['<<Image:Lokasi 1 Dok1>>', 'Foto dokumentasi pertama lokasi 1'],
  ['<<Image:Lokasi 1 Dok2>>', 'Foto dokumentasi kedua lokasi 1'],
  ['[[tidak1]]', 'Tanda slide kondisi tidak normal lokasi 1'],
  ['[[normal1]]', 'Tanda slide kondisi normal (opsional)'],
];

const showReq = ref(true);
const form = reactive({ name: '', description: '', pptx: null, thumbnail: null, agree: false });
const thumbUrl = ref('');
const submitting = ref(false);
const fieldErrors = ref({});
const pptxErrors = ref([]);
const warnings = ref([]);
const generalError = ref('');

const canSubmit = computed(() => form.name && form.pptx && form.thumbnail && form.agree);
const fieldError = (k) => (fieldErrors.value[k] || [])[0] || '';
const sizeLabel = (b) => (b / 1024 / 1024).toFixed(1) + ' MB';

const resetErrors = () => { fieldErrors.value = {}; pptxErrors.value = []; warnings.value = []; generalError.value = ''; };

const onPptx = (e) => {
  resetErrors();
  const f = e.target.files?.[0] || null;
  if (f && !f.name.toLowerCase().endsWith('.pptx')) {
    pptxErrors.value = ['File desain harus berformat .pptx.'];
    e.target.value = ''; form.pptx = null; return;
  }
  if (f && f.size > MAX_PPTX) {
    pptxErrors.value = ['Ukuran file PPTX maksimal 20 MB.'];
    e.target.value = ''; form.pptx = null; return;
  }
  form.pptx = f;
};

const onThumb = (e) => {
  resetErrors();
  if (thumbUrl.value) URL.revokeObjectURL(thumbUrl.value);
  const f = e.target.files?.[0] || null;
  if (f && (!/^image\/(png|jpeg|webp)$/.test(f.type) || f.size > MAX_THUMB)) {
    fieldErrors.value = { thumbnail: ['Thumbnail harus PNG, JPG, atau WEBP dan maksimal 2 MB.'] };
    e.target.value = ''; form.thumbnail = null; thumbUrl.value = ''; return;
  }
  form.thumbnail = f;
  thumbUrl.value = f ? URL.createObjectURL(f) : '';
};

const submit = async () => {
  resetErrors();
  submitting.value = true;
  try {
    const fd = new FormData();
    fd.append('name', form.name);
    if (form.description) fd.append('description', form.description);
    fd.append('pptx', form.pptx);
    fd.append('thumbnail', form.thumbnail);
    const res = await api.post('/templates', fd);
    const extra = res.data?.warnings || [];
    router.push({ path: '/templates', query: { created: '1', warn: extra.length ? String(extra.length) : undefined } });
  } catch (err) {
    const data = err.response?.data;
    if (err.response?.status === 422 && data) {
      const errs = data.errors || {};
      pptxErrors.value = errs.pptx || [];
      warnings.value = data.warnings || [];
      fieldErrors.value = { name: errs.name, thumbnail: errs.thumbnail };
      if (!pptxErrors.value.length && !errs.name && !errs.thumbnail) generalError.value = data.message || 'Data tidak valid.';
    } else if (!err.response) {
      generalError.value = 'Tidak dapat terhubung ke server. Periksa koneksi Anda.';
    } else {
      generalError.value = data?.message || 'Gagal menyimpan template. Coba lagi.';
    }
  } finally {
    submitting.value = false;
  }
};

onBeforeUnmount(() => { if (thumbUrl.value) URL.revokeObjectURL(thumbUrl.value); });
</script>
