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
  /* PDF theme variants: each template changes its composition and decorative language. */
  .cover-header { position: relative; }
  .cover-header:after { content: '✦  ✦  ✦'; position: absolute; right: 42pt; top: 15pt; font-size: 24pt; color: #{{ $theme['accent_color'] ?? '0284C7' }}; }
  .loc-header { position: relative; }
  .loc-header:after { content: '◆  ◆  ◆'; float: right; color: #{{ $theme['accent_color'] ?? '0284C7' }}; font-size: 12pt; margin-top: -4pt; }
  .theme-tropical_pop .cover-header { height: 92pt; padding: 18pt 40pt; border-bottom-width: 10pt; }
  .theme-tropical_pop .cover-card-left { left: 15pt; top: 26pt; border: 3pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; border-radius: 24pt; }
  .theme-tropical_pop .cover-photo-frame { right: 26pt; top: 136pt; border-radius: 22pt; border-width: 5pt; }
  .theme-tropical_pop .loc-header { border-bottom-width: 9pt; }
  .theme-tropical_pop .photo-box, .theme-tropical_pop .info-box { border-radius: 18pt; border-width: 3pt; }
  .theme-batik_nusantara .cover-header { height: 82pt; padding: 20pt 42pt; border-bottom: 8pt double #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-batik_nusantara .cover-card-left { left: 10pt; top: 32pt; border-radius: 2pt; border: 3pt double #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-batik_nusantara .cover-photo-frame { right: 30pt; top: 146pt; border-radius: 2pt; border: 4pt double #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-batik_nusantara .loc-header { border-bottom: 8pt double #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-batik_nusantara .photo-box, .theme-batik_nusantara .info-box { border-radius: 2pt; border: 2pt solid #{{ $theme['primary_color'] ?? '064E3B' }}; }
  .theme-comic_pop .cover-header { height: 98pt; padding: 20pt 36pt; border-bottom: 10pt solid #{{ $theme['accent_color'] ?? 'F97316' }}; }
  .theme-comic_pop .cover-title { font-size: 27pt; }
  .theme-comic_pop .cover-card-left { left: 0; top: 18pt; border: 4pt solid #{{ $theme['primary_color'] ?? '115E59' }}; border-radius: 0; box-shadow: 7pt 7pt 0 #{{ $theme['accent_color'] ?? 'F97316' }}; }
  .theme-comic_pop .cover-photo-frame { right: 34pt; top: 142pt; border-radius: 0; border: 4pt solid #{{ $theme['primary_color'] ?? '115E59' }}; box-shadow: 7pt 7pt 0 #{{ $theme['accent_color'] ?? 'F97316' }}; }
  .theme-comic_pop .loc-header { border-bottom-width: 10pt; }
  .theme-comic_pop .photo-box, .theme-comic_pop .info-box { border-radius: 0; border: 3pt solid #{{ $theme['primary_color'] ?? '115E59' }}; box-shadow: 4pt 4pt 0 #{{ $theme['accent_color'] ?? 'F97316' }}; }
  .theme-scrapbook .cover-header { height: 76pt; padding: 16pt 46pt; border-bottom: 3pt dashed #{{ $theme['accent_color'] ?? 'FBBF24' }}; }
  .theme-scrapbook .cover-card-left { left: 35pt; top: 38pt; border: 2pt dashed #{{ $theme['accent_color'] ?? 'FBBF24' }}; }
  .theme-scrapbook .cover-photo-frame { right: 40pt; top: 142pt; border: 3pt dashed #{{ $theme['accent_color'] ?? 'FBBF24' }}; border-radius: 4pt; }
  .theme-scrapbook .loc-header { border-bottom: 3pt dashed #{{ $theme['accent_color'] ?? 'FBBF24' }}; }
  .theme-scrapbook .photo-box, .theme-scrapbook .info-box { border: 2pt dashed #{{ $theme['accent_color'] ?? 'FBBF24' }}; border-radius: 5pt; }
  .theme-memphis .cover-header { height: 104pt; padding: 24pt 40pt; border-bottom: 12pt solid #{{ $theme['accent_color'] ?? 'F43F5E' }}; }
  .theme-memphis .cover-card-left { left: 0; top: 16pt; border-radius: 24pt 0 24pt 0; border: 3pt solid #{{ $theme['secondary_color'] ?? '7C3AED' }}; }
  .theme-memphis .cover-photo-frame { right: 42pt; top: 150pt; border-radius: 0 24pt 0 24pt; border: 4pt solid #{{ $theme['accent_color'] ?? 'F43F5E' }}; }
  .theme-memphis .loc-header { border-bottom-width: 12pt; }
  .theme-memphis .photo-box:first-child { border-radius: 18pt 0 18pt 0; }
  .theme-memphis .photo-box-right { border-radius: 0 18pt 0 18pt; }
  .theme-memphis .info-box { border-radius: 20pt 0 20pt 0; border: 3pt solid #{{ $theme['secondary_color'] ?? '7C3AED' }}; }
  .theme-festival .cover-header { height: 90pt; padding: 20pt 50pt; border-bottom: 7pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-festival .cover-card-left { left: 24pt; top: 22pt; border-left: 9pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; border-radius: 4pt 16pt 16pt 4pt; }
  .theme-festival .cover-photo-frame { right: 18pt; top: 136pt; border: 5pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; border-radius: 18pt; }
  .theme-festival .loc-header { border-bottom: 7pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-festival .photo-box, .theme-festival .info-box { border-radius: 14pt; border: 2pt solid #{{ $theme['accent_color'] ?? 'F59E0B' }}; }
  .theme-retro_groovy .cover-header { height: 86pt; padding: 18pt 42pt; border-bottom: 8pt solid #{{ $theme['accent_color'] ?? 'FACC15' }}; }
  .theme-retro_groovy .cover-title { letter-spacing: 1.5pt; }
  .theme-retro_groovy .cover-card-left { left: 18pt; top: 30pt; border-radius: 28pt; border: 3pt solid #{{ $theme['secondary_color'] ?? 'C026D3' }}; }
  .theme-retro_groovy .cover-photo-frame { right: 30pt; top: 148pt; border-radius: 28pt; border: 4pt solid #{{ $theme['accent_color'] ?? 'FACC15' }}; }
  .theme-retro_groovy .loc-header { border-bottom-width: 8pt; }
  .theme-retro_groovy .photo-box, .theme-retro_groovy .info-box { border-radius: 22pt; border: 2pt solid #{{ $theme['secondary_color'] ?? 'C026D3' }}; }
  .theme-rainbow_infographic .cover-header { height: 78pt; padding: 18pt 40pt; border-bottom: 6pt solid #{{ $theme['accent_color'] ?? 'D8C79C' }}; }
  .theme-rainbow_infographic .cover-card-left { left: 8pt; top: 22pt; border-top: 8pt solid #{{ $theme['secondary_color'] ?? '1C362A' }}; border-radius: 0 0 12pt 12pt; }
  .theme-rainbow_infographic .cover-photo-frame { right: 22pt; top: 140pt; border: 4pt solid #{{ $theme['secondary_color'] ?? '1C362A' }}; border-radius: 10pt; }
  .theme-rainbow_infographic .loc-header { border-bottom-width: 6pt; }
  .theme-rainbow_infographic .photo-box, .theme-rainbow_infographic .info-box { border-radius: 8pt; border: 2pt solid #{{ $theme['secondary_color'] ?? '1C362A' }}; }
  .theme-paper_cut .cover-header { height: 96pt; padding: 22pt 40pt; border-bottom: 10pt solid #{{ $theme['accent_color'] ?? 'EA580C' }}; }
  .theme-paper_cut .cover-card-left { left: 32pt; top: 18pt; border: 0; border-left: 10pt solid #{{ $theme['secondary_color'] ?? '0284C7' }}; border-radius: 0; box-shadow: 5pt 5pt 0 #{{ $theme['accent_color'] ?? 'EA580C' }}; }
  .theme-paper_cut .cover-photo-frame { right: 20pt; top: 142pt; border: 0; border-bottom: 8pt solid #{{ $theme['accent_color'] ?? 'EA580C' }}; border-radius: 0; box-shadow: 5pt 5pt 0 #{{ $theme['secondary_color'] ?? '0284C7' }}; }
  .theme-paper_cut .loc-header { border-bottom-width: 10pt; }
  .theme-paper_cut .photo-box, .theme-paper_cut .info-box { border-radius: 0; border: 0; border-bottom: 6pt solid #{{ $theme['accent_color'] ?? 'EA580C' }}; box-shadow: 3pt 3pt 0 #{{ $theme['secondary_color'] ?? '0284C7' }}; }
  .theme-botanical .cover-header { height: 82pt; padding: 20pt 44pt; border-bottom: 5pt solid #{{ $theme['accent_color'] ?? '84CC16' }}; }
  .theme-botanical .cover-card-left { left: 18pt; top: 26pt; border: 2pt solid #{{ $theme['secondary_color'] ?? '334155' }}; border-radius: 20pt 4pt 20pt 4pt; }
  .theme-botanical .cover-photo-frame { right: 28pt; top: 140pt; border: 3pt solid #{{ $theme['accent_color'] ?? '84CC16' }}; border-radius: 20pt 4pt 20pt 4pt; }
  .theme-botanical .loc-header { border-bottom-width: 5pt; }
  .theme-botanical .photo-box, .theme-botanical .info-box { border-radius: 16pt 4pt 16pt 4pt; border: 2pt solid #{{ $theme['secondary_color'] ?? '334155' }}; }
  /* Alternate composition: stacked documentary photos on the left, findings panel on the right. */
  .theme-paper_cut .photos-container, .theme-rainbow_infographic .photos-container { width: 330pt; margin-bottom: 0; }
  .theme-paper_cut .photo-box, .theme-rainbow_infographic .photo-box { width: 315pt; float: left; padding: 5pt; }
  .theme-paper_cut .photo-img, .theme-rainbow_infographic .photo-img { width: 300pt; height: 165pt; }
  .theme-paper_cut .photo-placeholder, .theme-rainbow_infographic .photo-placeholder { width: 300pt; height: 165pt; line-height: 165pt; }
  .theme-paper_cut .photo-box-right, .theme-rainbow_infographic .photo-box-right { clear: left; float: left; margin-top: 8pt; }
  .theme-paper_cut .info-box, .theme-rainbow_infographic .info-box { position: absolute; top: 100pt; right: 28pt; width: 535pt; }
  .theme-batik_nusantara .photos-container { padding-top: 4pt; }
  .theme-batik_nusantara .photo-box:first-child { margin-left: 4pt; }
  .theme-batik_nusantara .photo-box-right { margin-right: 4pt; }
  .theme-festival .info-box { border-top-width: 7pt; }
  .theme-memphis .info-box { border-right-width: 8pt; }
  .theme-retro_groovy .loc-title { letter-spacing: 1pt; }
  .theme-botanical .info-box { border-left-width: 8pt; }
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