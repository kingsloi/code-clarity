import '@popperjs/core/dist/umd/popper';
import 'jquery/src/jquery.js'
import 'bootstrap/dist/js/bootstrap.min.js'

import "./assets/style.scss";

import { createApp } from 'vue'
import App from './App.vue'

const app = createApp(App);

app.mount('#app');
