<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Laporan Pengawasan Lingkungan Kantor BPSDM</title>
</head>
<body style="margin: 0; padding: 0; background-color: #f1f5f9; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: #1e293b; -webkit-text-size-adjust: 100%;">
  <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f1f5f9; padding: 24px 12px;">
    <tr>
      <td align="center">
        <!-- Main Card Container -->
        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 600px; background-color: #ffffff; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1), 0 2px 4px -2px rgba(0, 0, 0, 0.1); border: 1px solid #e2e8f0;">
          
          <!-- Header Banner -->
          <tr>
            <td style="background: linear-gradient(135deg, #1e3a8a 0%, #2563eb 100%); padding: 28px 24px; text-align: left; color: #ffffff;">
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                <tr>
                  <td>
                    <div style="font-size: 11px; font-weight: 800; letter-spacing: 0.12em; color: #fde047; text-transform: uppercase; margin-bottom: 4px;">
                      BPSDM PROVINSI • PENGAWASAN LINGKUNGAN KANTOR
                    </div>
                    <h1 style="margin: 0; font-size: 20px; font-weight: 800; line-height: 1.3; color: #ffffff;">
                      Laporan Pengawasan Lingkungan Kantor
                    </h1>
                    <div style="margin-top: 6px; font-size: 12px; color: #e0e7ff;">
                      Dokumen Resmi Pemantauan Kebersihan &amp; Kerapian Lingkungan (Format 16:9 Widescreen)
                    </div>
                  </td>
                </tr>
              </table>
            </td>
          </tr>

          <!-- Body Content -->
          <tr>
            <td style="padding: 24px;">
              <p style="margin: 0 0 16px 0; font-size: 14px; line-height: 1.6; color: #334155;">
                Yth. <strong>Bapak/Ibu Pimpinan &amp; Rekan Kerja</strong>,
              </p>
              <p style="margin: 0 0 20px 0; font-size: 14px; line-height: 1.6; color: #475569;">
                Telah diselesaikan kegiatan pengawasan dan pemantauan kondisi lingkungan kantor BPSDM. Berikut ringkasan laporan hasil pemeriksaan:
              </p>

              <!-- Report Meta Table -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; margin-bottom: 20px; overflow: hidden;">
                <tr>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; width: 40%; font-size: 12px; font-weight: 600; color: #64748b;">
                    Nomor Laporan
                  </td>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #1d4ed8; font-family: monospace;">
                    {{ $report->report_number }}
                  </td>
                </tr>
                <tr>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #64748b;">
                    Hari &amp; Tanggal
                  </td>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 600; color: #1e293b;">
                    {{ $report->formatted_date ?: $report->inspection_date }}
                  </td>
                </tr>
                <tr>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 12px; font-weight: 600; color: #64748b;">
                    Petugas Pengawas
                  </td>
                  <td style="padding: 12px 16px; border-bottom: 1px solid #e2e8f0; font-size: 13px; font-weight: 700; color: #0f172a;">
                    {{ $report->officer_name }}
                  </td>
                </tr>
                <tr>
                  <td style="padding: 12px 16px; font-size: 12px; font-weight: 600; color: #64748b;">
                    Template Dokumen
                  </td>
                  <td style="padding: 12px 16px; font-size: 13px; font-weight: 600; color: #4338ca;">
                    {{ $report->template?->name ?: 'Standar 16:9 Widescreen' }}
                  </td>
                </tr>
              </table>

              @if(!empty($notes))
              <!-- Custom User Notes -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #eff6ff; border-left: 4px solid #3b82f6; border-radius: 8px; margin-bottom: 20px;">
                <tr>
                  <td style="padding: 12px 16px;">
                    <div style="font-size: 11px; font-weight: 700; color: #1d4ed8; text-transform: uppercase; margin-bottom: 4px;">
                      Catatan Petugas Pengirim:
                    </div>
                    <div style="font-size: 13px; color: #1e40af; line-height: 1.5;">
                      {{ $notes }}
                    </div>
                  </td>
                </tr>
              </table>
              @endif

              <!-- Location Breakdown -->
              <h2 style="font-size: 14px; font-weight: 700; color: #0f172a; margin: 0 0 12px 0; text-transform: uppercase; letter-spacing: 0.05em;">
                Ringkasan 5 Titik Pemantauan
              </h2>

              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="border-collapse: collapse; width: 100%; margin-bottom: 24px; font-size: 12px;">
                <thead>
                  <tr style="background-color: #f1f5f9; text-align: left;">
                    <th style="padding: 10px 12px; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #475569; width: 8%;">No</th>
                    <th style="padding: 10px 12px; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #475569; width: 42%;">Titik Lokasi</th>
                    <th style="padding: 10px 12px; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #475569; width: 25%;">Status</th>
                    <th style="padding: 10px 12px; border-bottom: 2px solid #cbd5e1; font-weight: 700; color: #475569; width: 25%;">Keterangan</th>
                  </tr>
                </thead>
                <tbody>
                  @php $index = 1; @endphp
                  @foreach($report->reportLocations as $rLoc)
                  <tr style="border-bottom: 1px solid #e2e8f0;">
                    <td style="padding: 10px 12px; font-weight: 600; color: #64748b; text-align: center;">
                      {{ $index++ }}
                    </td>
                    <td style="padding: 10px 12px; font-weight: 600; color: #1e293b;">
                      {{ $rLoc->location?->name ?: 'Lokasi #' . $rLoc->location_id }}
                    </td>
                    <td style="padding: 10px 12px;">
                      @if($rLoc->condition === 'clean')
                        <span style="display: inline-block; padding: 3px 8px; font-size: 10px; font-weight: 700; border-radius: 9999px; background-color: #dcfce7; color: #15803d;">
                          ✓ Bersih
                        </span>
                      @else
                        <span style="display: inline-block; padding: 3px 8px; font-size: 10px; font-weight: 700; border-radius: 9999px; background-color: #fee2e2; color: #b91c1c;">
                          ⚠ Perlu Penanganan
                        </span>
                      @endif
                    </td>
                    <td style="padding: 10px 12px; color: #475569; font-size: 11px;">
                      @if($rLoc->condition === 'clean')
                        {{ $rLoc->notes ?: 'Kondisi terpantau bersih dan terawat.' }}
                      @else
                        <strong>Masalah:</strong> {{ $rLoc->problem ?: '-' }}<br>
                        <strong>Solusi:</strong> {{ $rLoc->solution ?: '-' }}
                      @endif
                    </td>
                  </tr>
                  @endforeach
                </tbody>
              </table>

              <!-- Attachment Notification Banner -->
              <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color: #fdf2f8; border: 1px solid #fbcfe8; border-radius: 12px; margin-bottom: 20px;">
                <tr>
                  <td style="padding: 16px;">
                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0">
                      <tr>
                        <td width="36" valign="top" style="padding-right: 12px;">
                          <div style="width: 32px; height: 32px; background-color: #e11d48; border-radius: 8px; text-align: center; line-height: 32px; color: #ffffff; font-weight: bold; font-size: 14px;">
                            PDF
                          </div>
                        </td>
                        <td>
                          <div style="font-size: 13px; font-weight: 700; color: #9f1239;">
                            Dokumen PDF 16:9 Widescreen Terlampir
                          </div>
                          <div style="font-size: 12px; color: #881337; margin-top: 2px; line-height: 1.4;">
                            Laporan lengkap beserta foto dokumentasi telah dilampirkan dalam format PDF 16:9 Widescreen untuk presentasi dan pengarsipan.
                          </div>
                        </td>
                      </tr>
                    </table>
                  </td>
                </tr>
              </table>

              <p style="margin: 0; font-size: 13px; color: #64748b; line-height: 1.6;">
                Demikian laporan ini kami sampaikan. Atas perhatian dan kerjasamanya kami ucapkan terima kasih.
              </p>
            </td>
          </tr>

          <!-- Footer -->
          <tr>
            <td style="background-color: #f8fafc; border-top: 1px solid #e2e8f0; padding: 20px 24px; text-align: center;">
              <p style="margin: 0 0 4px 0; font-size: 11px; font-weight: 700; color: #475569; text-transform: uppercase; letter-spacing: 0.05em;">
                BADAN PENGEMBANGAN SUMBER DAYA MANUSIA (BPSDM)
              </p>
              <p style="margin: 0; font-size: 11px; color: #94a3b8;">
                Sistem Pelaporan Pengawasan Lingkungan Kantor &bull; Otomatisasi Presentasi 16:9
              </p>
            </td>
          </tr>

        </table>
      </td>
    </tr>
  </table>
</body>
</html>

