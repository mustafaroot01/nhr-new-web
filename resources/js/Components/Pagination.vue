<script setup>
import { Link } from '@inertiajs/vue3';
defineProps({ links: { type: Array, default: () => [] } });
function decodeLabel(label) {
    return label.replace(/&laquo;/g, '«').replace(/&raquo;/g, '»').replace(/&amp;/g, '&');
}
</script>

<template>
    <nav v-if="links.length > 3" class="pagination">
        <template v-for="(link, i) in links" :key="i">
            <Link
                v-if="link.url"
                :href="link.url"
                :class="['page-link', { active: link.active }]"
            >{{ decodeLabel(link.label) }}</Link>
            <span v-else class="page-link disabled">{{ decodeLabel(link.label) }}</span>
        </template>
    </nav>
</template>

<style scoped>
.pagination { display: flex; gap: 6px; justify-content: center; flex-wrap: wrap; margin: 40px 0; }
.page-link {
    min-width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;
    padding: 0 10px; border: 1px solid var(--border); border-radius: 8px;
    text-decoration: none; color: var(--text); background: var(--card); font-weight: 600;
}
.page-link.active { background: var(--primary); color: #fff; border-color: var(--primary); }
.page-link:hover:not(.active):not(.disabled) { border-color: var(--primary); color: var(--primary); }
.page-link.disabled { color: var(--muted); opacity: .5; }
</style>
