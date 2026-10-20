<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PostCard from '../Components/PostCard.vue';
import { useLocale } from '../Composables/useLocale';

defineProps({ query: String, results: Array, total: Number });
const { t } = useLocale();
</script>

<template>
    <Head :title="t('نتائج البحث', 'Search Results')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('نتائج البحث', 'Search Results') }}</h1>
            <p v-if="query" class="query-info">
                {{ t('نتائج البحث عن', 'Results for') }}: <strong>"{{ query }}"</strong> — {{ total }} {{ t('نتيجة', 'results') }}
            </p>
            <div v-if="results.length" class="grid">
                <PostCard v-for="p in results" :key="p.id" :post="p" />
            </div>
            <p v-else class="empty">{{ t('لا توجد نتائج مطابقة.', 'No matching results.') }}</p>
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; }
.query-info { color: var(--muted); margin-bottom: 24px; }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.empty { text-align: center; color: var(--muted); padding: 60px 0; }
@media (max-width: 980px) { .grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
</style>
