import axios from 'axios';
import { Capacitor } from '@capacitor/core';

/**
 * Deteksi alamat API server.
 *
 * Urutan prioritas:
 * 1. URL yang disimpan user di localStorage  (runtime override dari Pengaturan)
 * 2. VITE_API_BASE_URL dari env build         (.env.production saat npm run build:cap)
 * 3. Deteksi otomatis berdasarkan platform:
 *    - Capacitor native (HP asli)  -> WAJIB sudah di-set via (1) atau (2)
 *    - Emulator Android             -> http://10.0.2.2:8000/api
 *    - Browser biasa                -> /api (relatif, ditangani Laravel)
 */
const STORAGE_KEY = 'bpsdm_api_base_url';

const getBaseUrl = () => {
    // 1. Runtime override dari localStorage
    const savedUrl = localStorage.getItem(STORAGE_KEY);
    if (savedUrl) {
        return savedUrl;
    }

    // 2. Build-time env (di-set di .env.production sebelum npm run build:cap)
    const envUrl = import.meta.env.VITE_API_BASE_URL;
    if (envUrl) {
        return envUrl.replace(/\/+$/, '');
    }

    // 3. Platform-based fallback
    if (Capacitor.isNativePlatform()) {
        // Fallback IP komputer saat di build (bisa diubah lewat tombol Atur Server)
        return 'http://10.50.83.224:8000/api';
    }

    // Browser biasa (dev server / production web)
    if (window.location.protocol === 'file:' ||
        (window.location.hostname === 'localhost' && window.location.port === '')) {
        // Emulator Android (WebView lokal tanpa port)
        return 'http://10.0.2.2:8000/api';
    }

    return '/api';
};

const resolvedBase = getBaseUrl();

const api = axios.create({
    baseURL: resolvedBase || 'http://localhost:8000/api',
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    timeout: 120000,
});

/**
 * Apakah API base URL belum dikonfigurasi?
 * True jika aplikasi berjalan di HP asli tapi belum ada URL server.
 */
export const isApiUnconfigured = () => {
    return Capacitor.isNativePlatform() && !localStorage.getItem(STORAGE_KEY) && !import.meta.env.VITE_API_BASE_URL;
};

/**
 * Simpan URL API kustom (dari halaman Pengaturan / dialog konfigurasi).
 */
export const setCustomApiUrl = (url) => {
    if (url) {
        const cleaned = url.replace(/\/+$/, '');
        localStorage.setItem(STORAGE_KEY, cleaned);
        api.defaults.baseURL = cleaned;
    } else {
        localStorage.removeItem(STORAGE_KEY);
        const fresh = getBaseUrl();
        api.defaults.baseURL = fresh || 'http://localhost:8000/api';
    }
};

export const getApiUrl = () => api.defaults.baseURL;

export default api;