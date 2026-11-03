
import axios from 'axios';
import { Capacitor } from '@capacitor/core';
import { ensureDeviceRegistered, forgetDeviceToken } from './deviceId.js';

const STORAGE_KEY = 'bpsdm_api_base_url';

const getBaseUrl = () => {
    // 1. URL dari Pengaturan (bisa diubah tanpa build ulang)
    const savedUrl = localStorage.getItem(STORAGE_KEY);
    if (savedUrl) {
        return savedUrl.replace(/\/+$/, '');
    }

    // 2. Aplikasi Android (Capacitor)
    if (Capacitor.isNativePlatform()) {
        const envUrl = import.meta.env.VITE_API_BASE_URL;
        if (envUrl) return envUrl.replace(/\/+$/, '');
        return '';
    }

    // 3. Browser: pakai /api di server yang sama
    return '/api';
};

const resolvedBase = getBaseUrl();


const api = axios.create({
    baseURL: resolvedBase,
    headers: {
        Accept: 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'ngrok-skip-browser-warning': 'true',
    },
    timeout: 120000,
});

// Otomatis kirim identitas perangkat (Device ID) pada setiap permintaan
api.interceptors.request.use(async (config) => {
    if (config.url?.endsWith('/devices/register')) return config;
    const token = await ensureDeviceRegistered(config.baseURL || api.defaults.baseURL, false);
    config.headers.Authorization = `Bearer ${token}`;
    return config;
});

api.interceptors.response.use(
    (response) => response,
    async (error) => {
        const config = error.config;
        if (error.response?.status === 401 && config && !config._deviceRegistrationRetried) {
            config._deviceRegistrationRetried = true;
            forgetDeviceToken();
            const token = await ensureDeviceRegistered(config.baseURL || api.defaults.baseURL, true);
            config.headers = config.headers || {};
            config.headers.Authorization = `Bearer ${token}`;
            return api.request(config);
        }
        return Promise.reject(error);
    }
);


export const isApiUnconfigured = () => {
    return (
        Capacitor.isNativePlatform() &&
        !import.meta.env.VITE_API_BASE_URL &&
        !localStorage.getItem(STORAGE_KEY)
    );
};

export const setCustomApiUrl = (url) => {
    if (url) {
        const cleaned = url.replace(/\/+$/, '');
        localStorage.setItem(STORAGE_KEY, cleaned);
        api.defaults.baseURL = cleaned;
    } else {
        localStorage.removeItem(STORAGE_KEY);
        api.defaults.baseURL = getBaseUrl();
    }
};

export const getApiUrl = () => api.defaults.baseURL;

export const resolveAssetUrl = (path) => {
    if (!path) return '';
    // Strip domain if present (e.g. from an old absolute url or ngrok url)
    let clean = path.replace(/^https?:\/\/[^/]+/i, '');
    // If path starts with /api/storage/, normalize to /storage/
    clean = clean.replace(/^\/api\/storage\//i, '/storage/');
    if (!clean.startsWith('/')) clean = '/' + clean;

    const base = api.defaults.baseURL || '';
    if (/^https?:\/\//i.test(base)) {
        return base.replace(/\/api\/?$/, '') + clean;
    }
    return clean;
};

export default api;