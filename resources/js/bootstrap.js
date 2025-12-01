import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// [PERBAIKAN] Set Base URL untuk semua request Axios
window.axios.defaults.baseURL = 'https://geocitra.com/e-daily-report';
