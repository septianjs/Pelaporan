#!/usr/bin/env node
/**
 * Selaraskan warna UI dengan logo Decora (navy #14213D + amber #F5A524).
 *
 * Jalankan dari folder utama proyek:
 *   node scripts/recolor.mjs --dry     # hanya tampilkan apa yang akan berubah
 *   node scripts/recolor.mjs           # terapkan
 *
 * - Hanya menyentuh resources/js (*.vue, *.js). CSS desain PDF/slide TIDAK diubah.
 * - Mengganti kode warna #RRGGBB (hijau tua -> navy, emas/pasir -> amber, krem -> abu kebiruan)
 *   dan kelas Tailwind stone-* -> navy-* (token navy didefinisikan di resources/css/app.css).
 * - Aman dijalankan ulang (warna baru tidak ikut diganti lagi).
 * - Dibiarkan: merah (error), hijau status sukses/online, dan kuning-oranye peringatan/offline.
 */
import { readFileSync, writeFileSync, readdirSync, statSync } from 'node:fs';
import { join, extname } from 'node:path';

const DRY = process.argv.includes('--dry');
const ROOT = join(process.cwd(), 'resources', 'js');

// Penggantian eksplisit untuk warna merek utama (huruf besar semua).
const EXPLICIT = {
  // hijau tua -> navy logo
  '#14251D': '#14213D',
  '#1A3428': '#1B2C4D',
  '#1B382B': '#1B2C4D',
  '#173023': '#14213D',
  '#1C362A': '#14213D',
  '#1E372A': '#263B61',
  '#1E372B': '#263B61',
  '#1D3529': '#263B61',
  '#254234': '#2A4068',
  '#254435': '#2A4068',
  '#233F31': '#2A4068',
  '#2D503F': '#35507D',
  '#2D5441': '#35507D',
  '#0E1A14': '#0B1428',
  // teks hampir-hitam -> navy
  '#1C1B18': '#14213D',
  '#1A1A18': '#14213D',
  // emas/pasir -> amber logo
  '#D8C79C': '#F5A524',
  '#C8B788': '#F5A524',
  '#CDB987': '#F5A524',
  '#D4C497': '#F5A524',
  '#D1C7B7': '#C7D2E5',
};

// Dibiarkan apa adanya (status sukses/online, error, peringatan/offline).
const KEEP = new Set([
  '#EF4444',
  // hijau status sukses/online
  '#34D399', '#EAF3EC', '#CCE4D3', '#1E4826', '#C8E0D0',
  // tint kuning-oranye pucat untuk banner offline/peringatan
  '#FAF2E6', '#FAF3E6', '#FAF5E8', '#FBF6E4', '#FAF2DF',
]);

const TARGET_HUE = 219;

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

  // Hijau (zaitun s.d. toska) -> biru navy, terang-gelapnya dipertahankan.
  if (h >= 75 && h <= 175 && chroma > 4) {
    // Warna gelap dijaga agar tidak lebih jenuh dari navy logo.
    const sat = Math.min(1, s * 1.5 + 0.05);
    return hslToHex([TARGET_HUE, l < 0.35 ? Math.min(sat, 0.6) : sat, l]);
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
    return `-navy-${n}`;
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
    console.log(`${DRY ? '[dry] ' : ''}${file.replace(process.cwd() + '/', '')}: ${count} warna, ${stoneCount} kelas stone->navy`);
    if (!DRY) writeFileSync(file, next);
  }
}

console.log(`\nTotal ${total} penggantian${DRY ? ' (simulasi, belum ditulis)' : ''}.`);
if (DRY) {
  console.log('\nContoh pemetaan:');
  [...seen.entries()].slice(0, process.argv.includes('--all') ? 999 : 25).forEach(([a, b]) => console.log(`  ${a} -> ${b}`));
}
