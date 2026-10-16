import { Capacitor } from '@capacitor/core';
import { Filesystem, Directory } from '@capacitor/filesystem';
import { Share } from '@capacitor/share';
import api from './api.js';

/**
 * Convert Blob to Base64 string for Capacitor Filesystem
 */
export function blobToBase64(blob) {
  return new Promise((resolve, reject) => {
    const reader = new FileReader();
    reader.onerror = reject;
    reader.onload = () => {
      const dataUrl = reader.result;
      const base64 = dataUrl.split(',')[1];
      resolve(base64);
    };
    reader.readAsDataURL(blob);
  });
}

/**
 * Generate formatted WhatsApp message text with rich details
 */
export function formatWhatsAppMessage(report) {
  const locations = report.report_locations || [];
  const attentionLocations = locations.filter(l => l.condition === 'needs_attention');
  const cleanCount = locations.filter(l => l.condition === 'clean').length;
  const totalCount = locations.length || 5;

  let msg = `*LAPORAN PENGAWASAN LINGKUNGAN KANTOR BPSDM*\n`;
  msg += `━━━━━━━━━━━━━━━━━━━━━━\n`;
  msg += `📋 *No. Laporan:* ${report.report_number}\n`;
  msg += `📅 *Tanggal:* ${report.formatted_date || report.inspection_date}\n`;
  msg += `👤 *Petugas Pengawas:* ${report.officer_name}\n`;
  msg += `📊 *Ringkasan:* `;

  if (attentionLocations.length === 0) {
    msg += `✓ *Bersih & Rapi (${cleanCount}/${totalCount} Titik)*\n`;
    msg += `Semua titik pemantauan dalam kondisi bersih, aman, dan rapi.\n`;
  } else {
    msg += `⚠️ *${attentionLocations.length} Titik Perlu Penanganan*\n\n`;
    msg += `*Rincian Temuan & Solusi:*\n`;
    attentionLocations.forEach((loc, idx) => {
      const locName = loc.location?.name || `Lokasi ${idx + 1}`;
      msg += `${idx + 1}. *${locName}*\n`;
      if (loc.problem) msg += `   • *Masalah:* ${loc.problem}\n`;
      if (loc.solution) msg += `   • *Solusi:* ${loc.solution}\n`;
    });
  }

  const pdfUrl = `${api.defaults.baseURL}/reports/${report.id}/pdf`;
  msg += `\n📄 *Unduh Dokumen Laporan (PDF 16:9):*\n${pdfUrl}\n`;
  msg += `━━━━━━━━━━━━━━━━━━━━━━\n`;
  msg += `_Sistem Pelaporan Pengawasan Lingkungan BPSDM_`;

  return msg;
}

/**
 * Share PDF file natively via Capacitor Share or Web Share API
 */
export async function sharePdfDocument(report) {
  const cleanNum = (report.report_number || `LAP-${report.id}`).replace(/[^A-Za-z0-9_\-]/g, '_');
  const fileName = `Laporan_${cleanNum}.pdf`;
  const title = `Laporan ${report.report_number}`;
  const text = `${title} • ${report.formatted_date || report.inspection_date} • Petugas: ${report.officer_name}`;
  const pdfUrl = `${api.defaults.baseURL}/reports/${report.id}/pdf`;

  // 1. Native Android / iOS via Capacitor
  if (Capacitor.isNativePlatform()) {
    try {
      // Fetch binary PDF blob
      const res = await api.get(`/reports/${report.id}/pdf`, { responseType: 'blob' });
      const base64Data = await blobToBase64(res.data);

      // Save to temporary cache directory
      await Filesystem.writeFile({
        path: fileName,
        data: base64Data,
        directory: Directory.Cache,
      });

      // Retrieve URI for sharing
      const fileResult = await Filesystem.getUri({
        path: fileName,
        directory: Directory.Cache,
      });

      // Share via native intent sheet (WhatsApp, Gmail, Telegram, Bluetooth, etc.)
      await Share.share({
        title,
        text,
        files: [fileResult.uri],
        dialogTitle: 'Bagikan Laporan (PDF 16:9)',
      });
      return { success: true, method: 'native-file' };
    } catch (err) {
      if (err.message?.includes('cancelled') || err.message?.includes('Cancel') || err.name === 'AbortError') {
        return { success: true, method: 'cancelled' };
      }
      // Fallback to URL share if file write fails
      await Share.share({
        title,
        text,
        url: pdfUrl,
        dialogTitle: 'Bagikan Tautan Laporan',
      });
      return { success: true, method: 'native-url' };
    }
  }

  // 2. Web Browser environment
  try {
    const res = await api.get(`/reports/${report.id}/pdf`, { responseType: 'blob' });
    const file = new File([res.data], fileName, { type: 'application/pdf' });

    // Check if Web Share API supports file sharing
    if (navigator.canShare && navigator.canShare({ files: [file] })) {
      await navigator.share({
        title,
        text,
        files: [file],
      });
      return { success: true, method: 'web-file' };
    } else if (navigator.share) {
      // Fallback to text & URL share
      await navigator.share({
        title,
        text,
        url: pdfUrl,
      });
      return { success: true, method: 'web-url' };
    } else {
      // Fallback open PDF in new window
      window.open(pdfUrl, '_blank');
      return { success: true, method: 'web-open' };
    }
  } catch (err) {
    if (err.name === 'AbortError' || err.message?.includes('cancelled')) {
      return { success: true, method: 'cancelled' };
    }
    // Ultimate fallback
    window.open(pdfUrl, '_blank');
    return { success: true, method: 'web-open' };
  }
}

/**
 * Open WhatsApp with prefilled formatted message
 */
export function openWhatsApp(report) {
  const text = formatWhatsAppMessage(report);
  const waUrl = `https://wa.me/?text=${encodeURIComponent(text)}`;
  window.open(waUrl, '_blank');
}

