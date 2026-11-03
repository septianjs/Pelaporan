/**
 * Layanan Identitas Perangkat (Device Account Tanpa Login)
 *
 * Memberikan identitas unik dan persisten untuk setiap perangkat HP
 * sehingga data laporan, antrian offline, dan template tidak tertukar atau
 * menimpa data dari HP lain, tanpa perlu proses registrasi / login.
 */

const STORAGE_KEY_DEVICE_ID = 'decora_device_id';
const STORAGE_KEY_OFFICER_NAME = 'decora_officer_name';
const STORAGE_KEY_DEVICE_LABEL = 'decora_device_label';
const STORAGE_KEY_DEVICE_TOKEN = 'decora_device_token';
let registrationPromise = null;

/**
 * Buat ID perangkat acak unik jika belum ada.
 * Contoh format: DEV-M1B8-9A4F
 */
function generateRandomDeviceId() {
  const ts = Date.now().toString(36).slice(-4).toUpperCase();
  const rand = Math.random().toString(36).slice(2, 6).toUpperCase();
  return `DEV-${ts}-${rand}`;
}

/**
 * Dapatkan ID perangkat unik untuk HP ini.
 */
export function getDeviceId() {
  try {
    let id = localStorage.getItem(STORAGE_KEY_DEVICE_ID);
    if (!id || typeof id !== 'string' || id.trim() === '') {
      id = generateRandomDeviceId();
      localStorage.setItem(STORAGE_KEY_DEVICE_ID, id);
    }
    return id.trim();
  } catch (err) {
    console.warn('Gagal mengakses localStorage untuk Device ID:', err);
    return 'DEV-LOCAL-ANON';
  }
}

/**
 * Dapatkan kode ringkas untuk tampilan UI (mis. #9A4F).
 */
export function getDeviceShortCode() {
  const full = getDeviceId();
  const parts = full.split('-');
  return '#' + (parts[parts.length - 1] || 'DEC');
}

/**
 * Nama default petugas yang tersimpan di HP ini.
 */
export function getDefaultOfficerName() {
  try {
    return localStorage.getItem(STORAGE_KEY_OFFICER_NAME) || '';
  } catch {
    return '';
  }
}

/**
 * Simpan nama default petugas agar otomatis terisi saat buat laporan baru.
 */
export function setDefaultOfficerName(name) {
  try {
    if (name && typeof name === 'string') {
      localStorage.setItem(STORAGE_KEY_OFFICER_NAME, name.trim());
    }
  } catch (err) {
    console.warn('Gagal menyimpan nama petugas:', err);
  }
}

/**
 * Label/nama perangkat (opsional, mis. "HP Lapangan 1").
 */
export function getDeviceLabel() {
  try {
    return localStorage.getItem(STORAGE_KEY_DEVICE_LABEL) || '';
  } catch {
    return '';
  }
}

export function setDeviceLabel(label) {
  try {
    if (label) {
      localStorage.setItem(STORAGE_KEY_DEVICE_LABEL, label.trim());
    } else {
      localStorage.removeItem(STORAGE_KEY_DEVICE_LABEL);
    }
  } catch (err) {
    console.warn('Gagal menyimpan label perangkat:', err);
  }
}

export function getDeviceToken() {
  try {
    return localStorage.getItem(STORAGE_KEY_DEVICE_TOKEN) || '';
  } catch {
    return '';
  }
}

function clearDeviceToken() {
  try {
    localStorage.removeItem(STORAGE_KEY_DEVICE_TOKEN);
  } catch {
    // Storage may be unavailable in a restricted WebView.
  }
}

export function forgetDeviceToken() {
  clearDeviceToken();
}

export async function ensureDeviceRegistered(apiBaseUrl, force = false) {
  if (!apiBaseUrl) return '';
  if (!force && getDeviceToken()) return getDeviceToken();
  if (registrationPromise) return registrationPromise;

  registrationPromise = (async () => {
    const cleanBase = apiBaseUrl.replace(/\/+$/, '');
    const url = new URL(`${cleanBase}/devices/register`, window.location.origin).href;
    const response = await fetch(url, {
      method: 'POST',
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        legacy_device_id: getDeviceId(),
        label: getDeviceLabel(),
      }),
    });

    const data = await response.json().catch(() => null);
    if (!response.ok || !data?.token) {
      throw new Error(data?.message || 'Registrasi perangkat gagal.');
    }

    localStorage.setItem(STORAGE_KEY_DEVICE_TOKEN, data.token);
    return data.token;
  })().finally(() => {
    registrationPromise = null;
  });

  return registrationPromise;
}
