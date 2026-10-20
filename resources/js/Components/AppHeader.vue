<script setup>
import { Link, router, usePage } from '@inertiajs/vue3';
import { computed, nextTick, ref, watch } from 'vue';
import { useLocale } from '../Composables/useLocale';
import { useDarkMode } from '../Composables/useDarkMode';

const page = usePage();
const { locale, localeUrl, switchLocaleUrl, t } = useLocale();
const settings = computed(() => page.props.settings);
const siteName = computed(() => t(settings.value.site_name_ar, settings.value.site_name_en));
const categories = computed(() => page.props.navigation || []);

const { isDark, toggle: toggleDark } = useDarkMode();
const mobileOpen = ref(false);
const openDropdown = ref(null);
const expandedCat = ref(null);
const searchQ = ref('');
const searchOpen = ref(false);

const currentPath = computed(() => page.url);
const isActive = (href) => {
    const clean = href.replace(/^\/(ar|en)/, '') || '/';
    const cur = currentPath.value.replace(/^\/(ar|en)/, '') || '/';
    return clean === '/' ? cur === '/' : cur.startsWith(clean);
};

// Primary category bar entries (home + dynamic categories + content sections)
const tailNav = computed(() => [
    { label: t('التقارير والإصدارات', 'Reports'), href: localeUrl('/reports') },
    { label: t('المكتبة الإعلامية', 'Media'), href: localeUrl('/media') },
]);

// Utility links in the slim top row
const utilityNav = computed(() => [
    { label: t('من نحن', 'About'), href: localeUrl('/من-نحن'), icon: 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0z' },
    { label: t('اتصل بنا', 'Contact'), href: localeUrl('/contact'), icon: 'M3 8l7.89 5.26a2 2 0 0 0 2.22 0L21 8M5 19h14a2 2 0 0 0 2-2V7a2 2 0 0 0-2-2H5a2 2 0 0 0-2 2v10a2 2 0 0 0 2 2z' },
    { label: t('تقديم بلاغ', 'Report'), href: localeUrl('/complaint'), icon: 'M12 9v2m0 4h.01M5.07 19H19a2 2 0 0 0 1.75-2.97L13.74 4a2 2 0 0 0-3.5 0L3.33 16.03A2 2 0 0 0 5.07 19z' },
]);

const searchInput = ref(null);

function doSearch() {
    const q = searchQ.value.trim();
    if (q.length >= 2) {
        router.get(localeUrl('/search'), { q }, { preserveState: false });
        searchOpen.value = false;
        closeMobile();
    }
}

function toggleSearch() {
    searchOpen.value = !searchOpen.value;
    if (searchOpen.value) {
        nextTick(() => searchInput.value?.focus());
    }
}

function closeMobile() { mobileOpen.value = false; }

function toggleCat(id) {
    expandedCat.value = expandedCat.value === id ? null : id;
}

// Lock body scroll when mobile menu is open
watch(mobileOpen, (v) => {
    document.body.style.overflow = v ? 'hidden' : '';
});
</script>

<template>
    <header class="site-header">
        <!-- Top utility strip -->
        <div class="utility-bar">
            <div class="container utility-inner">
                <div class="utility-links">
                    <Link v-for="u in utilityNav" :key="u.href" :href="u.href" class="utility-link">{{ u.label }}</Link>
                </div>
                <div class="utility-actions">
                    <button class="dark-toggle" @click="toggleDark" :aria-label="isDark ? 'Light mode' : 'Dark mode'">
                        <svg v-if="isDark" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                        <svg v-else width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                    </button>
                    <span class="utility-sep"></span>
                    <a :href="switchLocaleUrl()" class="lang-switch">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20"/></svg>
                        {{ t('English', 'عربي') }}
                    </a>
                </div>
            </div>
        </div>

        <!-- Main header: brand + search -->
        <div class="main-bar">
            <div class="container main-inner">
                <Link :href="localeUrl('/')" class="brand">
                    <img :src="settings.logo" :alt="siteName" class="brand-logo" />
                    <span class="brand-name">{{ siteName }}</span>
                </Link>

                <form class="search-box" @submit.prevent="doSearch">
                    <svg class="search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input ref="searchInput" v-model="searchQ" :placeholder="t('ابحث في الموقع...', 'Search the site...')" class="search-input" />
                    <button type="submit" class="search-btn">{{ t('بحث', 'Search') }}</button>
                </form>

                <button class="burger" @click="mobileOpen = !mobileOpen" aria-label="menu">
                    <span :class="['burger-bar', { open: mobileOpen }]"></span>
                    <span :class="['burger-bar', { open: mobileOpen }]"></span>
                    <span :class="['burger-bar', { open: mobileOpen }]"></span>
                </button>
            </div>
        </div>

        <!-- Category navigation bar -->
        <nav class="cat-bar">
            <div class="container cat-inner">
                <Link :href="localeUrl('/')" :class="['cat-link', { active: isActive(localeUrl('/')) }]">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                    {{ t('الرئيسية', 'Home') }}
                </Link>

                <div
                    v-for="cat in categories"
                    :key="cat.id"
                    class="cat-item"
                    @mouseenter="openDropdown = cat.id"
                    @mouseleave="openDropdown = null"
                >
                    <Link :href="localeUrl('/category/' + cat.slug)" :class="['cat-link', { active: isActive(localeUrl('/category/' + cat.slug)) }]">
                        {{ cat.name }}
                        <span v-if="cat.children.length" class="caret">▾</span>
                    </Link>
                    <transition name="fade">
                        <div v-if="cat.children.length && openDropdown === cat.id" class="dropdown">
                            <Link
                                v-for="child in cat.children"
                                :key="child.id"
                                :href="localeUrl('/category/' + child.slug)"
                                class="dropdown-link"
                            >
                                <span class="dot"></span>{{ child.name }}
                            </Link>
                        </div>
                    </transition>
                </div>

                <Link v-for="item in tailNav" :key="item.href" :href="item.href" :class="['cat-link', { active: isActive(item.href) }]">
                    {{ item.label }}
                </Link>
            </div>
        </nav>

        <!-- Mobile overlay + drawer -->
        <transition name="overlay">
            <div v-if="mobileOpen" class="mobile-overlay" @click="closeMobile"></div>
        </transition>
        <transition name="drawer">
            <nav v-if="mobileOpen" class="mobile-drawer">
                <!-- Drawer header -->
                <div class="drawer-header">
                    <Link :href="localeUrl('/')" class="drawer-brand" @click="closeMobile">
                        <img v-if="settings.logo" :src="settings.logo" :alt="siteName" class="drawer-logo" />
                        <span class="drawer-name">{{ siteName }}</span>
                    </Link>
                    <button class="drawer-close" @click="closeMobile" aria-label="close">
                        <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                    </button>
                </div>

                <!-- Search -->
                <div class="drawer-search">
                    <svg class="drawer-search-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.3-4.3"/></svg>
                    <input v-model="searchQ" @keyup.enter="doSearch" :placeholder="t('ابحث في الموقع...', 'Search...')" />
                </div>

                <!-- Navigation links -->
                <div class="drawer-links">
                    <Link :href="localeUrl('/')" :class="['drawer-link', { active: isActive(localeUrl('/')) }]" @click="closeMobile">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="m3 9 9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"/><path d="M9 22V12h6v10"/></svg>
                        {{ t('الرئيسية', 'Home') }}
                    </Link>

                    <div class="drawer-section-label">{{ t('الأقسام', 'Categories') }}</div>

                    <template v-for="cat in categories" :key="cat.id">
                        <div class="drawer-cat-row">
                            <Link :href="localeUrl('/category/' + cat.slug)" :class="['drawer-link', { active: isActive(localeUrl('/category/' + cat.slug)) }]" @click="closeMobile">
                                {{ cat.name }}
                            </Link>
                            <button v-if="cat.children.length" class="drawer-expand" :class="{ open: expandedCat === cat.id }" @click.stop="toggleCat(cat.id)" aria-label="expand">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round"><polyline points="6 9 12 15 18 9"/></svg>
                            </button>
                        </div>
                        <transition name="expand">
                            <div v-if="cat.children.length && expandedCat === cat.id" class="drawer-children">
                                <Link
                                    v-for="child in cat.children"
                                    :key="child.id"
                                    :href="localeUrl('/category/' + child.slug)"
                                    :class="['drawer-child', { active: isActive(localeUrl('/category/' + child.slug)) }]"
                                    @click="closeMobile"
                                >
                                    <span class="child-dot"></span>{{ child.name }}
                                </Link>
                            </div>
                        </transition>
                    </template>

                    <div class="drawer-section-label">{{ t('المزيد', 'More') }}</div>

                    <Link v-for="item in tailNav" :key="item.href" :href="item.href" :class="['drawer-link', { active: isActive(item.href) }]" @click="closeMobile">
                        {{ item.label }}
                    </Link>
                    <Link v-for="u in utilityNav" :key="u.href" :href="u.href" :class="['drawer-link drawer-link--muted', { active: isActive(u.href) }]" @click="closeMobile">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path :d="u.icon" /></svg>
                        {{ u.label }}
                    </Link>
                </div>

                <!-- Footer: language switch -->
                <div class="drawer-footer">
                    <a :href="switchLocaleUrl()" class="drawer-lang">
                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15 15 0 0 1 0 20 15 15 0 0 1 0-20"/></svg>
                        {{ t('English', 'عربي') }}
                    </a>
                </div>
            </nav>
        </transition>
    </header>
</template>

<style scoped>
.site-header { position: sticky; top: 0; z-index: 50; box-shadow: 0 2px 12px rgba(0,0,0,.1); }

/* Utility strip */
.utility-bar { background: var(--dark); color: #cbd5e1; font-size: 13px; }
.utility-inner { display: flex; align-items: center; justify-content: space-between; height: 38px; }
.utility-links { display: flex; gap: 2px; }
.utility-link { color: #cbd5e1; text-decoration: none; padding: 4px 12px; border-radius: 6px; }
.utility-link:hover { color: #fff; background: rgba(255,255,255,.08); }
.utility-actions { display: flex; align-items: center; gap: 4px; }
.utility-sep { width: 1px; height: 16px; background: rgba(255,255,255,.2); }
.dark-toggle { background: none; border: none; color: #cbd5e1; cursor: pointer; padding: 7px 10px; border-radius: 6px; display: flex; align-items: center; transition: color .2s, background .2s; }
.dark-toggle:hover { color: #fbbf24; background: rgba(255,255,255,.08); }
.lang-switch { display: inline-flex; align-items: center; gap: 6px; color: #cbd5e1; text-decoration: none; font-weight: 600; padding: 4px 12px; border-radius: 6px; transition: color .2s, background .2s; }
.lang-switch:hover { color: #fff; background: rgba(255,255,255,.08); }

/* Main brand bar */
.main-bar { background: #fff; }
.main-inner { display: flex; align-items: center; gap: 24px; height: 84px; }
.brand { display: flex; align-items: center; gap: 14px; text-decoration: none; flex-shrink: 0; }
.brand-logo { height: 62px; width: auto; }
.brand-name { font-weight: 800; font-size: 19px; color: var(--secondary); line-height: 1.3; max-width: 320px; }
.search-box {
    display: flex; align-items: center; gap: 4px; background: var(--bg);
    border: 1.5px solid var(--border); border-radius: 12px;
    margin-inline-start: auto; width: 360px; max-width: 42vw; padding-inline-start: 14px; padding: 4px 4px 4px 14px;
    transition: border-color .2s, box-shadow .2s;
}
.search-box:focus-within { border-color: var(--primary); box-shadow: 0 0 0 3px rgba(26,82,118,.1); }
.search-icon { width: 18px; height: 18px; color: var(--muted); flex-shrink: 0; }
.search-input { flex: 1; background: transparent; border: 0; padding: 12px 10px; color: var(--text); outline: none; font-family: inherit; font-size: 15px; }
.search-btn {
    background: var(--primary); border: 0; color: #fff; padding: 10px 24px;
    cursor: pointer; font-weight: 600; font-size: 14px; font-family: inherit;
    border-radius: 10px; transition: background .2s, transform .15s, box-shadow .2s;
    letter-spacing: .3px;
}
.search-btn:hover { background: var(--secondary); transform: scale(1.03); box-shadow: 0 2px 8px rgba(26,82,118,.25); }
.search-btn:active { transform: scale(.97); }
.burger {
    display: none; background: transparent; border: 0; cursor: pointer;
    margin-inline-start: auto; width: 36px; height: 36px;
    flex-direction: column; align-items: center; justify-content: center; gap: 5px;
    padding: 4px;
}
.burger-bar {
    display: block; width: 24px; height: 2.5px; background: var(--secondary);
    border-radius: 2px; transition: transform .3s, opacity .3s;
}
.burger-bar.open:nth-child(1) { transform: translateY(7.5px) rotate(45deg); }
.burger-bar.open:nth-child(2) { opacity: 0; }
.burger-bar.open:nth-child(3) { transform: translateY(-7.5px) rotate(-45deg); }

/* Category bar */
.cat-bar { background: var(--primary); }
.cat-inner { display: flex; align-items: stretch; gap: 2px; }
.cat-item { position: relative; display: flex; }
.cat-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: #fff; text-decoration: none; padding: 14px 16px;
    font-weight: 600; font-size: 15px; white-space: nowrap;
    border-bottom: 3px solid transparent; transition: background .2s, border-color .2s;
}
.cat-link:hover { background: rgba(255,255,255,.12); }
.cat-link.active { border-bottom-color: var(--accent); background: rgba(255,255,255,.08); }
.caret { font-size: 10px; opacity: .85; }

.dropdown {
    position: absolute; top: 100%; inset-inline-start: 0;
    background: #fff; min-width: 240px; border-radius: 0 0 12px 12px;
    box-shadow: 0 14px 34px rgba(0,0,0,.18); padding: 8px; z-index: 60;
    border-top: 3px solid var(--accent);
}
.dropdown-link {
    display: flex; align-items: center; gap: 10px;
    color: var(--text); text-decoration: none; padding: 11px 14px;
    border-radius: 8px; font-size: 14px; font-weight: 500;
}
.dropdown-link:hover { background: var(--bg); color: var(--primary); }
.dot { width: 6px; height: 6px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }

.fade-enter-active, .fade-leave-active { transition: opacity .18s, transform .18s; }
.fade-enter-from, .fade-leave-to { opacity: 0; transform: translateY(-6px); }

/* Mobile overlay */
.mobile-overlay {
    display: none; position: fixed; inset: 0; z-index: 100;
    background: rgba(0,0,0,.45); backdrop-filter: blur(2px);
}
.overlay-enter-active, .overlay-leave-active { transition: opacity .3s ease; }
.overlay-enter-from, .overlay-leave-to { opacity: 0; }

/* Mobile drawer */
.mobile-drawer {
    display: none; position: fixed; top: 0; inset-inline-start: 0; bottom: 0;
    width: 310px; max-width: 85vw; z-index: 110;
    background: #fff; flex-direction: column;
    box-shadow: 4px 0 24px rgba(0,0,0,.15);
}
[dir="rtl"] .mobile-drawer { box-shadow: -4px 0 24px rgba(0,0,0,.15); }
.drawer-enter-active, .drawer-leave-active { transition: transform .3s cubic-bezier(.4,0,.2,1); }
.drawer-enter-from, .drawer-leave-to { transform: translateX(-100%); }
[dir="rtl"] .drawer-enter-from, [dir="rtl"] .drawer-leave-to { transform: translateX(100%); }

/* Drawer header */
.drawer-header {
    display: flex; align-items: center; justify-content: space-between; gap: 12px;
    padding: 16px 18px; border-bottom: 1px solid var(--border);
    background: var(--bg); flex-shrink: 0;
}
.drawer-brand { display: flex; align-items: center; gap: 10px; text-decoration: none; min-width: 0; }
.drawer-logo { height: 40px; width: auto; flex-shrink: 0; }
.drawer-name { font-weight: 700; font-size: 14px; color: var(--secondary); line-height: 1.3; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.drawer-close {
    width: 36px; height: 36px; border-radius: 50%; border: 0;
    background: transparent; color: var(--muted); cursor: pointer;
    display: flex; align-items: center; justify-content: center; flex-shrink: 0;
    transition: background .2s, color .2s;
}
.drawer-close:hover { background: rgba(0,0,0,.06); color: var(--text); }

/* Drawer search */
.drawer-search {
    display: flex; align-items: center; gap: 8px; margin: 14px 18px;
    background: var(--bg); border: 1.5px solid var(--border);
    border-radius: 10px; padding: 0 14px; flex-shrink: 0;
}
.drawer-search:focus-within { border-color: var(--primary); }
.drawer-search-icon { width: 17px; height: 17px; color: var(--muted); flex-shrink: 0; }
.drawer-search input {
    flex: 1; border: 0; background: transparent; padding: 11px 4px;
    font-size: 14px; color: var(--text); outline: none; font-family: inherit;
}

/* Drawer links area */
.drawer-links { flex: 1; overflow-y: auto; padding: 6px 0; }
.drawer-section-label {
    font-size: 11px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--muted); padding: 16px 20px 6px; user-select: none;
}
.drawer-link {
    display: flex; align-items: center; gap: 10px;
    color: var(--text); text-decoration: none;
    padding: 11px 20px; font-size: 15px; font-weight: 500;
    transition: background .15s, color .15s;
}
.drawer-link svg { width: 18px; height: 18px; color: var(--muted); flex-shrink: 0; }
.drawer-link:hover { background: var(--bg); }
.drawer-link.active { color: var(--primary); font-weight: 700; background: rgba(26,82,118,.06); }
.drawer-link.active svg { color: var(--primary); }
.drawer-link--muted { color: var(--muted); font-size: 14px; }

/* Category row with expand */
.drawer-cat-row { display: flex; align-items: center; }
.drawer-cat-row .drawer-link { flex: 1; }
.drawer-expand {
    width: 40px; height: 44px; border: 0; background: transparent;
    display: flex; align-items: center; justify-content: center;
    color: var(--muted); cursor: pointer; flex-shrink: 0;
    transition: transform .25s, color .2s;
}
.drawer-expand:hover { color: var(--primary); }
.drawer-expand.open { transform: rotate(180deg); }

/* Children sub-list */
.drawer-children { background: var(--bg); border-radius: 0; }
.drawer-child {
    display: flex; align-items: center; gap: 8px;
    color: var(--text); text-decoration: none;
    padding: 9px 20px 9px 36px; font-size: 14px;
    transition: background .15s, color .15s;
}
[dir="rtl"] .drawer-child { padding: 9px 36px 9px 20px; }
.drawer-child:hover { background: rgba(0,0,0,.03); color: var(--primary); }
.drawer-child.active { color: var(--primary); font-weight: 600; }
.child-dot { width: 5px; height: 5px; border-radius: 50%; background: var(--accent); flex-shrink: 0; }

/* Expand animation */
.expand-enter-active, .expand-leave-active { transition: all .25s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; }
.expand-enter-to, .expand-leave-from { max-height: 300px; }

/* Drawer footer */
.drawer-footer {
    flex-shrink: 0; border-top: 1px solid var(--border);
    padding: 14px 18px; background: var(--bg);
}
.drawer-lang {
    display: inline-flex; align-items: center; gap: 8px;
    color: var(--primary); text-decoration: none; font-weight: 600; font-size: 14px;
    padding: 8px 16px; border-radius: 8px; border: 1.5px solid var(--primary);
    transition: background .2s, color .2s;
}
.drawer-lang:hover { background: var(--primary); color: #fff; }

@media (max-width: 920px) {
    .cat-bar { display: none; }
    .search-box { display: none; }
    .burger { display: flex; }
    .mobile-overlay { display: block; }
    .mobile-drawer { display: flex; }
    .utility-links { display: none; }
    .main-inner { height: 68px; }
    .brand-logo { height: 50px; }
    .brand-name { font-size: 16px; max-width: 220px; }
}
</style>
