import { createApp } from 'vue';
import { createPinia } from 'pinia';
import router from './router/index.js';
import App from './App.vue';
import axios from 'axios';
import 'bootstrap/dist/css/bootstrap.min.css';
import 'bootstrap';

axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest';
axios.defaults.withCredentials = true;

const app = createApp(App);
const pinia = createPinia();

app.use(pinia);

// Importar el store DESPUÉS de instalar pinia
import { useAuthStore } from './stores/auth.js';
const authStore = useAuthStore();

// Cargar usuario antes de montar la app
authStore.fetchUser().finally(() => {
    app.use(router);
    app.mount('#app');
});