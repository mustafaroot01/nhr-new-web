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
        <div class="about-hero">
            <div class="container">
                <h1>{{ page.title }}</h1>
            </div>
        </div>

        <div class="container about-body">
            <div v-if="page.image" class="about-image-wrap">
                <img :src="page.image" :alt="page.title" class="about-image" />
            </div>

            <div class="content" v-html="page.body"></div>
        </div>
    </AppLayout>
</template>

<style scoped>
.about-hero {
    background: linear-gradient(135deg, var(--secondary) 0%, var(--primary) 100%);
    color: #fff;
    padding: 52px 16px 44px;
    text-align: center;
}
.about-hero h1 {
    font-size: clamp(26px, 5vw, 38px);
    font-weight: 800;
    margin: 0;
    letter-spacing: -.02em;
}

.about-body {
    max-width: 860px;
    padding: 40px 16px 64px;
}

.about-image-wrap {
    margin-bottom: 32px;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.08);
}
.about-image {
    width: 100%;
    display: block;
}

.content {
    line-height: 2;
    color: var(--text);
    font-size: 16px;
}
.content :deep(h2),
.content :deep(h3) {
    color: var(--secondary);
    margin: 32px 0 14px;
    font-weight: 700;
}
.content :deep(h2) { font-size: 22px; }
.content :deep(h3) { font-size: 18px; }

.content :deep(p) {
    margin: 0 0 18px;
}

.content :deep(p:first-child) {
    font-size: 17px;
    line-height: 2;
    color: var(--secondary);
    font-weight: 500;
    padding-bottom: 20px;
    border-bottom: 2px solid var(--accent);
    margin-bottom: 28px;
}

.content :deep(img) {
    border-radius: 10px;
    margin: 18px 0;
}
.content :deep(ul),
.content :deep(ol) {
    padding-inline-start: 24px;
    margin: 0 0 18px;
}
.content :deep(li) {
    margin-bottom: 10px;
    line-height: 1.9;
}
.content :deep(a) {
    color: var(--primary);
    text-decoration: underline;
    text-underline-offset: 3px;
}
.content :deep(blockquote) {
    border-inline-start: 4px solid var(--accent);
    margin: 24px 0;
    padding: 16px 20px;
    background: rgba(201, 148, 30, .06);
    border-radius: 0 8px 8px 0;
    font-style: italic;
    color: var(--secondary);
}

html.dark .about-hero {
    background: linear-gradient(135deg, #1a2332 0%, var(--primary) 100%);
}
html.dark .about-image-wrap {
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
}
html.dark .content :deep(p:first-child) {
    color: #e5e7eb;
    border-color: var(--accent);
}

@media (max-width: 640px) {
    .about-hero { padding: 36px 16px 32px; }
    .about-body { padding: 28px 16px 48px; }
    .content { font-size: 15px; }
    .content :deep(p:first-child) { font-size: 15px; }
}
</style>
