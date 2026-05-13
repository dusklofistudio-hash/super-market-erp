import axios from 'axios';
import * as bootstrap from 'bootstrap';
import jQuery from 'jquery';

window.axios = axios;
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';

const token = document.head.querySelector('meta[name="csrf-token"]');
if (token) {
    window.axios.defaults.headers.common['X-CSRF-TOKEN'] = token.content;
}

window.bootstrap = bootstrap;
window.$ = window.jQuery = jQuery;
