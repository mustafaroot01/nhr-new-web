<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { useLocale } from '../Composables/useLocale';

defineProps({ images: Array });
const { localeUrl, t } = useLocale();
const lightbox = ref(null);
</script>

<template>
    <Head :title="t('المكتبة الإعلامية', 'Media Library')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('المكتبة الإعلامية', 'Media Library') }}</h1>
            <div v-if="images.length" class="masonry">
                <figure v-for="(img, i) in images" :key="i" class="m-item">
                    <img :src="img.url" :alt="img.post_title" loading="lazy" @click="lightbox = img.url" />
                    <figcaption>
                        <Link :href="localeUrl('/posts/' + img.post_slug)">{{ img.post_title }}</Link>
                    </figcaption>
                </figure>
            </div>
            <p v-else class="empty">{{ t('لا توجد صور.', 'No images yet.') }}</p>
        </div>
        <div v-if="lightbox" class="lightbox" @click="lightbox = null">
            <img :src="lightbox" alt="" />
            <button class="lightbox-close">✕</button>
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; }
.masonry { columns: 4; column-gap: 14px; }
.m-item { margin: 0 0 14px; break-inside: avoid; border-radius: 10px; overflow: hidden; background: var(--card); border: 1px solid var(--border); }
.m-item img { width: 100%; display: block; cursor: zoom-in; transition: opacity .2s; }
.m-item img:hover { opacity: .9; }
.m-item figcaption { padding: 10px 12px; font-size: 13px; }
.m-item figcaption a { color: var(--secondary); text-decoration: none; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.m-item figcaption a:hover { color: var(--primary); }
.empty { text-align: center; color: var(--muted); padding: 60px 0; }
.lightbox { position: fixed; inset: 0; background: rgba(0,0,0,.9); display: flex; align-items: center; justify-content: center; z-index: 100; cursor: zoom-out; }
.lightbox img { max-width: 92%; max-height: 92%; border-radius: 8px; }
.lightbox-close { position: absolute; top: 24px; inset-inline-end: 24px; background: transparent; border: 0; color: #fff; font-size: 32px; cursor: pointer; }
@media (max-width: 980px) { .masonry { columns: 3; } }
@media (max-width: 680px) { .masonry { columns: 2; } }
</style>
