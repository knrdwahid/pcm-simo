import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';

// Vuetify 3
import 'vuetify/styles';
import '@mdi/font/css/materialdesignicons.css';
import { createVuetify } from 'vuetify';
import * as components from 'vuetify/components';
import * as directives from 'vuetify/directives';

const vuetify = createVuetify({
    components,
    directives,
    icons: {
        defaultSet: 'mdi',
    },
    theme: {
        defaultTheme: 'pcmSimo',
        themes: {
            pcmSimo: {
                dark: false,
                colors: {
                    primary: '#006837',   // Hijau Muhammadiyah Resmi
                    secondary: '#0A2540', // Deep Midnight Navy
                    accent: '#F59E0B',    // Emas Surya Muhammadiyah
                    surface: '#FFFFFF',
                    background: '#F8FAF8',
                    info: '#0284C7',
                    success: '#059669',
                    warning: '#D97706',
                    error: '#DC2626',
                },
            },
        },
    },
});

const appName = import.meta.env.VITE_APP_NAME || 'PCM Simo';

createInertiaApp({
    title: (title) => {
        if (!title) return 'Portal Resmi | PCM Simo';
        return title.includes('PCM Simo') ? title : `${title} | ${appName}`;
    },
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(vuetify)
            .mount(el);
    },
    progress: {
        delay: 50,
        color: '#006837',
        includeCSS: true,
        showSpinner: false,
    },
});
