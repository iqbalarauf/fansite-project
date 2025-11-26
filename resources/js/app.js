import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';

const readAppSettingsFromDom = () => {
    try {
        const el = document?.getElementById('app');
        if (!el?.dataset?.page) return null;
        const page = JSON.parse(el.dataset.page);
        return page?.props?.appSettings ?? null;
    } catch (e) {
        return null;
    }
};

let currentAppSettings = readAppSettingsFromDom();

const titleSuffix = () => currentAppSettings?.sidebar_name
    || currentAppSettings?.app_name
    || import.meta.env.VITE_APP_NAME
    || 'Laravel';

document.addEventListener('inertia:finish', (event) => {
    currentAppSettings = event?.detail?.visit?.page?.props?.appSettings ?? currentAppSettings;
});

createInertiaApp({
    // Compose the browser title using the freshest shared settings that Inertia
    // has provided so updates apply immediately after saving the form.
    title: (title) => `${title} - ${titleSuffix()}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        currentAppSettings = props?.initialPage?.props?.appSettings ?? currentAppSettings;
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
