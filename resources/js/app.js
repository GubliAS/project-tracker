import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import './assets/css/styles.css';
import './assets/css/pm-custom.css';
import './assets/js/preline.js';

createInertiaApp({
    resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .mount(el);
    },
    title: (title) => (title ? `${title} - Project Tracker` : 'Project Tracker'),
    progress: {
        color: '#5c61f2',
    },
});
