<script setup>
import { useLocale } from '../Composables/useLocale';

const props = defineProps({ url: { type: String, default: '' } });
const { t } = useLocale();
</script>

<template>
    <section v-if="url" class="fb-section">
        <div class="container">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24" fill="currentColor" class="st-icon"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07"/></svg>
                {{ t('تابعونا على فيسبوك', 'Follow us on Facebook') }}
            </h2>

            <a :href="url" target="_blank" rel="noopener" class="fb-card">
                <div class="fb-card-icon">
                    <svg viewBox="0 0 24 24" fill="currentColor"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07"/></svg>
                </div>
                <div class="fb-card-body">
                    <h3>{{ t('جمعية النضال لحقوق الإنسان', 'Al-Nidal Association for Human Rights') }}</h3>
                    <p>{{ t('تابع آخر أخبارنا ونشاطاتنا وفعالياتنا على صفحتنا في فيسبوك', 'Follow our latest news, activities and events on our Facebook page') }}</p>
                    <span class="fb-card-btn">
                        <svg viewBox="0 0 20 20" fill="currentColor" width="16" height="16"><path d="M10 3a1 1 0 0 1 1 1v5h5a1 1 0 1 1 0 2h-5v5a1 1 0 1 1-2 0v-5H4a1 1 0 1 1 0-2h5V4a1 1 0 0 1 1-1z"/></svg>
                        {{ t('تابع الصفحة', 'Follow Page') }}
                    </span>
                </div>
            </a>
        </div>
    </section>
</template>

<style scoped>
.fb-section { padding: 40px 0; }
.st-icon { width: 26px; height: 26px; color: #1877f2; }

.fb-card {
    display: flex;
    align-items: center;
    gap: 24px;
    max-width: 600px;
    margin: 0 auto;
    padding: 28px 32px;
    background: var(--card);
    border: 1px solid var(--border);
    border-radius: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,.06);
    text-decoration: none;
    transition: transform .2s, box-shadow .2s;
}
.fb-card:hover {
    transform: translateY(-3px);
    box-shadow: 0 8px 28px rgba(24,119,242,.15);
    border-color: #1877f2;
}

.fb-card-icon {
    flex-shrink: 0;
    width: 64px;
    height: 64px;
    background: #1877f2;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
}
.fb-card-icon svg {
    width: 32px;
    height: 32px;
    color: #fff;
}

.fb-card-body h3 {
    margin: 0 0 6px;
    font-size: 17px;
    font-weight: 700;
    color: var(--secondary);
}
.fb-card-body p {
    margin: 0 0 14px;
    font-size: 14px;
    color: var(--muted);
    line-height: 1.6;
}

.fb-card-btn {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    background: #1877f2;
    color: #fff;
    padding: 8px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    transition: background .2s;
}
.fb-card:hover .fb-card-btn {
    background: #1565c0;
}

html.dark .fb-card {
    border-color: #374151;
}
html.dark .fb-card:hover {
    border-color: #1877f2;
}
html.dark .fb-card-body h3 {
    color: #e5e7eb;
}

@media (max-width: 540px) {
    .fb-card {
        flex-direction: column;
        text-align: center;
        padding: 24px 20px;
        gap: 16px;
    }
    .fb-card-icon {
        width: 56px;
        height: 56px;
    }
    .fb-card-icon svg {
        width: 28px;
        height: 28px;
    }
}
</style>
