#!/usr/bin/env node
/**
 * Ganti palet hijau/krem lama menjadi palet baru (kobalt + stabilo kuning + kertas dingin).
 *
 * Jalankan dari folder utama proyek:
 *   node scripts/recolor.mjs --dry     # hanya tampilkan apa yang akan berubah
 *   node scripts/recolor.mjs           # terapkan
 *
 * - Hanya menyentuh resources/js (*.vue, *.js). CSS desain PDF/slide TIDAK diubah.
 * - Hanya mengganti kode warna #RRGGBB dan kelas Tailwind stone-* -> slate-* (abu netral jadi abu kebiruan); teks dan logika di file tidak disentuh.
 * - Aman dijalankan ulang (warna baru tidak ikut diganti lagi).
 * - Warna merah (error), kuning-oranye (peringatan/offline), dan dan titik status merah #EF4444 dibiarkan.
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';

const DRY = process.argv.includes('--dry');
const ROOT = join(process.cwd(), 'resources', 'js');

// Penggantian eksplisit untuk warna merek utama (huruf besar semua).
const EXPLICIT = {
  // hijau tua -> kobalt
  '#14251D': '#1C2BA0',
  '#1A3428': '#1C2BA0',
  '#1B382B': '#1C2BA0',
  '#173023': '#16217F',
  '#1C362A': '#16217F',
  '#1E372A': '#2433B3',
  '#1E372B': '#2433B3',
  '#1D3529': '#2433B3',
  '#254234': '#2B3CC8',
  '#254435': '#3040C0',
  '#233F31': '#3344C8',
  '#2D503F': '#3F50D0',
  '#2D5441': '#3F50D0',
  '#0E1A14': '#0A0F2E',
  // emas/pasir -> stabilo kuning
  '#D8C79C': '#FFD84D',
  '#C8B788': '#FFD84D',
  '#CDB987': '#FFD84D',
  '#D4C497': '#FFD84D',
  '#D1C7B7': '#C9D0FF',
  // centang hijau pada toast gelap -> stabilo kuning
  '#34D399': '#FFD84D',
};

// Dibiarkan apa adanya (status online/offline, dll.).
const KEEP = new Set([
  '#EF4444',
  // tint kuning-oranye pucat untuk banner offline/peringatan
  '#FAF2E6', '#FAF3E6', '#FAF5E8', '#FBF6E4', '#FAF2DF',
]);

const TARGET_HUE = 232;

function hexToRgb(h) {
  return [1, 3, 5].map((i) => parseInt(h.slice(i, i + 2), 16));
}
function rgbToHsl([r, g, b]) {
  r /= 255; g /= 255; b /= 255;
  const max = Math.max(r, g, b), min = Math.min(r, g, b);
  const l = (max + min) / 2;
  const d = max - min;
  let h = 0, s = 0;
  if (d) {
    s = d / (1 - Math.abs(2 * l - 1));
    if (max === r) h = ((g - b) / d) % 6;
    else if (max === g) h = (b - r) / d + 2;
    else h = (r - g) / d + 4;
    h *= 60;
    if (h < 0) h += 360;
  }
  return [h, s, l];
}
function hslToHex([h, s, l]) {
  const c = (1 - Math.abs(2 * l - 1)) * s;
  const x = c * (1 - Math.abs(((h / 60) % 2) - 1));
  const m = l - c / 2;
  let rgb;
  if (h < 60) rgb = [c, x, 0];
  else if (h < 120) rgb = [x, c, 0];
  else if (h < 180) rgb = [0, c, x];
  else if (h < 240) rgb = [0, x, c];
  else if (h < 300) rgb = [x, 0, c];
  else rgb = [c, 0, x];
  return (
    '#' +
    rgb
      .map((v) => Math.round((v + m) * 255).toString(16).padStart(2, '0'))
      .join('')
      .toUpperCase()
  );
}

function convert(hex) {
  const up = hex.toUpperCase();
  if (EXPLICIT[up]) return EXPLICIT[up];
  if (KEEP.has(up)) return up;

  const rgb = hexToRgb(up);
  const [h, s, l] = rgbToHsl(rgb);
  const chroma = Math.max(...rgb) - Math.min(...rgb);

  // Hijau (zaitun s.d. toska) -> biru kobalt, terang-gelapnya dipertahankan.
  if (h >= 75 && h <= 175 && chroma > 4) {
    return hslToHex([TARGET_HUE, Math.min(1, s * 1.5 + 0.05), l]);
  }
  // Krem/beige hampir-netral -> abu dingin kebiruan (untuk latar, garis, teks sekunder).
  if (h >= 25 && h <= 70 && chroma <= 24) {
    return hslToHex([TARGET_HUE, Math.max(0.1, s * 0.9), l]);
  }
  return up;
}

function walk(dir, out = []) {
  for (const name of readdirSync(dir)) {
    const p = join(dir, name);
    if (name === 'node_modules') continue;
    const st = statSync(p);
    if (st.isDirectory()) walk(p, out);
    else if (['.vue', '.js'].includes(extname(p))) out.push(p);
  }
  return out;
}

let total = 0;
const seen = new Map();

for (const file of walk(ROOT)) {
  const src = readFileSync(file, 'utf8');
  let count = 0;
  let stoneCount = 0;
  const swapped = src.replace(/-stone-(\d{2,3})\b/g, (_, n) => {
    stoneCount++;
    return `-slate-${n}`;
  });
  const next = swapped.replace(/#[0-9A-Fa-f]{6}\b/g, (m) => {
    const to = convert(m);
    if (to.toUpperCase() === m.toUpperCase()) return m;
    count++;
    seen.set(m.toUpperCase(), to);
    return to;
  });
  if (count || stoneCount) {
    total += count + stoneCount;
    console.log(`${DRY ? '[dry] ' : ''}${file.replace(process.cwd() + '/', '')}: ${count} warna, ${stoneCount} kelas stone->slate`);
    if (!DRY) writeFileSync(file, next);
  }
}

console.log(`\nTotal ${total} penggantian${DRY ? ' (simulasi, belum ditulis)' : ''}.`);
if (DRY) {
  console.log('\nContoh pemetaan:');
  [...seen.entries()].slice(0, process.argv.includes('--all') ? 999 : 25).forEach(([a, b]) => console.log(`  ${a} -> ${b}`));
}
