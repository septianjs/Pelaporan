<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="utf-8">
<title>{{ $report->report_number }} - Laporan Pengawasan Lingkungan BPSDM</title>
<style>
{!! $pdfCss ?? '' !!}
</style>
</head>
<body class="template-{{ str_pad((string) $theme['number'], 2, '0', STR_PAD_LEFT) }}">

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
          <div class="meta-value-sub" style="color: #{{ $theme['primary_color'] }}; font-size: 14pt;">
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