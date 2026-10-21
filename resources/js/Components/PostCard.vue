<script setup>
import { Link } from '@inertiajs/vue3';
import { useLocale } from '../Composables/useLocale';

defineProps({ post: { type: Object, required: true } });
const { locale, localeUrl, t } = useLocale();

// Compact number so large view counts stay short (1,200 → "1.2K" / "1.2 ألف")
function formatViews(n) {
    const num = Number(n) || 0;
    try {
        return new Intl.NumberFormat(locale.value === 'ar' ? 'ar-EG' : 'en-US', {
            notation: 'compact',
            maximumFractionDigits: 1,
        }).format(num);
    } catch (e) {
        return num;
    }
}
</script>

<template>
    <article class="card">
        <Link :href="localeUrl('/posts/' + post.slug)" class="card-image">
            <img v-if="post.image" :src="post.image" :alt="post.title" loading="lazy" />
            <div v-else class="card-placeholder">
                <img src="/favicon.png" alt="" />
            </div>
            <span v-if="post.category" class="card-badge">{{ post.category.name }}</span>
        </Link>
        <div class="card-body">
            <Link :href="localeUrl('/posts/' + post.slug)" class="card-title">{{ post.title }}</Link>
            <p v-if="post.excerpt" class="card-excerpt">{{ post.excerpt }}</p>
            <div class="card-meta">
                <span v-if="post.date" class="meta-item">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    {{ post.date }}
                </span>
                <span class="meta-item views" :title="t('المشاهدات', 'Views')">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    <span class="views-num">{{ formatViews(post.views) }}</span>
                </span>
            </div>
        </div>
    </article>
</template>

<style scoped>
.card {
    background: var(--card); border-radius: 12px; overflow: hidden;
    box-shadow: 0 1px 4px rgba(0,0,0,.06); border: 1px solid var(--border);
    display: flex; flex-direction: column; transition: transform .2s, box-shadow .2s;
}
.card:hover { transform: translateY(-4px); box-shadow: 0 8px 24px rgba(0,0,0,.1); }
.card-image { position: relative; display: block; aspect-ratio: 16/10; overflow: hidden; background: var(--border); }
.card-image img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.card:hover .card-image img { transform: scale(1.05); }
.card-placeholder { width: 100%; height: 100%; display: flex; align-items: center; justify-content: center; opacity: .3; }
.card-placeholder img { width: 64px; height: 64px; }
.card-badge {
    position: absolute; top: 12px; inset-inline-start: 12px;
    background: var(--accent); color: #fff; font-size: 12px; font-weight: 600;
    padding: 4px 12px; border-radius: 20px;
}
.card-body { padding: 16px; display: flex; flex-direction: column; gap: 8px; flex: 1; }
.card-title {
    font-weight: 700; font-size: 17px; color: var(--secondary); text-decoration: none;
    line-height: 1.5; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.card-title:hover { color: var(--primary); }
.card-excerpt {
    color: var(--muted); font-size: 14px; margin: 0; flex: 1;
    display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden;
}
.card-meta { display: flex; justify-content: space-between; align-items: center; gap: 10px; color: var(--muted); font-size: 13px; padding-top: 8px; border-top: 1px solid var(--border); }
.meta-item { display: inline-flex; align-items: center; gap: 5px; white-space: nowrap; }
.meta-item svg { width: 15px; height: 15px; opacity: .75; flex-shrink: 0; }
.views { background: var(--bg); padding: 3px 9px; border-radius: 20px; }
.views-num { font-weight: 600; font-variant-numeric: tabular-nums; }
</style>
