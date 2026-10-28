<template>
  <div class="template-create-container">
    <!-- Header -->
    <div class="header-card">
      <div class="flex items-center gap-3">
        <router-link
          to="/templates"
          class="btn-back-square"
          aria-label="Kembali"
        >
          <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
          </svg>
        </router-link>
        <div>
          <h2 class="header-title">Buat Template</h2>
          <p class="header-subtitle">Unggah desain PowerPoint sebagai template laporan.</p>
        </div>
      </div>
    </div>

    <!-- Syarat template -->
    <div class="accordion-card">
      <button
        type="button"
        @click="showReq = !showReq"
        class="accordion-header"
      >
        <span class="text-sm font-bold text-[#14213D]">Syarat membuat template</span>
        <svg
          class="w-4 h-4 text-[#676F7F] transition-transform"
          :class="showReq ? 'rotate-180' : ''"
          fill="none"
          stroke="currentColor"
          stroke-width="2"
          viewBox="0 0 24 24"
        >
          <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/>
        </svg>
      </button>

      <div v-show="showReq" class="accordion-body">
        <div>
          <p class="req-title">Wajib (file ditolak jika tidak terpenuhi)</p>
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
          <p class="req-title">Sangat disarankan</p>
          <ul class="list-disc pl-4 space-y-1">
            <li>Slide <b>kondisi tidak normal</b> untuk tiap lokasi: salin slide normal, lalu tambahkan teks <code>[[tidak1]]</code> (sesuai nomor lokasi). Tanpa slide ini, lokasi yang berstatus "perlu penanganan" tidak muncul di PDF.</li>
            <li>Penanda pada slide normal boleh ditandai <code>[[normal1]]</code>. Penanda <code>[[...]]</code> dihapus otomatis saat laporan dibuat.</li>
          </ul>
        </div>

        <div>
          <p class="req-title">Penanda yang tersedia</p>
          <div class="table-container">
            <table class="w-full text-[11px]">
              <thead class="table-head">
                <tr>
                  <th class="text-left px-3 py-2">Penanda</th>
                  <th class="text-left px-3 py-2">Diisi dengan</th>
                </tr>
              </thead>
              <tbody>
                <tr
                  v-for="t in tokens"
                  :key="t[0]"
                  class="border-t border-[#DEE2EB]"
                >
                  <td class="px-3 py-1.5 font-mono whitespace-nowrap">{{ t[0] }}</td>
                  <td class="px-3 py-1.5">{{ t[1] }}</td>
                </tr>
              </tbody>
            </table>
          </div>
          <p class="mt-2 text-[#676F7F]">
            Ganti angka 1 dengan nomor lokasi 1 sampai 5. Penulisan tidak membedakan huruf besar/kecil. Satu penanda sebaiknya diketik utuh dalam satu kali ketikan agar tidak terpecah.
          </p>
        </div>

        <div>
          <p class="req-title">Thumbnail</p>
          <ul class="list-disc pl-4 space-y-1">
            <li>PNG, JPG, atau WEBP, maksimal 2 MB.</li>
            <li>Disarankan rasio 16:9 (mis. 1280 x 720), berupa tangkapan layar slide sampul.</li>
          </ul>
        </div>
      </div>
    </div>

    <!-- Form -->
    <div class="form-card">
      <div class="form-group">
        <label class="form-label" for="tpl-name">Nama template</label>
        <input
          id="tpl-name"
          v-model.trim="form.name"
          maxlength="60"
          type="text"
          placeholder="Contoh: Template Hijau Elegan"
          class="form-input"
        />
        <p v-if="fieldError('name')" class="field-error">{{ fieldError('name') }}</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="tpl-desc">
          Deskripsi <span class="font-normal text-[#788191]">(opsional)</span>
        </label>
        <input
          id="tpl-desc"
          v-model.trim="form.description"
          maxlength="255"
          type="text"
          placeholder="Keterangan singkat desain"
          class="form-input"
        />
      </div>

      <div class="form-group">
        <label class="form-label" for="tpl-pptx">File desain (.pptx)</label>
        <input
          id="tpl-pptx"
          ref="pptxInput"
          type="file"
          accept=".pptx,application/vnd.openxmlformats-officedocument.presentationml.presentation"
          @change="onPptx"
          class="file-input"
        />
        <p v-if="form.pptx" class="file-hint">{{ form.pptx.name }} ({{ sizeLabel(form.pptx.size) }})</p>
      </div>

      <div class="form-group">
        <label class="form-label" for="tpl-thumb">Thumbnail</label>
        <input
          id="tpl-thumb"
          ref="thumbInput"
          type="file"
          accept="image/png,image/jpeg,image/webp"
          @change="onThumb"
          class="file-input"
        />
        <p v-if="fieldError('thumbnail')" class="field-error">{{ fieldError('thumbnail') }}</p>
        <img
          v-if="thumbUrl"
          :src="thumbUrl"
          alt="Pratinjau thumbnail"
          class="thumb-preview"
        />
      </div>

      <label class="agree-checkbox-label">
        <input v-model="form.agree" type="checkbox" class="agree-checkbox" />
        <span>Saya sudah membaca syarat di atas dan template saya memakai penanda yang benar.</span>
      </label>

      <!-- Hasil validasi -->
      <div v-if="pptxErrors.length" class="alert-error-box">
        <p class="text-xs font-bold text-[#A6382C] mb-1.5">File PPTX belum memenuhi syarat:</p>
        <ul class="list-disc pl-4 space-y-1 text-[11px] text-[#8F2F24]">
          <li v-for="(e, i) in pptxErrors" :key="i">{{ e }}</li>
        </ul>
      </div>

      <div v-if="warnings.length" class="alert-warn-box">
        <p class="text-xs font-bold text-[#7A6320] mb-1.5">Peringatan (template tetap bisa disimpan):</p>
        <ul class="list-disc pl-4 space-y-1 text-[11px] text-[#6B5719]">
          <li v-for="(w, i) in warnings" :key="i">{{ w }}</li>
        </ul>
      </div>

      <p v-if="generalError" class="text-xs text-[#A6382C]">{{ generalError }}</p>

      <button
        type="button"
        :disabled="!canSubmit || submitting"
        @click="submit"
        class="btn-submit-template"
      >
        <svg v-if="submitting" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="3"/>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v4a4 4 0 00-4 4H4z"/>
        </svg>
        <span>{{ submitting ? 'Memeriksa & menyimpan…' : 'Simpan Template' }}</span>
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

<style scoped>
.template-create-container {
  padding-left: 1rem;
  padding-right: 1rem;
  padding-top: 1.25rem;
  padding-bottom: 6rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

/* Header */
.header-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
}

.btn-back-square {
  width: 2.25rem;
  height: 2.25rem;
  border-radius: 0.75rem;
  background-color: #DEE2EB;
  color: #14213D;
  display: flex;
  align-items: center;
  justify-content: center;
  flex-shrink: 0;
  text-decoration: none;
  transition: all 0.15s ease;
}

.btn-back-square:active {
  transform: scale(0.97);
}

.header-title {
  font-size: 1rem;
  font-weight: 700;
  color: #14213D;
}

.header-subtitle {
  font-size: 0.75rem;
  color: #525A6A;
}

/* Accordion */
.accordion-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  overflow: hidden;
}

.accordion-header {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: space-between;
  padding: 1rem 1.25rem;
  text-align: left;
  background: transparent;
  border: none;
  cursor: pointer;
}

.accordion-body {
  padding-left: 1.25rem;
  padding-right: 1.25rem;
  padding-bottom: 1.25rem;
  display: flex;
  flex-direction: column;
  gap: 1rem;
  font-size: 0.75rem;
  color: #394150;
  line-height: 1.6;
}

.req-title {
  font-weight: 700;
  color: #14213D;
  margin-bottom: 0.25rem;
}

.table-container {
  border-radius: 0.75rem;
  border: 1px solid #D7DBE2;
  overflow: hidden;
}

.table-head {
  background-color: #DEE2EB;
  color: #14213D;
}

/* Form Card */
.form-card {
  background-color: #F5F7FA;
  border-radius: 1rem;
  padding: 1.25rem;
  border: 1px solid #D7DBE2;
  box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
  display: flex;
  flex-direction: column;
  gap: 1rem;
}

.form-group {
  display: flex;
  flex-direction: column;
}

.form-label {
  display: block;
  font-size: 0.75rem;
  font-weight: 700;
  color: #14213D;
  margin-bottom: 0.375rem;
}

.form-input {
  width: 100%;
  border-radius: 0.75rem;
  border: 1px solid #CBD1DB;
  background-color: #FFFFFF;
  padding: 0.625rem 0.875rem;
  font-size: 0.875rem;
  color: #14213D;
  box-sizing: border-box;
  transition: border-color 0.15s ease;
}

.form-input:focus {
  outline: none;
  border-color: #14213D;
}

.file-input {
  display: block;
  width: 100%;
  font-size: 0.75rem;
  color: #394150;
}

.file-input::file-selector-button {
  margin-right: 0.75rem;
  border-radius: 0.75rem;
  border: none;
  background-color: #14213D;
  padding: 0.5rem 0.875rem;
  font-size: 0.75rem;
  font-weight: 700;
  color: #F5F7FA;
  cursor: pointer;
  transition: background-color 0.15s ease;
}

.file-input::file-selector-button:hover {
  background-color: #1B2C4D;
}

.file-hint {
  margin-top: 0.25rem;
  font-size: 0.6875rem;
  color: #676F7F;
}

.field-error {
  margin-top: 0.25rem;
  font-size: 0.6875rem;
  color: #A6382C;
}

.thumb-preview {
  margin-top: 0.5rem;
  width: 100%;
  aspect-ratio: 16 / 9;
  object-fit: cover;
  border-radius: 0.75rem;
  border: 1px solid #D7DBE2;
}

.agree-checkbox-label {
  display: flex;
  align-items: flex-start;
  gap: 0.625rem;
  font-size: 0.75rem;
  color: #394150;
  cursor: pointer;
  line-height: 1.4;
}

.agree-checkbox {
  margin-top: 0.125rem;
  width: 1rem;
  height: 1rem;
  accent-color: #14213D;
}

/* Alerts */
.alert-error-box {
  border-radius: 0.75rem;
  border: 1px solid #F5C7C1;
  background-color: #FDF0EE;
  padding: 0.875rem;
}

.alert-warn-box {
  border-radius: 0.75rem;
  border: 1px solid #E8D9A8;
  background-color: #FBF6E4;
  padding: 0.875rem;
}

/* Submit Button */
.btn-submit-template {
  width: 100%;
  display: flex;
  align-items: center;
  justify-content: center;
  gap: 0.5rem;
  padding: 0.875rem;
  border-radius: 1rem;
  background-color: #14213D;
  color: #F5F7FA;
  font-weight: 700;
  font-size: 0.875rem;
  border: none;
  cursor: pointer;
  transition: all 0.2s ease;
}

.btn-submit-template:active {
  transform: scale(0.98);
}

.btn-submit-template:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}
</style>
