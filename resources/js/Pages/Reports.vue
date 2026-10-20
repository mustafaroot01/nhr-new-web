<script setup>
import { Head, Link } from '@inertiajs/vue3';
import AppLayout from '../Layouts/AppLayout.vue';
import Pagination from '../Components/Pagination.vue';
import { useLocale } from '../Composables/useLocale';

defineProps({ reports: Object, years: Array, activeYear: Number });
const { localeUrl, t } = useLocale();
</script>

<template>
    <Head :title="t('التقارير والإصدارات', 'Reports & Publications')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('التقارير والإصدارات', 'Reports & Publications') }}</h1>

            <div v-if="years.length" class="years">
                <Link :href="localeUrl('/reports')" :class="['year-chip', { active: !activeYear }]">{{ t('الكل', 'All') }}</Link>
                <Link v-for="y in years" :key="y" :href="localeUrl('/reports') + '?year=' + y" :class="['year-chip', { active: activeYear === y }]">{{ y }}</Link>
            </div>

            <div v-if="reports.data.length" class="reports-grid">
                <a v-for="r in reports.data" :key="r.id" :href="r.pdf || '#'" target="_blank" class="report">
                    <div class="cover">
                        <img v-if="r.cover" :src="r.cover" :alt="r.title" loading="lazy" />
                        <div v-else class="pdf-icon">PDF</div>
                        <span class="year">{{ r.year }}</span>
                    </div>
                    <div class="info">
                        <h3>{{ r.title }}</h3>
                        <p v-if="r.description">{{ r.description }}</p>
                        <span class="download">⬇ {{ t('تحميل PDF', 'Download PDF') }}</span>
                    </div>
                </a>
            </div>
            <p v-else class="empty">{{ t('لا توجد تقارير.', 'No reports found.') }}</p>
            <Pagination :links="reports.links" />
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; }
.years { display: flex; gap: 10px; flex-wrap: wrap; margin-bottom: 28px; }
.year-chip { padding: 7px 18px; border: 1px solid var(--border); border-radius: 20px; text-decoration: none; color: var(--text); background: var(--card); font-weight: 600; }
.year-chip.active, .year-chip:hover { background: var(--primary); color: #fff; border-color: var(--primary); }
.reports-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 24px; }
.report { display: flex; flex-direction: column; text-decoration: none; background: var(--card); border: 1px solid var(--border); border-radius: 12px; overflow: hidden; transition: transform .2s, box-shadow .2s; }
.report:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.cover { position: relative; aspect-ratio: 16/10; background: #eef1f4; }
.cover img { width: 100%; height: 100%; object-fit: cover; }
.pdf-icon { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; font-weight: 800; color: #c0392b; font-size: 32px; }
.year { position: absolute; top: 12px; inset-inline-start: 12px; background: var(--accent); color: #fff; font-weight: 700; padding: 3px 12px; border-radius: 16px; font-size: 13px; }
.info { padding: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.info h3 { margin: 0; color: var(--secondary); font-size: 17px; line-height: 1.5; }
.info p { margin: 0; color: var(--muted); font-size: 14px; flex: 1; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
.download { color: var(--primary); font-weight: 600; font-size: 14px; }
.empty { text-align: center; color: var(--muted); padding: 60px 0; }
@media (max-width: 980px) { .reports-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 560px) { .reports-grid { grid-template-columns: 1fr; } }
</style>
