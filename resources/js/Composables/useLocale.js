import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

export function useLocale() {
    const page = usePage();
    const locale = computed(() => page.props.locale || 'ar');
    const isRtl = computed(() => locale.value === 'ar');

    // Build a localized URL for the current locale
    const localeUrl = (path = '') => {
        const clean = path.startsWith('/') ? path : `/${path}`;
        return `/${locale.value}${clean === '/' ? '' : clean}`;
    };

    // Switch to the other locale on the current path
    const switchLocaleUrl = () => {
        const other = locale.value === 'ar' ? 'en' : 'ar';
        const current = window.location.pathname;
        const stripped = current.replace(/^\/(ar|en)/, '');
        return `/${other}${stripped || ''}`;
    };

    const t = (ar, en) => (locale.value === 'ar' ? ar : en);

    return { locale, isRtl, localeUrl, switchLocaleUrl, t };
}
