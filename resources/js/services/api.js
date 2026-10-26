
import axios from 'axios';
import { Capacitor } from '@capacitor/core';

const STORAGE_KEY = 'bpsdm_api_base_url';

const getBaseUrl = () => {
    // 1. URL dari developer saat build APK
    const envUrl = import.meta.env.VITE_API_BASE_URL;
    if (envUrl) {
        return envUrl.replace(/\/+$/, '');
    }

    // 2. URL yang disimpan melalui Pengaturan
    const savedUrl = localStorage.getItem(STORAGE_KEY);
    if (savedUrl) {
        return savedUrl.replace(/\/+$/, '');
    }

    // 3. Fallback untuk emulator Android
    if (Capacitor.isNativePlatform()) {
        return 'http://10.0.2.2:8000/api';
    }

    // 4. Browser biasa
    if (
        window.location.protocol === 'file:' ||
        (window.location.hostname === 'localhost' &&
            window.location.port === '')
    ) {
        return 'http://10.0.2.2:8000/api';
    }

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
