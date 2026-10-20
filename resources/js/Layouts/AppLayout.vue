<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, onMounted, watchEffect } from 'vue';
import AppHeader from '../Components/AppHeader.vue';
import AppFooter from '../Components/AppFooter.vue';
import CookieConsent from '../Components/CookieConsent.vue';
import BackToTop from '../Components/BackToTop.vue';
import { useDarkMode } from '../Composables/useDarkMode';
import ToastNotifications from '../Components/ToastNotifications.vue';
import PageLoadingBar from '../Components/PageLoadingBar.vue';

const page = usePage();
const { init: initDarkMode } = useDarkMode();
onMounted(initDarkMode);
const settings = computed(() => page.props.settings);
const locale = computed(() => page.props.locale || 'ar');

// Load a Google Font dynamically and return a promise that resolves when fonts are ready.
function ensureFontLoaded(families) {
    const unique = [...new Set(families.filter(Boolean))];
    if (!unique.length) return Promise.resolve();
    const id = 'nahr-dynamic-fonts';
    const href = 'https://fonts.googleapis.com/css2?' +
        unique.map((f) => 'family=' + encodeURIComponent(f) + ':wght@300;400;500;600;700;800').join('&') +
        '&display=swap';
    let link = document.getElementById(id);
    const needsLoad = !link || link.getAttribute('href') !== href;
    if (!link) {
        link = document.createElement('link');
        link.id = id;
        link.rel = 'stylesheet';
        document.head.appendChild(link);
    }
    if (needsLoad) {
        link.setAttribute('href', href);
        return new Promise((resolve) => {
            link.addEventListener('load', () => {
                document.fonts.ready.then(resolve);
            }, { once: true });
            setTimeout(resolve, 3000);
        });
    }
    return document.fonts.ready;
}

// Apply brand colors + chosen fonts from shared props
watchEffect(async () => {
    if (typeof document === 'undefined') return;
    const root = document.documentElement;

    const c = settings.value?.colors;
    if (c) {
        root.style.setProperty('--primary', c.primary);
        root.style.setProperty('--secondary', c.secondary);
        root.style.setProperty('--accent', c.accent);
    }

    const fonts = settings.value?.fonts;
    if (fonts) {
        await ensureFontLoaded([fonts.ar, fonts.en]);
        const active = locale.value === 'ar' ? fonts.ar : fonts.en;
        const fallback = locale.value === 'ar' ? 'Tajawal' : 'Inter';
        root.style.setProperty('--font-sans', `'${active}', '${fallback}', system-ui, sans-serif`);
    }
});
</script>

<template>
    <div class="app-shell">
        <PageLoadingBar />
        <AppHeader />
        <main class="app-main">
            <slot />
        </main>
        <AppFooter />
        <CookieConsent />
        <BackToTop />
        <ToastNotifications />
    </div>
</template>

<style>
:root {
    --primary: #1a5276;
    --secondary: #2c3e50;
    --accent: #c9941e;
    --dark: #1a2332;
    --text: #1f2937;
    --muted: #6b7280;
    --bg: #f8f9fa;
    --card: #ffffff;
    --border: #e5e7eb;
    --font-sans: 'Tajawal', 'Inter', system-ui, sans-serif;
}
* { box-sizing: border-box; }
html { scroll-behavior: smooth; }
body {
    margin: 0;
    font-family: var(--font-sans);
    color: var(--text);
    background: var(--bg);
    line-height: 1.6;
}
.container { max-width: 1200px; margin: 0 auto; padding: 0 16px; }
.app-shell { min-height: 100vh; display: flex; flex-direction: column; }
.app-main { flex: 1; }
a { color: var(--primary); }
img { max-width: 100%; }

/* Prevent any accidental horizontal overflow on small screens */
html, body { overflow-x: hidden; max-width: 100%; }

.section-title {
    display: flex; align-items: center; gap: 12px;
    font-size: clamp(20px, 4vw, 24px); font-weight: 800; color: var(--secondary);
    margin: 0 0 24px;
}
.section-title::before {
    content: ''; width: 5px; height: 26px; background: var(--accent); border-radius: 3px; flex-shrink: 0;
}
.btn {
    display: inline-flex; align-items: center; gap: 8px;
    background: var(--primary); color: #fff; text-decoration: none;
    padding: 10px 22px; border-radius: 8px; font-weight: 600; border: 0; cursor: pointer;
    transition: background .2s, transform .1s;
}
.btn:hover { background: var(--secondary); }
.btn-accent { background: var(--accent); }
.btn-accent:hover { background: #a87a16; }
.btn-outline { background: transparent; border: 1px solid var(--border); color: var(--text); }

/* Dark mode overrides */
html.dark {
    --text: #e5e7eb;
    --muted: #9ca3af;
    --bg: #111827;
    --card: #1f2937;
    --border: #374151;
    --dark: #0f172a;
}
html.dark body { background: var(--bg); color: var(--text); }
html.dark .main-bar { background: #1f2937; }
html.dark .cat-bar { background: var(--primary); }
html.dark img { filter: brightness(.92); }

@media (max-width: 560px) {
    .container { padding: 0 14px; }
}
</style>
