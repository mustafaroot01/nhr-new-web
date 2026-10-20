<script setup>
import { Head } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import PostCard from '../Components/PostCard.vue';
import Pagination from '../Components/Pagination.vue';
import { useLocale } from '../Composables/useLocale';

defineProps({ category: Object, posts: Object });
const { t } = useLocale();
</script>

<template>
    <Head :title="category.name" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ category.name }}</h1>
            <div v-if="posts.data.length" class="grid">
                <PostCard v-for="p in posts.data" :key="p.id" :post="p" />
            </div>
            <p v-else class="empty">{{ t('لا توجد مقالات في هذا القسم.', 'No articles in this category.') }}</p>
            <Pagination :links="posts.links" />
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; }
.grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.empty { text-align: center; color: var(--muted); padding: 60px 0; }
@media (max-width: 980px) { .grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .grid { grid-template-columns: 1fr; } }
</style>
