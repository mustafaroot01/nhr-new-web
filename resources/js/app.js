import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { createI18n } from 'vue-i18n';
import '../css/app.css';

const i18n = createI18n({
    legacy: false,
    locale: document.documentElement.lang || 'ar',
    fallbackLocale: 'ar',
    messages: {},
});

createInertiaApp({
    title: (title) => title ? `${title} - جمعية النضال لحقوق الإنسان` : 'جمعية النضال لحقوق الإنسان',
    resolve: (name) => {
        const pages = import.meta.glob('./Pages/**/*.vue', { eager: true });
        return pages[`./Pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(i18n)
            .mount(el);
    },
});
