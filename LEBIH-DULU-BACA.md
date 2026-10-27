# Desain baru: kobalt + stabilo kuning

Isi paket ini (salin ke folder utama proyek, timpa file yang sama):

- resources/js/App.vue                      kerangka + transisi halaman berarah (kiri/kanan)
- resources/js/components/LogoMark.vue      logo baru (formulir + centang + stabilo)
- resources/js/components/TopHeader.vue     header terang, status Online/Offline
- resources/js/components/BottomNav.vue     menu bawah melayang, penanda kuning meluncur
- resources/js/views/Home.vue               beranda baru (sudah memuat perubahan teks sebelumnya)
- resources/css/app.css                     token warna baru + animasi
- scripts/recolor.mjs                       pengganti warna untuk SEMUA halaman lain
- public/logo.svg                           logo siap pakai (favicon / ikon)

## Langkah

1. Simpan dulu pekerjaan lama:   git add -A && git commit -m "sebelum redesign"
2. Ekstrak paket ini ke folder proyek (timpa).
3. Lihat dulu perubahan warna:   node scripts/recolor.mjs --dry
4. Terapkan:                     node scripts/recolor.mjs
5. Build:                        npm run build
6. Buka di browser dengan Ctrl+Shift+R.
   Untuk aplikasi HP: npx cap sync, lalu build APK dari Android Studio.

Batal / kembali ke desain lama:  git checkout .

Catatan
- Skrip hanya mengubah kode warna dan kelas stone-* di resources/js. Desain PDF/slide tidak disentuh.
- Skrip aman dijalankan ulang.
- File resources/js/views/Home.vue.diagnostic-backup adalah sisa cadangan; boleh dihapus.
