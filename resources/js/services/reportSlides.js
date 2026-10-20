/**
 * Util data untuk ReportSlide.vue.
 *
 * Tujuannya: pratinjau di aplikasi menampilkan data PERSIS seperti yang akan
 * dirender PDF (ReportPdfService::generatePdf), termasuk aturan "selalu 5 lokasi".
 */

// Dipakai hanya bila API belum mengirim `template.theme` (mis. cache offline lama).
// Nilai sama dengan template pertama di BuiltinTemplateGeneratorService.
export const FALLBACK_THEME = {
  style_type: 'tropical_pop',
  primary_color: '166534',
  secondary_color: '15803D',
  accent_color: 'D97706',
  bg_color: 'F0FDF4',
  card_bg: 'FFFFFF',
  card_alt: 'DCFCE7',
  photo: { officer: [550, 300], location: [400, 205] },
};

export const TOTAL_LOCATIONS = 5;

const CONDITION_LABELS = {
  clean: 'Kondisi Sudah Bersih dan Rapi',
  needs_attention: 'Kondisi Masih Perlu Penanganan Khusus',
};

const photoUrl = (loc, type) =>
  loc?.report_photos?.find((p) => p.photo_type === type)?.photo_url || null;

/**
 * Ubah report dari API menjadi tepat 5 entri lokasi, sama seperti PDF:
 * titik tanpa data tetap tampil sebagai halaman placeholder.
 */
export function normalizeReportLocations(report) {
  const source = report?.report_locations || [];
  const result = [];
  for (let i = 0; i < TOTAL_LOCATIONS; i++) {
    const loc = source[i];
    if (loc) {
      result.push({
        name: loc.display_name || loc.location?.name || `Lokasi ${i + 1}`,
        condition: loc.condition,
        condition_label: loc.condition_label || CONDITION_LABELS[loc.condition] || '',
        notes: loc.notes,
        problem: loc.problem,
        solution: loc.solution,
        photo_1: photoUrl(loc, 'photo_1'),
        photo_2: photoUrl(loc, 'photo_2'),
      });
    } else {
      result.push({
        name: `Lokasi ${i + 1}`,
        condition: 'clean',
        condition_label: 'Data belum diisi',
        notes: 'Data pemantauan untuk titik ini belum diisi.',
        problem: null,
        solution: null,
        photo_1: null,
        photo_2: null,
      });
    }
  }
  return result;
}

// ── Data contoh untuk miniatur di pemilih template ──────────────────────────
export const SAMPLE_REPORT = {
  report_number: '001/LKL/BPSDM/2026',
  formatted_date: 'Selasa, 20 Oktober 2026',
  officer_name: 'Nama Petugas Pengawas',
  officer_photo_url: null,
};

export const SAMPLE_LOCATIONS = [
  {
    name: 'Lobi Utama',
    condition: 'clean',
    condition_label: CONDITION_LABELS.clean,
    notes: 'Area terpantau bersih, rapi, dan terawat dengan baik.',
    problem: null,
    solution: null,
    photo_1: null,
    photo_2: null,
  },
];
