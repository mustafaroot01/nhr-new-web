<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed, onMounted, onBeforeUnmount, ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import PostCard from '../Components/PostCard.vue';
import FacebookFeed from '../Components/FacebookFeed.vue';
import { useLocale } from '../Composables/useLocale';

const fbUrl = computed(() => usePage().props.settings?.social?.facebook || '');

const props = defineProps({
    banners: Array,
    featured: Array,
    latest: Array,
    sections: Array,
    reports: Array,
});

const { localeUrl, t } = useLocale();
const slide = ref(0);
let timer = null;

function go(i) {
    const n = props.banners.length;
    slide.value = (i + n) % n;
}

function startAuto() {
    stopAuto();
    if (props.banners.length > 1) {
        timer = setInterval(() => go(slide.value + 1), 5000);
    }
}
function stopAuto() {
    if (timer) {
        clearInterval(timer);
        timer = null;
    }
}
// Restart the countdown whenever the user manually changes slide
function manualGo(i) {
    go(i);
    startAuto();
}

onMounted(startAuto);
onBeforeUnmount(stopAuto);
</script>

<template>
    <Head :title="t('الرئيسية', 'Home')" />
    <AppLayout>
        <!-- Hero slider -->
        <section v-if="banners.length" class="hero" @mouseenter="stopAuto" @mouseleave="startAuto">
            <transition-group name="fade" tag="div" class="hero-track">
                <div v-for="(b, i) in banners" :key="i" v-show="i === slide" class="hero-slide">
                    <video
                        v-if="b.type === 'video' && b.video"
                        class="hero-video"
                        :src="b.video"
                        :poster="b.image || undefined"
                        autoplay
                        muted
                        loop
                        playsinline
                    ></video>
                    <div v-else class="hero-bg" :class="{ active: i === slide }" :style="{ backgroundImage: `url(${b.image})` }"></div>
                    <div class="hero-overlay"></div>
                </div>
            </transition-group>

            <div class="hero-content container">
                <transition :name="'rise'" mode="out-in">
                    <div :key="slide" class="hero-caption" v-if="banners[slide] && (banners[slide].title || banners[slide].subtitle)">
                        <span class="hero-kicker">{{ t('جمعية النضال لحقوق الإنسان', 'Al-Nidal for Human Rights') }}</span>
                        <h1 v-if="banners[slide].title">{{ banners[slide].title }}</h1>
                        <p v-if="banners[slide].subtitle">{{ banners[slide].subtitle }}</p>
                        <a v-if="banners[slide].link && banners[slide].button" :href="banners[slide].link" class="hero-btn">
                            {{ banners[slide].button }}
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="5" y1="12" x2="19" y2="12"/><polyline points="12 5 19 12 12 19"/></svg>
                        </a>
                    </div>
                </transition>
            </div>

            <template v-if="banners.length > 1">
                <button class="hero-nav prev" @click="manualGo(slide - 1)" aria-label="prev">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="15 18 9 12 15 6"/></svg>
                </button>
                <button class="hero-nav next" @click="manualGo(slide + 1)" aria-label="next">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><polyline points="9 18 15 12 9 6"/></svg>
                </button>
                <div class="hero-dots">
                    <button v-for="(b, i) in banners" :key="i" :class="['dot', { active: i === slide }]" @click="manualGo(i)" aria-label="slide">
                        <span class="dot-fill"></span>
                    </button>
                </div>
                <div class="hero-counter"><strong>{{ slide + 1 }}</strong> / {{ banners.length }}</div>
            </template>
        </section>

        <div class="container">
            <!-- Featured -->
            <section v-if="featured.length" class="block">
                <h2 class="section-title">{{ t('الأخبار المميزة', 'Featured News') }}</h2>
                <div class="grid">
                    <PostCard v-for="p in featured" :key="p.id" :post="p" />
                </div>
            </section>

            <!-- Latest news -->
            <section v-if="latest.length" class="block">
                <div class="block-head">
                    <h2 class="section-title">{{ t('آخر الأخبار', 'Latest News') }}</h2>
                    <Link :href="localeUrl('/posts')" class="see-all">{{ t('عرض الكل', 'View all') }} ←</Link>
                </div>
                <div class="grid">
                    <PostCard v-for="p in latest" :key="p.id" :post="p" />
                </div>
            </section>

            <!-- Category sections -->
            <section v-for="sec in sections" :key="sec.category.id" class="block">
                <div class="block-head">
                    <h2 class="section-title">{{ sec.category.name }}</h2>
                    <Link :href="localeUrl('/category/' + sec.category.slug)" class="see-all">{{ t('عرض الكل', 'View all') }} ←</Link>
                </div>
                <div class="grid grid-4">
                    <PostCard v-for="p in sec.posts" :key="p.id" :post="p" />
                </div>
            </section>

            <!-- Reports -->
            <section v-if="reports.length" class="block">
                <div class="block-head">
                    <h2 class="section-title">{{ t('التقارير والإصدارات', 'Reports & Publications') }}</h2>
                    <Link :href="localeUrl('/reports')" class="see-all">{{ t('عرض الكل', 'View all') }} ←</Link>
                </div>
                <div class="reports-row">
                    <a v-for="r in reports" :key="r.id" :href="r.pdf || '#'" target="_blank" class="report-item">
                        <div class="report-cover">
                            <img v-if="r.cover" :src="r.cover" :alt="r.title" loading="lazy" />
                            <div v-else class="report-pdf-icon">PDF</div>
                        </div>
                        <div class="report-info">
                            <span class="report-year">{{ r.year }}</span>
                            <h4>{{ r.title }}</h4>
                        </div>
                    </a>
                </div>
            </section>
        </div>

        <!-- Facebook feed (auto, from page settings) -->
        <FacebookFeed :url="fbUrl" />
    </AppLayout>
</template>

<style scoped>
.hero { position: relative; height: 560px; background: #0f1824; overflow: hidden; }
.hero-track { position: absolute; inset: 0; }
.hero-slide { position: absolute; inset: 0; }
.hero-bg {
    position: absolute; inset: 0; background-size: cover; background-position: center;
    transform: scale(1.05);
}
.hero-video {
    position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; display: block;
}
/* Ken Burns slow zoom on the active slide */
.hero-bg.active { animation: kenburns 8s ease-out forwards; }
@keyframes kenburns { from { transform: scale(1.05); } to { transform: scale(1.14); } }
.fade-enter-active, .fade-leave-active { transition: opacity 1s ease; }
.fade-enter-from, .fade-leave-to { opacity: 0; }

.hero-overlay {
    position: absolute; inset: 0;
    background: linear-gradient(to top, rgba(15,24,36,.92) 0%, rgba(15,24,36,.45) 45%, rgba(15,24,36,.25) 100%);
}
.hero-overlay::after {
    content: ''; position: absolute; inset: 0;
    background: linear-gradient(to right, rgba(15,24,36,.6), transparent 60%);
}
[dir="rtl"] .hero-overlay::after {
    background: linear-gradient(to left, rgba(15,24,36,.6), transparent 60%);
}

.hero-content { position: absolute; inset: 0; display: flex; align-items: center; z-index: 2; }
.hero-caption { color: #fff; max-width: 680px; }
.hero-kicker {
    display: inline-block; background: var(--accent); color: #fff;
    font-size: 13px; font-weight: 700; padding: 6px 16px; border-radius: 20px;
    margin-bottom: 18px; letter-spacing: .3px;
}
.hero-caption h1 {
    font-size: 46px; font-weight: 800; line-height: 1.25; margin: 0 0 16px;
    text-shadow: 0 2px 20px rgba(0,0,0,.4);
}
.hero-caption p {
    font-size: 21px; line-height: 1.6; margin: 0 0 28px; color: #e2e8f0;
    text-shadow: 0 1px 10px rgba(0,0,0,.4); max-width: 560px;
}
.hero-btn {
    display: inline-flex; align-items: center; gap: 10px;
    background: var(--accent); color: #fff; text-decoration: none;
    padding: 14px 30px; border-radius: 10px; font-weight: 700; font-size: 16px;
    box-shadow: 0 8px 24px rgba(201,148,30,.35); transition: transform .2s, box-shadow .2s;
}
.hero-btn:hover { transform: translateY(-2px); box-shadow: 0 12px 30px rgba(201,148,30,.45); }
.hero-btn svg { width: 18px; height: 18px; }
[dir="rtl"] .hero-btn svg { transform: scaleX(-1); }

/* Caption rise animation on slide change */
.rise-enter-active { transition: opacity .6s ease, transform .6s ease; }
.rise-leave-active { transition: opacity .3s ease; }
.rise-enter-from { opacity: 0; transform: translateY(24px); }
.rise-leave-to { opacity: 0; }

.hero-nav {
    position: absolute; top: 50%; transform: translateY(-50%);
    background: rgba(255,255,255,.12); backdrop-filter: blur(6px);
    border: 1px solid rgba(255,255,255,.25); color: #fff; width: 52px; height: 52px;
    border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center;
    z-index: 3; transition: background .2s, border-color .2s;
}
.hero-nav svg { width: 24px; height: 24px; }
.hero-nav:hover { background: var(--accent); border-color: var(--accent); }
.hero-nav.prev { inset-inline-start: 28px; }
.hero-nav.next { inset-inline-end: 28px; }

.hero-dots { position: absolute; bottom: 28px; inset-inline-start: 0; inset-inline-end: 0; display: flex; gap: 10px; justify-content: center; z-index: 3; }
.dot { width: 36px; height: 5px; border-radius: 3px; border: 0; background: rgba(255,255,255,.3); cursor: pointer; padding: 0; overflow: hidden; position: relative; }
.dot-fill { position: absolute; inset: 0; inset-inline-end: auto; width: 0; background: var(--accent); border-radius: 3px; }
.dot.active .dot-fill { width: 100%; animation: fill 5s linear; }
@keyframes fill { from { width: 0; } to { width: 100%; } }

.hero-counter {
    position: absolute; bottom: 26px; inset-inline-end: 32px; color: #fff; z-index: 3;
    font-size: 14px; background: rgba(0,0,0,.3); padding: 5px 14px; border-radius: 20px; backdrop-filter: blur(6px);
}
.hero-counter strong { color: var(--accent); font-size: 16px; }

@media (max-width: 860px) {
    .hero { height: 440px; }
    .hero-caption h1 { font-size: 30px; }
    .hero-caption p { font-size: 17px; }
    .hero-nav { width: 42px; height: 42px; }
    .hero-nav.prev { inset-inline-start: 12px; }
    .hero-nav.next { inset-inline-end: 12px; }
    .hero-counter { display: none; }
}

.block { padding: 40px 0; }
.block-head { display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap; }
.see-all { color: var(--primary); font-weight: 600; text-decoration: none; margin-bottom: 24px; white-space: nowrap; }
.see-all:hover { color: var(--accent); }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.grid-4 { grid-template-columns: repeat(4, 1fr); }

.reports-row { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; }
.report-item { display: flex; flex-direction: column; text-decoration: none; background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.report-item:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.report-cover { aspect-ratio: 3/4; background: #eef1f4; }
.report-cover img { width: 100%; height: 100%; object-fit: cover; }
.report-pdf-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #c0392b; font-size: 28px; }
.report-info { padding: 14px; }
.report-year { color: var(--accent); font-weight: 700; font-size: 13px; }
.report-info h4 { margin: 6px 0 0; color: var(--secondary); font-size: 15px; line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }

@media (max-width: 980px) {
    .grid, .grid-4 { grid-template-columns: repeat(2, 1fr); }
    .reports-row { grid-template-columns: repeat(3, 1fr); }
    .block { padding: 28px 0; }
}
@media (max-width: 640px) {
    .grid, .grid-4, .reports-row { grid-template-columns: repeat(2, 1fr); gap: 14px; }
}
@media (max-width: 420px) {
    .grid, .grid-4 { grid-template-columns: 1fr; }
}
</style>
