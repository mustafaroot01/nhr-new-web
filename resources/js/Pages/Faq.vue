<script setup>
import { Head } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { useLocale } from '../Composables/useLocale';

defineProps({ faqs: Array });
const { t } = useLocale();
const open = ref(0);
</script>

<template>
    <Head :title="t('الأسئلة الشائعة', 'FAQ')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('الأسئلة الشائعة', 'Frequently Asked Questions') }}</h1>
            <div v-if="faqs.length" class="faq-list">
                <div v-for="(f, i) in faqs" :key="i" :class="['faq', { open: open === i }]">
                    <button class="faq-q" @click="open = open === i ? -1 : i">
                        <span>{{ f.question }}</span>
                        <span class="icon">{{ open === i ? '−' : '+' }}</span>
                    </button>
                    <div v-show="open === i" class="faq-a">{{ f.answer }}</div>
                </div>
            </div>
            <p v-else class="empty">{{ t('لا توجد أسئلة.', 'No questions yet.') }}</p>
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; max-width: 820px; margin: 0 auto; }
.faq { background: var(--card); border: 1px solid var(--border); border-radius: 10px; margin-bottom: 12px; overflow: hidden; }
.faq.open { border-color: var(--primary); }
.faq-q { width: 100%; display: flex; justify-content: space-between; align-items: center; gap: 12px; padding: 18px 20px; background: transparent; border: 0; cursor: pointer; font-size: 17px; font-weight: 600; color: var(--secondary); text-align: start; }
.icon { color: var(--accent); font-size: 24px; font-weight: 700; flex-shrink: 0; }
.faq-a { padding: 0 20px 20px; color: var(--text); line-height: 1.8; }
.empty { text-align: center; color: var(--muted); padding: 60px 0; }
</style>
