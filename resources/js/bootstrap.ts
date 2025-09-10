import axios from 'axios';

// Only set window.axios if we're in a browser environment
if (typeof window !== 'undefined') {
    window.axios = axios;
    window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
}
