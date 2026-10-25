import axios from 'axios';

// Default base URL determination:
// If running in browser, relative '/api' works with Laravel.
// If running in Capacitor native Android, default to stored API base URL or window.location.origin
const getBaseUrl = () => {
    const savedUrl = localStorage.getItem('bpsdm_api_base_url');
    if (savedUrl) {
        return savedUrl;
    }
    // Alamat server produksi/uji, ditetapkan saat build (lihat .env.production).
    // Contoh: VITE_API_BASE_URL=https://domain-anda.go.id/api
    const envUrl = import.meta.env.VITE_API_BASE_URL;
    if (envUrl) {
        return envUrl.replace(/\/+$/, '');
    }
    // Check if on capacitor or file/localhost
    if (window.location.protocol === 'file:' || window.location.hostname === 'localhost' && window.location.port === '') {
        // Hanya untuk emulator Android (10.0.2.2 = PC pengembang). Tidak berlaku di HP sungguhan.
        return 'http://10.0.2.2:8000/api';
    }
    return '/api';
};

const api = axios.create({
    baseURL: getBaseUrl(),
    headers: {
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
    },
    timeout: 120000, // upload banyak foto lewat data seluler butuh waktu lebih lama
});

export const setCustomApiUrl = (url) => {
    if (url) {
        localStorage.setItem('bpsdm_api_base_url', url);
        api.defaults.baseURL = url;
    } else {
        localStorage.removeItem('bpsdm_api_base_url');
        api.defaults.baseURL = getBaseUrl();
    }
};

export const getApiUrl = () => api.defaults.baseURL;

export default api;

