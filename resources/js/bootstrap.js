// resources/js/bootstrap.js
import axios from 'axios';
window.axios = axios;

window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

// Lee el meta y lo pone en cada request (POST/PATCH/DELETE)
const token = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
if (token) {
  window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token;
}
