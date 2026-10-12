import axios from 'axios';

// Default base URL determination:
// If running in browser, relative '/api' works with Laravel.
// If running in Capacitor native Android, default to stored API base URL or window.location.origin
const getBaseUrl = () => {
    const savedUrl = localStorage.getItem('bpsdm_api_base_url');
    if (savedUrl) {
        return savedUrl;
    }
    // Check if on capacitor or file/localhost
    if (window.location.protocol === 'file:' || window.location.hostname === 'localhost' && window.location.port === '') {
        // Android WebView default host loopback to PC:
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
    timeout: 30000,
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

