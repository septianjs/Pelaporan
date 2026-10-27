
import axios from 'axios';
import { Capacitor } from '@capacitor/core';

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
        return 'http://10.0.2.2:8000/api';
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

export default api;
