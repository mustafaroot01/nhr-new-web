<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';

defineProps({ page: Object });
</script>

<template>
    <Head :title="page.meta_title || page.title">
        <meta name="description" :content="page.meta_description" />
    </Head>
    <AppLayout>
        <!-- Hero banner -->
        <div class="page-hero">
            <div class="container">
                <h1>{{ page.title }}</h1>
                <p v-if="page.subtitle" class="page-subtitle">{{ page.subtitle }}</p>
            </div>
        </div>

        <div class="container page-body">
            <div v-if="page.image" class="page-image-wrap">
                <img :src="page.image" :alt="page.title" class="page-image" />
            </div>

            <div class="page-content" v-html="page.body"></div>

            <div v-if="page.gallery && page.gallery.length" class="page-gallery">
                <div v-for="(img, i) in page.gallery" :key="i" class="gallery-item">
                    <img :src="img.url" :alt="img.alt || page.title" loading="lazy" />
                </div>
            </div>

            <div v-if="page.attachments && page.attachments.length" class="page-attachments">
                <div v-for="(file, i) in page.attachments" :key="i" class="attachment-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <a :href="file.url" target="_blank" rel="noopener">
                        {{ file.name }}
                        <span class="att-meta">{{ file.type }} · {{ file.size }}</span>
                    </a>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page-hero {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    color: #fff;
    padding: 52px 16px 44px;
    text-align: center;
}
.page-hero h1 {
    font-size: clamp(26px, 5vw, 38px);
    font-weight: 800;
    margin: 0 0 8px;
    letter-spacing: -.02em;
}
.page-subtitle {
    font-size: 16px;
    opacity: .85;
    margin: 0;
    max-width: 600px;
    margin-inline: auto;
    line-height: 1.7;
}

.page-body {
    max-width: 860px;
    padding: 40px 16px 64px;
}

.page-image-wrap {
    margin-bottom: 32px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
.page-image {
    width: 100%;
    display: block;
}

.page-content {
    line-height: 2;
    color: var(--text);
    font-size: 16px;
}
.page-content :deep(h2),
.page-content :deep(h3) {
    color: var(--secondary);
    margin: 32px 0 14px;
    font-weight: 700;
}
.page-content :deep(h2) { font-size: 22px; }
.page-content :deep(h3) { font-size: 18px; }

.page-content :deep(p) {
    margin: 0 0 18px;
}

.page-content :deep(p:first-child) {
    font-size: 17px;
    line-height: 2;
    color: var(--secondary);
    font-weight: 500;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--accent);
    margin-bottom: 28px;
}

.page-content :deep(img) {
    border-radius: 10px;
    margin: 18px 0;
}
.page-content :deep(ul),
.page-content :deep(ol) {
    padding-inline-start: 24px;
    margin: 0 0 18px;
}
.page-content :deep(li) {
    margin-bottom: 8px;
}
.page-content :deep(a) {
    color: var(--primary);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.page-content :deep(blockquote) {
    border-inline-start: 4px solid var(--accent);
    margin: 24px 0;
    padding: 16px 20px;
    background: rgba(201, 148, 30, .06);
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: var(--secondary);
}

.page-gallery {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(220px, 1fr));
    gap: 12px;
    margin-top: 36px;
}
.gallery-item img {
    width: 100%;
    border-radius: 10px;
    aspect-ratio: 4/3;
    object-fit: cover;
}

.page-attachments {
    margin-top: 36px;
    display: flex;
    flex-direction: column;
    gap: 10px;
}
.attachment-item {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    background: var(--bg);
    border: 1px solid var(--border);
    border-radius: 10px;
    transition: border-color .2s;
}
.attachment-item:hover { border-color: var(--primary); }
.attachment-item svg {
    width: 22px; height: 22px;
    color: var(--accent); flex-shrink: 0;
}
.attachment-item a {
    color: var(--text); text-decoration: none;
    font-weight: 600; font-size: 14px;
}
.att-meta {
    display: block; font-size: 12px;
    color: var(--muted); font-weight: 400; margin-top: 2px;
}

html.dark .page-hero {
    background: linear-gradient(135deg, #1a2332 0%, var(--primary) 100%);
}
html.dark .page-image-wrap {
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
}
html.dark .page-content :deep(p:first-child) {
    color: #e5e7eb;
    border-color: var(--accent);
}
html.dark .page-content :deep(blockquote) {
    background: rgba(201, 148, 30, .1);
}

@media (max-width: 640px) {
    .page-hero { padding: 36px 16px 32px; }
    .page-body { padding: 28px 16px 48px; }
    .page-content { font-size: 15px; }
    .page-content :deep(p:first-child) { font-size: 15px; }
    .page-gallery { grid-template-columns: repeat(auto-fill, minmax(160px, 1fr)); }
}
</style>
