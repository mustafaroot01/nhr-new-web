<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';
import AppLayout from '../../Layouts/AppLayout.vue';
import PostCard from '../../Components/PostCard.vue';
import { useLocale } from '../../Composables/useLocale';

const props = defineProps({ post: Object, related: Array });
const { locale, localeUrl, t } = useLocale();

function formatViews(n) {
    const num = Number(n) || 0;
    try {
        return new Intl.NumberFormat(locale.value === 'ar' ? 'ar-EG' : 'en-US', {
            notation: 'compact', maximumFractionDigits: 1,
        }).format(num);
    } catch (e) {
        return num;
    }
}

const fontScale = ref(Number(localStorage.getItem('nahr_font_scale')) || 1);
function setFont(delta) {
    fontScale.value = Math.min(1.5, Math.max(0.85, +(fontScale.value + delta).toFixed(2)));
    try { localStorage.setItem('nahr_font_scale', String(fontScale.value)); } catch (e) {}
}
function printPage() { window.print(); }

const shareUrl = typeof window !== 'undefined' ? window.location.href : '';
function share(network) {
    const u = encodeURIComponent(shareUrl);
    const title = encodeURIComponent(props.post.title);
    const map = {
        facebook: `https://www.facebook.com/sharer/sharer.php?u=${u}`,
        twitter: `https://twitter.com/intent/tweet?url=${u}&text=${title}`,
        whatsapp: `https://wa.me/?text=${title}%20${u}`,
        telegram: `https://t.me/share/url?url=${u}&text=${title}`,
    };
    if (network === 'copy') {
        navigator.clipboard?.writeText(shareUrl);
        return;
    }
    window.open(map[network], '_blank', 'width=600,height=500');
}

const lightbox = ref(null);
const readProgress = ref(0);

function updateReadProgress() {
    const el = document.querySelector('.article-body');
    if (!el) return;
    const rect = el.getBoundingClientRect();
    const total = el.scrollHeight;
    const visible = window.innerHeight;
    const scrolled = Math.max(0, -rect.top);
    readProgress.value = Math.min(100, Math.max(0, (scrolled / (total - visible * 0.5)) * 100));
}

onMounted(() => window.addEventListener('scroll', updateReadProgress, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', updateReadProgress));
</script>

<template>
    <Head :title="post.meta_title || post.title">
        <meta name="description" :content="post.meta_description" />
        <meta property="og:title" :content="post.title" />
        <meta property="og:description" :content="post.meta_description" />
        <meta v-if="post.image" property="og:image" :content="post.image" />
        <meta property="og:type" content="article" />
    </Head>
    <AppLayout>
        <div class="read-progress-track">
            <div class="read-progress-bar" :style="{ width: readProgress + '%' }"></div>
        </div>
        <article class="container article">
            <nav class="crumbs">
                <Link :href="localeUrl('/')">{{ t('الرئيسية', 'Home') }}</Link>
                <span>›</span>
                <Link v-if="post.category" :href="localeUrl('/category/' + post.category.slug)">{{ post.category.name }}</Link>
            </nav>

            <header class="article-head">
                <span v-if="post.category" class="cat-tag">{{ post.category.name }}</span>
                <h1>{{ post.title }}</h1>
                <p v-if="post.subtitle" class="subtitle">{{ post.subtitle }}</p>
                <div class="article-meta">
                    <span v-if="post.author">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                        {{ post.author }}
                    </span>
                    <span v-if="post.date">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        {{ post.date }}
                    </span>
                    <span :title="t('المشاهدات', 'Views')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        {{ formatViews(post.views) }} {{ t('مشاهدة', 'views') }}
                    </span>
                </div>
            </header>

            <div class="toolbar">
                <div class="tool-group">
                    <button @click="setFont(-0.1)" class="tool text-tool" :title="t('تصغير الخط','Smaller')">A<small>−</small></button>
                    <button @click="setFont(0.1)" class="tool text-tool" :title="t('تكبير الخط','Larger')">A<small>+</small></button>
                    <button @click="printPage" class="tool" :title="t('طباعة','Print')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                    </button>
                </div>
                <div class="tool-group share">
                    <span class="share-label">{{ t('مشاركة:', 'Share:') }}</span>
                    <button @click="share('facebook')" class="tool" title="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07"/></svg>
                    </button>
                    <button @click="share('twitter')" class="tool" title="X">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.59l5.24 6.93zm-1.29 19.5h2.04L6.48 3.24H4.3z"/></svg>
                    </button>
                    <button @click="share('whatsapp')" class="tool" title="WhatsApp">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.96-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.4-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.02-.52-.08-.15-.67-1.62-.92-2.22-.24-.58-.49-.5-.67-.5l-.57-.01c-.2 0-.52.07-.8.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.07 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.3 1.26.49 1.69.63.71.22 1.36.19 1.87.12.57-.08 1.76-.72 2.01-1.42.25-.7.25-1.29.17-1.42-.07-.13-.27-.2-.57-.35M12.04 21.5c-1.63 0-3.23-.44-4.63-1.27l-.33-.2-3.44.9.92-3.35-.22-.34a9.4 9.4 0 0 1-1.44-5.01c0-5.2 4.24-9.44 9.46-9.44 2.53 0 4.9.99 6.69 2.77a9.38 9.38 0 0 1 2.76 6.68c0 5.2-4.24 9.44-9.44 9.44M20.52 3.44A11.76 11.76 0 0 0 12.04 0C5.46 0 .1 5.35.1 11.92c0 2.1.55 4.15 1.6 5.96L0 24l6.3-1.65a11.9 11.9 0 0 0 5.73 1.46h.01c6.58 0 11.94-5.35 11.94-11.92 0-3.19-1.24-6.19-3.5-8.45"/></svg>
                    </button>
                    <button @click="share('telegram')" class="tool" title="Telegram">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path d="M23.95 4.57l-3.62 17.06c-.27 1.2-.98 1.5-1.99.93l-5.5-4.05-2.65 2.55c-.3.3-.54.54-1.1.54l.4-5.56L19.5 6.1c.44-.39-.1-.6-.68-.22L6.4 13.84l-5.43-1.7C-.2 11.8-.2 11 1.28 10.4L22.4 2.26c.98-.36 1.84.22 1.55 2.31"/></svg>
                    </button>
                    <button @click="share('copy')" class="tool" :title="t('نسخ الرابط','Copy link')">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"/></svg>
                    </button>
                </div>
            </div>

            <img v-if="post.image" :src="post.image" :alt="post.title" class="hero-img" />

            <div class="article-body" :style="{ fontSize: fontScale + 'rem' }" v-html="post.body"></div>

            <section v-if="post.gallery.length" class="gallery-sec">
                <h3 class="section-title">{{ t('معرض الصور', 'Gallery') }}</h3>
                <div class="gallery">
                    <button v-for="(g, i) in post.gallery" :key="i" class="gallery-item" @click="lightbox = g.url">
                        <img :src="g.url" :alt="g.alt || ''" loading="lazy" />
                    </button>
                </div>
            </section>

            <section v-if="post.attachments.length" class="attach-sec">
                <h3 class="section-title">{{ t('المرفقات', 'Attachments') }}</h3>
                <a v-for="(a, i) in post.attachments" :key="i" :href="a.url" target="_blank" class="attach">
                    <span class="attach-type">{{ a.type }}</span>
                    <span class="attach-name">{{ a.name }}</span>
                    <span class="attach-size">{{ a.size }}</span>
                    <span class="attach-dl">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                    </span>
                </a>
            </section>

            <section v-if="related.length" class="related">
                <h3 class="section-title">{{ t('مقالات ذات صلة', 'Related') }}</h3>
                <div class="grid">
                    <PostCard v-for="p in related" :key="p.id" :post="p" />
                </div>
            </section>
        </article>

        <div v-if="lightbox" class="lightbox" @click="lightbox = null">
            <img :src="lightbox" alt="" />
            <button class="lightbox-close">✕</button>
        </div>
    </AppLayout>
</template>

<style scoped>
.read-progress-track {
    position: sticky; top: 0; z-index: 40;
    width: 100%; height: 3px;
    background: var(--border);
}
.read-progress-bar {
    height: 100%;
    background: linear-gradient(90deg, var(--primary), var(--accent));
    transition: width .1s linear;
    border-radius: 0 2px 2px 0;
}
.article { max-width: 820px; padding-top: 24px; padding-bottom: 40px; }
.crumbs { display: flex; gap: 8px; font-size: 14px; color: var(--muted); margin-bottom: 20px; flex-wrap: wrap; }
.crumbs a { color: var(--primary); text-decoration: none; }
.article-head { margin-bottom: 20px; }
.cat-tag { display: inline-block; background: var(--accent); color: #fff; padding: 4px 14px; border-radius: 20px; font-size: 13px; font-weight: 600; margin-bottom: 14px; }
.article-head h1 { font-size: 32px; font-weight: 800; color: var(--secondary); line-height: 1.4; margin: 0 0 10px; }
.subtitle { font-size: 19px; color: var(--muted); margin: 0 0 14px; }
.article-meta { display: flex; gap: 20px; color: var(--muted); font-size: 14px; flex-wrap: wrap; }
.article-meta span { display: inline-flex; align-items: center; gap: 6px; }
.article-meta svg { width: 16px; height: 16px; opacity: .8; }
.toolbar { display: flex; justify-content: space-between; align-items: center; padding: 12px 0; border-top: 1px solid var(--border); border-bottom: 1px solid var(--border); margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.tool-group { display: flex; align-items: center; gap: 6px; }
.share-label { color: var(--muted); font-size: 14px; font-weight: 600; margin-inline-end: 4px; }
.tool { background: var(--bg); border: 1px solid var(--border); border-radius: 8px; width: 40px; height: 40px; cursor: pointer; font-size: 15px; font-weight: 700; color: var(--secondary); display: flex; align-items: center; justify-content: center; transition: background .15s, color .15s, border-color .15s; }
.tool svg { width: 18px; height: 18px; }
.text-tool { font-size: 16px; }
.text-tool small { font-size: 11px; vertical-align: super; }
.tool:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.hero-img { width: 100%; border-radius: 12px; margin-bottom: 28px; }
.article-body { line-height: 1.9; color: var(--text); }
.article-body :deep(p) { margin: 0 0 18px; }
.article-body :deep(h2), .article-body :deep(h3) { color: var(--secondary); margin: 28px 0 14px; }
.article-body :deep(img) { border-radius: 10px; margin: 16px 0; }
.article-body :deep(a) { color: var(--primary); }
.gallery-sec, .attach-sec, .related { margin-top: 40px; }
.gallery { display: grid; grid-template-columns: repeat(4, 1fr); gap: 12px; }
.gallery-item { padding: 0; border: 0; cursor: pointer; border-radius: 8px; overflow: hidden; aspect-ratio: 1; background: #eee; }
.gallery-item img { width: 100%; height: 100%; object-fit: cover; transition: transform .2s; }
.gallery-item:hover img { transform: scale(1.08); }
.attach { display: flex; align-items: center; gap: 14px; background: var(--card); border: 1px solid var(--border); border-radius: 10px; padding: 14px 18px; text-decoration: none; color: var(--text); margin-bottom: 10px; }
.attach:hover { border-color: var(--primary); }
.attach-type { background: #c0392b; color: #fff; font-size: 12px; font-weight: 700; padding: 4px 10px; border-radius: 6px; }
.attach-name { flex: 1; font-weight: 500; }
.attach-size { color: var(--muted); font-size: 13px; }
.attach-dl { font-size: 18px; color: var(--primary); }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; }
.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,.9); display: flex; align-items: center; justify-content: center; z-index: 100; cursor: zoom-out; }
.lightbox img { max-width: 92%; max-height: 92%; border-radius: 8px; }
.lightbox-close { position: absolute; top: 24px; inset-inline-end: 24px; background: transparent; border: 0; color: #fff; font-size: 32px; cursor: pointer; }

@media (max-width: 680px) {
    .article-head h1 { font-size: 24px; }
    .gallery { grid-template-columns: repeat(3, 1fr); }
    .grid { grid-template-columns: 1fr; }
}
@media print {
    .toolbar, .related, .gallery-sec { display: none; }
}
</style>
