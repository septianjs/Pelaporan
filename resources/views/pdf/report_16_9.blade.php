<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $report->report_number }} - Laporan Pengawasan Lingkungan BPSDM</title>
<style>
  @page {
    size: 960pt 540pt;
    margin: 0;
  }
  * {
    box-sizing: border-box;
    -webkit-font-smoothing: antialiased;
  }
  body {
    margin: 0;
    padding: 0;
    font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
    color: #1e293b;
    background-color: {{ $theme['bg_color'] ?? '#F8FAFC' }};
  }
  .slide {
    width: 960pt;
    height: 540pt;
    page-break-after: always;
    position: relative;
    overflow: hidden;
    background-color: {{ $theme['bg_color'] ?? '#F8FAFC' }};
  }
  .slide-last {
    page-break-after: avoid;
  }

  /* ── COVER SLIDE STYLES ── */
  .cover-header {
    background: #{{ $theme['primary_color'] ?? '1E3A8A' }};
    height: 70pt;
    padding: 24pt 50pt;
    position: relative;
    border-bottom: 5pt solid #{{ $theme['accent_color'] ?? '0284C7' }};
  }
  .cover-badge {
    color: #{{ $theme['accent_color'] ?? '38BDF8' }};
    font-size: 10pt;
    font-weight: bold;
    letter-spacing: 2pt;
    text-transform: uppercase;
    margin-bottom: 6pt;
  }
  .cover-title {
    color: #ffffff;
    font-size: 26pt;
    font-weight: bold;
    letter-spacing: 0.5pt;
    margin: 0;
    line-height: 1.1;
  }
  .cover-body {
    padding: 40pt 50pt;
    height: 380pt;
    position: relative;
  }
  /* CATATAN DomPDF (penting saat mengubah layout):
     - `box-sizing: border-box` TIDAK didukung -> padding & border ditambahkan di LUAR width/height.
       Jumlahkan semua tinggi (termasuk padding/border) agar tidak melebihi 540pt, kalau tidak
       konten terlempar ke halaman baru.
     - Float + teks dinamis di cover pernah memicu page-break salah, jadi kartu kiri memakai
       position:relative dan frame foto memakai position:absolute (tidak ikut hitungan tinggi parent).
     - Ukuran foto petugas di sini (560x300pt) HARUS sama dengan OFFICER_PHOTO_W/H di
       ReportPdfService.php, kalau tidak foto tampak melar. */
  .cover-card-left {
    position: relative;
    top: 50px;
    left: 25px;
    width: 280pt;
    background: #ffffff;
    border-radius: 14pt;
    padding: 24pt;
    border: 1.5pt solid #e2e8f0;
    box-shadow: 0 4pt 12pt rgba(0,0,0,0.03);
  }
  .meta-group {
    margin-bottom: 16pt;
  }
  .meta-label {
    font-size: 9pt;
    font-weight: bold;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
    margin-bottom: 3pt;
  }
  .meta-value {
    font-size: 16pt;
    font-weight: bold;
    color: #{{ $theme['primary_color'] ?? '1E3A8A' }};
  }
  .meta-value-sub {
    font-size: 13pt;
    font-weight: bold;
    color: #334155;
  }
  .cover-photo-frame {
    position: Absolute;
    top: 150pt;
    right: 20pt;
    width: 580pt;
    height: 344pt;
    background: #ffffff;
    border-radius: 10pt;
    padding: 8pt;
    border: 2pt solid #{{ $theme['accent_color'] ?? '0284C7' }};
    text-align: center;
    box-shadow: 0 6pt 16pt rgba(0,0,0,0.06);
  }
  .cover-photo-img {
    width: 560pt;
    height: 300pt;
    border-radius: 12pt;
    object-fit: cover;
  }
  .cover-photo-placeholder {
    width: 560pt;
    height: 300pt;
    border-radius: 12pt;
    background: #e2e8f0;
    line-height: 260pt;
    color: #94a3b8;
    font-size: 12pt;
    font-weight: bold;
  }
  .cover-footer {
    position: absolute;
    bottom: 14pt;
    left: 50pt;
    right: 50pt;
    font-size: 9pt;
    color: #94a3b8;
    border-top: 1pt solid #e2e8f0;
    padding-top: 8pt;
  }

  /* ── LOCATION SLIDE STYLES ── */
  .loc-header {
    background: #{{ $theme['primary_color'] ?? '1E3A8A' }};
    padding: 10pt 45pt;
    color: #ffffff;
    height: 44pt;
    border-bottom: 4pt solid #{{ $theme['accent_color'] ?? '0284C7' }};
  }
  .loc-counter {
    color: #{{ $theme['accent_color'] ?? '38BDF8' }};
    font-size: 9pt;
    font-weight: bold;
    letter-spacing: 1.5pt;
    text-transform: uppercase;
  }
  .loc-title {
    font-size: 20pt;
    font-weight: bold;
    margin-top: 2pt;
    color: #ffffff;
  }
  .loc-body {
    padding: 20pt 45pt;
  }
  .photos-container {
    width: 100%;
    margin-bottom: 16pt;
  }
  .photo-box {
    width: 415pt;
    background: #ffffff;
    border-radius: 12pt;
    border: 1.5pt solid #e2e8f0;
    padding: 6pt;
    float: left;
    box-shadow: 0 4pt 10pt rgba(0,0,0,0.03);
  }
  .photo-box-right {
    float: right;
  }
  .photo-img {
    width: 400pt;
    height: 220pt;
    border-radius: 8pt;
    object-fit: cover;
    display: block;
  }
  .photo-placeholder {
    width: 400pt;
    height: 220pt;
    border-radius: 8pt;
    background: #f1f5f9;
    text-align: center;
    line-height: 220pt;
    color: #94a3b8;
    font-size: 11pt;
    font-weight: bold;
  }
  .photo-label {
    font-size: 8.5pt;
    font-weight: bold;
    color: #64748b;
    text-align: center;
    padding-top: 5pt;
    text-transform: uppercase;
    letter-spacing: 0.5pt;
  }
  .info-box {
    clear: both;
    background: #ffffff;
    border-radius: 12pt;
    padding: 14pt 18pt;
    border: 1.5pt solid #e2e8f0;
    box-shadow: 0 4pt 10pt rgba(0,0,0,0.03);
  }
  .badge-clean {
    display: inline-block;
    background: #dcfce7;
    color: #15803d;
    border: 1pt solid #86efac;
    padding: 3pt 10pt;
    border-radius: 6pt;
    font-size: 10pt;
    font-weight: bold;
  }
  .badge-attention {
    display: inline-block;
    background: #fee2e2;
    color: #b91c1c;
    border: 1pt solid #fca5a5;
    padding: 3pt 10pt;
    border-radius: 6pt;
    font-size: 10pt;
    font-weight: bold;
  }
  .info-table {
    width: 100%;
    margin-top: 8pt;
    border-collapse: collapse;
  }
  .info-table td {
    vertical-align: top;
    padding: 4pt 0;
    font-size: 10pt;
  }
  .info-title {
    width: 130pt;
    font-weight: bold;
    color: #475569;
  }
  .info-content {
    color: #1e293b;
    line-height: 1.35;
  }
  .loc-footer {
    position: absolute;
    bottom: 14pt;
    left: 45pt;
    width: 870pt;
    height: 14pt;
    font-size: 8pt;
    color: #94a3b8;
    border-top: 1pt solid #e2e8f0;
    padding-top: 5pt;
  }
  .footer-left {
    float: left;
    text-align: left;
  }
  .footer-right {
    float: right;
    text-align: right;
  }
  /* =========================================================
     DISTINCT PDF TEMPLATE COMPOSITIONS
     DomPDF-safe properties only; preserve existing Blade data.
  ========================================================= */
  .cover-header { position: relative; }
  .cover-header:after {
    content: '✦  ✦  ✦';
    position: absolute; right: 42pt; top: 15pt;
    font-size: 24pt; color: #{{ $theme['accent_color'] ?? '0284C7' }};
  }
  .loc-header { position: relative; }
  .loc-header:after {
    content: '◆  ◆  ◆'; float: right;
    color: #{{ $theme['accent_color'] ?? '0284C7' }};
    font-size: 12pt; margin-top: -4pt;
  }

  /* 1. Tropis Nusantara: botanical palette, generous hero photo, rounded frames. */
  .theme-tropical_pop .cover-header { height: 88pt; padding: 17pt 38pt; border-bottom: 9pt solid #F59E0B; }
  .theme-tropical_pop .cover-title { font-size: 25pt; }
  .theme-tropical_pop .cover-card-left { left: 15pt; top: 28pt; width: 300pt; border: 3pt solid #15803D; border-radius: 18pt; }
  .theme-tropical_pop .cover-photo-frame { right: 24pt; top: 132pt; width: 570pt; height: 344pt; border: 5pt solid #D97706; border-radius: 20pt; }
  .theme-tropical_pop .cover-photo-img, .theme-tropical_pop .cover-photo-placeholder { width: 550pt; height: 300pt; border-radius: 12pt; }
  .theme-tropical_pop .loc-header { border-bottom: 8pt solid #D97706; }
  .theme-tropical_pop .photo-box, .theme-tropical_pop .info-box { border: 3pt solid #15803D; border-radius: 14pt; }
  .theme-tropical_pop .photo-img, .theme-tropical_pop .photo-placeholder { height: 205pt; }

  /* 2. Batik Nusantara: double-line frame and traditional gold accents. */
  .theme-batik_nusantara .slide { border: 3pt double #A16207; }
  .theme-batik_nusantara .cover-header { height: 82pt; padding: 18pt 42pt; border-bottom: 8pt double #F59E0B; }
  .theme-batik_nusantara .cover-card-left { left: 10pt; top: 30pt; border: 3pt double #A16207; border-radius: 2pt; }
  .theme-batik_nusantara .cover-photo-frame { right: 28pt; top: 140pt; border: 5pt double #A16207; border-radius: 2pt; }
  .theme-batik_nusantara .loc-header { border-bottom: 7pt double #F59E0B; }
  .theme-batik_nusantara .photo-box, .theme-batik_nusantara .info-box { border: 2pt double #A16207; border-radius: 2pt; }
  .theme-batik_nusantara .photo-label { color: #854D0E; }

  /* 3. Infografis Geometris: bold blocks, asymmetry and oversized location counter. */
  .theme-comic_pop .cover-header { height: 100pt; padding: 20pt 35pt; border-bottom: 12pt solid #F97316; }
  .theme-comic_pop .cover-title { font-size: 28pt; }
  .theme-comic_pop .cover-card-left { left: 0; top: 18pt; width: 285pt; border: 4pt solid #0F766E; border-radius: 0; }
  .theme-comic_pop .cover-photo-frame { right: 30pt; top: 145pt; width: 545pt; height: 330pt; border: 4pt solid #0F766E; border-radius: 0; }
  .theme-comic_pop .loc-header { height: 58pt; padding: 8pt 40pt; border-bottom: 10pt solid #F97316; }
  .theme-comic_pop .loc-counter { font-size: 11pt; color: #FDE047; }
  .theme-comic_pop .loc-title { font-size: 22pt; }
  .theme-comic_pop .photo-box, .theme-comic_pop .info-box { border: 3pt solid #0F766E; border-radius: 0; box-shadow: 4pt 4pt 0 #F97316; }

  /* 4. Jurnal Dokumentasi: taped-paper feel and dashed photo frames. */
  .theme-scrapbook .cover-header { height: 76pt; padding: 16pt 46pt; border-bottom: 3pt dashed #BE123C; }
  .theme-scrapbook .cover-card-left { left: 30pt; top: 34pt; border: 2pt dashed #BE123C; border-radius: 4pt; }
  .theme-scrapbook .cover-photo-frame { right: 35pt; top: 138pt; border: 4pt dashed #BE123C; border-radius: 4pt; }
  .theme-scrapbook .loc-header { border-bottom: 4pt dashed #FBBF24; }
  .theme-scrapbook .photo-box { border: 3pt dashed #BE123C; border-radius: 3pt; box-shadow: 3pt 3pt 0 #FBBF24; }
  .theme-scrapbook .info-box { border: 2pt dashed #BE123C; border-radius: 3pt; background: #FFFBEB; }
  .theme-scrapbook .photo-label { background: #FEF3C7; padding: 5pt; }

  /* 5. Merah Putih Institusional: symmetric official cover and formal red bars. */
  .theme-memphis .cover-header { height: 88pt; padding: 18pt 50pt; text-align: center; border-bottom: 10pt solid #DC2626; }
  .theme-memphis .cover-title { font-size: 24pt; letter-spacing: 0.2pt; }
  .theme-memphis .cover-badge { color: #FCA5A5; }
  .theme-memphis .cover-card-left { position: absolute; left: 145pt; top: 105pt; width: 670pt; text-align: center; border: 2pt solid #DC2626; border-radius: 0; }
  .theme-memphis .cover-photo-frame { right: 195pt; top: 250pt; width: 570pt; height: 260pt; border: 4pt solid #DC2626; border-radius: 0; }
  .theme-memphis .cover-photo-img, .theme-memphis .cover-photo-placeholder { width: 550pt; height: 205pt; border-radius: 0; }
  .theme-memphis .loc-header { text-align: center; border-bottom: 9pt solid #DC2626; }
  .theme-memphis .photo-box, .theme-memphis .info-box { border: 2pt solid #B91C1C; border-radius: 0; box-shadow: none; }

  /* 6. Eco Green Report: clean environmental green with strong condition panel. */
  .theme-festival .cover-header { height: 88pt; padding: 18pt 45pt; border-bottom: 7pt solid #84CC16; }
  .theme-festival .cover-card-left { left: 24pt; top: 25pt; border-left: 9pt solid #16A34A; border-radius: 4pt 14pt 14pt 4pt; }
  .theme-festival .cover-photo-frame { right: 22pt; top: 135pt; border: 5pt solid #16A34A; border-radius: 16pt; }
  .theme-festival .loc-header { border-bottom: 7pt solid #84CC16; }
  .theme-festival .photo-box, .theme-festival .info-box { border: 2pt solid #16A34A; border-radius: 12pt; }
  .theme-festival .info-box { border-top: 7pt solid #84CC16; }
  .theme-festival .badge-clean { background: #DCFCE7; color: #166534; border-color: #86EFAC; }

  /* 7. Arsip Klasik: archival paper, serif-inspired framing and double borders. */
  .theme-retro_groovy .slide { background: #FFFBEB; }
  .theme-retro_groovy .cover-header { height: 84pt; padding: 18pt 42pt; border-bottom: 6pt double #92400E; }
  .theme-retro_groovy .cover-title { font-size: 24pt; letter-spacing: 0.4pt; }
  .theme-retro_groovy .cover-card-left { left: 18pt; top: 28pt; border: 3pt double #92400E; border-radius: 0; background: #FFFBEB; }
  .theme-retro_groovy .cover-photo-frame { right: 30pt; top: 143pt; border: 5pt double #92400E; border-radius: 0; background: #FFFBEB; }
  .theme-retro_groovy .loc-header { background: #78350F; border-bottom: 6pt double #D6A756; }
  .theme-retro_groovy .photo-box, .theme-retro_groovy .info-box { border: 3pt double #92400E; border-radius: 0; background: #FFFBEB; }

  /* 8. Majalah Editorial: dominant hero image on cover and editorial location grid. */
  .theme-rainbow_infographic .cover-header { height: 74pt; padding: 16pt 40pt; border-bottom: 5pt solid #0F172A; }
  .theme-rainbow_infographic .cover-title { font-size: 24pt; }
  .theme-rainbow_infographic .cover-card-left { left: 35pt; top: 26pt; width: 290pt; border-top: 7pt solid #0F172A; border-radius: 0; }
  .theme-rainbow_infographic .cover-photo-frame { right: 20pt; top: 130pt; width: 600pt; height: 350pt; border: 0; border-bottom: 8pt solid #0EA5E9; border-radius: 0; }
  .theme-rainbow_infographic .cover-photo-img, .theme-rainbow_infographic .cover-photo-placeholder { width: 580pt; height: 300pt; border-radius: 0; }
  .theme-rainbow_infographic .loc-header { background: #0F172A; border-bottom: 6pt solid #0EA5E9; }
  .theme-rainbow_infographic .photo-box { border: 0; border-bottom: 5pt solid #0EA5E9; border-radius: 0; box-shadow: none; }
  .theme-rainbow_infographic .info-box { border: 0; border-top: 3pt solid #0F172A; border-radius: 0; box-shadow: none; }

  /* 9. Blueprint Inspeksi: technical blue grid impression and annotation borders. */
  .theme-paper_cut .slide { background: #EFF6FF; }
  .theme-paper_cut .cover-header { height: 94pt; padding: 20pt 40pt; border-bottom: 8pt solid #38BDF8; }
  .theme-paper_cut .cover-card-left { left: 30pt; top: 20pt; border: 2pt solid #1D4ED8; border-radius: 0; box-shadow: 5pt 5pt 0 #BFDBFE; }
  .theme-paper_cut .cover-photo-frame { right: 22pt; top: 140pt; border: 3pt solid #1D4ED8; border-radius: 0; box-shadow: 5pt 5pt 0 #38BDF8; }
  .theme-paper_cut .loc-header { background: #1E40AF; border-bottom: 6pt solid #38BDF8; }
  .theme-paper_cut .photo-box, .theme-paper_cut .info-box { border: 2pt solid #1D4ED8; border-radius: 0; box-shadow: 3pt 3pt 0 #BFDBFE; }
  .theme-paper_cut .photo-label { color: #1D4ED8; }

  /* 10. Emerald Gold Ornamental: layered gold frame and symmetrical accents. */
  .theme-botanical .slide { background: #ECFDF5; }
  .theme-botanical .cover-header { height: 84pt; padding: 18pt 44pt; border-bottom: 5pt double #B8860B; }
  .theme-botanical .cover-card-left { left: 18pt; top: 26pt; border: 3pt double #B8860B; border-radius: 18pt 3pt 18pt 3pt; }
  .theme-botanical .cover-photo-frame { right: 28pt; top: 138pt; border: 5pt double #B8860B; border-radius: 18pt 3pt 18pt 3pt; }
  .theme-botanical .loc-header { background: #064E3B; border-bottom: 5pt double #D4AF37; }
  .theme-botanical .photo-box, .theme-botanical .info-box { border: 3pt double #B8860B; border-radius: 12pt 2pt 12pt 2pt; }
  .theme-botanical .info-box { border-left-width: 7pt; }

  /* Alternate asymmetric layouts for editorial and blueprint designs.
     Keep the two evidence photos and findings visible on the same page. */
  .theme-paper_cut .photos-container,
  .theme-rainbow_infographic .photos-container { width: 330pt; margin-bottom: 0; }
  .theme-paper_cut .photo-box,
  .theme-rainbow_infographic .photo-box { width: 315pt; float: left; padding: 5pt; margin-bottom: 8pt; }
  .theme-paper_cut .photo-img,
  .theme-rainbow_infographic .photo-img,
  .theme-paper_cut .photo-placeholder,
  .theme-rainbow_infographic .photo-placeholder { width: 300pt; height: 158pt; }
  .theme-paper_cut .photo-box-right,
  .theme-rainbow_infographic .photo-box-right { clear: left; float: left; }
  .theme-paper_cut .info-box,
  .theme-rainbow_infographic .info-box { position: absolute; top: 104pt; right: 28pt; width: 535pt; }
  .theme-paper_cut .loc-body,
  .theme-rainbow_infographic .loc-body { padding-top: 12pt; }
  .theme-paper_cut .photo-label,
  .theme-rainbow_infographic .photo-label { font-size: 8pt; }

</style>
</head>
<body class="theme-{{ $theme['style_type'] ?? 'tropical_pop' }}">

  <!-- ======================================================================= -->
  <!-- SLIDE 1: COVER                                                          -->
  <!-- ======================================================================= -->
  <div class="slide">
    <div class="cover-header">
      <div class="cover-badge">BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR</div>
      <h1 class="cover-title">LAPORAN PENGAWASAN LINGKUNGAN KANTOR</h1>
    </div>

      <!-- Left Metadata Box -->
      <div class="cover-card-left">
        <div class="meta-group">
          <div class="meta-label">Nomor Laporan Resmi</div>
          <div class="meta-value">{{ $report->report_number }}</div>
        </div>

        <div class="meta-group">
          <div class="meta-label">Hari & Tanggal Inspeksi</div>
          <div class="meta-value-sub">{{ $report->formatted_date }}</div>
        </div>

        <div class="meta-group">
          <div class="meta-label">Petugas Pengawas Lapangan</div>
          <div class="meta-value-sub" style="color: #{{ $theme['primary_color'] ?? '1E3A8A' }}; font-size: 14pt;">
            {{ $report->officer_name }}
          </div>
        </div>

        <div class="meta-group" style="margin-bottom: 0;">
          <div class="meta-label">Tema Desain PDF</div>
          <div style="font-size: 10pt; color: #64748b;">
            {{ $report->template->name ?? 'Default' }} • 16:9 PDF Widescreen 16:9
          </div>
        </div>
      </div>

      <!-- Right Officer Photo Frame -->
      <div class="cover-photo-frame">
        @if (!empty($officerPhotoBase64))
          <img src="{{ $officerPhotoBase64 }}" class="cover-photo-img" alt="Foto Petugas" />
        @else
          <div class="cover-photo-placeholder">[ FOTO PETUGAS ]</div>
        @endif
        <div style="font-size: 8.5pt; font-weight: bold; color: #64748b; margin-top: 5pt; text-transform: uppercase;">
          Foto Petugas Pengawas
        </div>
      </div>

    <div class="cover-footer">
      <span>Sistem Informasi Pelaporan Pengawasan Lingkungan Kantor BPSDM • PDF Widescreen 16:9</span>
    </div>
  </div>

  <!-- ======================================================================= -->
  <!-- SLIDES 2 - 6: 5 LOCATIONS                                               -->
  <!-- ======================================================================= -->
  @foreach ($locations as $index => $loc)
    @php
      $locNumber = $index + 1;
      $isLast = ($index === count($locations) - 1);
    @endphp
    <div class="slide {{ $isLast ? 'slide-last' : '' }}">
      <!-- Header -->
      <div class="loc-header">
        <div class="loc-counter">TITIK PEMANTAUAN {{ $locNumber }} DARI 5</div>
        <div class="loc-title">{{ $loc['name'] }}</div>
      </div>

      <!-- Body -->
      <div class="loc-body">
        <!-- 2 Photo Documentation Frames (side by side) -->
        <div class="photos-container">
          <!-- Photo 1 -->
          <div class="photo-box">
            @if (!empty($loc['photo_1_base64']))
              <img src="{{ $loc['photo_1_base64'] }}" class="photo-img" alt="Dokumentasi 1" />
            @else
              <div class="photo-placeholder">[ DOKUMENTASI 1 ]</div>
            @endif
            <div class="photo-label">Dokumentasi 1</div>
          </div>

          <!-- Photo 2 -->
          <div class="photo-box photo-box-right">
            @if (!empty($loc['photo_2_base64']))
              <img src="{{ $loc['photo_2_base64'] }}" class="photo-img" alt="Dokumentasi 2" />
            @else
              <div class="photo-placeholder">[ DOKUMENTASI 2 ]</div>
            @endif
            <div class="photo-label">Dokumentasi 2</div>
          </div>
        </div>

        <!-- Inspection Data & Findings Box -->
        <div class="info-box">
          <div style="margin-bottom: 6pt;">
            <span style="font-size: 9pt; font-weight: bold; color: #64748b; margin-right: 8pt; text-transform: uppercase;">
              Status Kebersihan:
            </span>
            @if ($loc['condition'] === 'clean')
              <span class="badge-clean"><span style="font-family: 'DejaVu Sans', sans-serif;">&#10003;</span> {{ $loc['condition_label'] }}</span>
            @else
              <span class="badge-attention">! {{ $loc['condition_label'] }}</span>
            @endif
          </div>

          <table class="info-table">
            @if ($loc['condition'] === 'clean')
              <tr>
                <td class="info-title">Catatan Pemantauan:</td>
                <td class="info-content">{{ $loc['notes'] ?: 'Area terpantau bersih, rapi, dan terawat dengan baik.' }}</td>
              </tr>
            @else
              <tr>
                <td class="info-title" style="color: #b91c1c;">Permasalahan:</td>
                <td class="info-content" style="color: #991b1b; font-weight: bold;">
                  {{ $loc['problem'] ?: 'Perlu penanganan kebersihan lebih lanjut pada titik ini.' }}
                </td>
              </tr>
              <tr>
                <td class="info-title" style="color: #0284c7;">Solusi Penanganan:</td>
                <td class="info-content">
                  {{ $loc['solution'] ?: 'Telah dikoordinasikan dengan tim kebersihan untuk penanganan segera.' }}
                </td>
              </tr>
            @endif
          </table>
        </div>
      </div>

      <!-- Slide Footer -->
      <div class="loc-footer">
        <div class="footer-left">
          {{ $report->report_number }} • {{ $report->formatted_date }} • Petugas: {{ $report->officer_name }}
        </div>
        <div class="footer-right">
          Slide {{ $locNumber + 1 }} dari 6
        </div>
      </div>
    </div>
  @endforeach

</body>
</html>