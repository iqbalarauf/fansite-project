import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';


createInertiaApp({
    // Compose the browser title using the latest app name available on
    // window.page.props so changes saved to the DB show up immediately
    // after Inertia visits/redirects.
    title: (title) => `${title} - ${window?.page?.props?.appSettings?.sidebar_name || window?.page?.props?.appSettings?.app_name || import.meta.env.VITE_APP_NAME || 'Laravel'}`,
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mount(el);
    },
    progress: {
        color: '#4B5563',
    },
});
