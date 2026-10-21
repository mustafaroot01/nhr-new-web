<script setup>
import { computed } from 'vue';
import { useLocale } from '../Composables/useLocale';

const props = defineProps({ url: { type: String, default: '' } });
const { locale, t } = useLocale();

const iframeSrc = computed(() => {
    if (!props.url) return '';
    const lang = locale.value === 'ar' ? 'ar_AR' : 'en_US';
    const encoded = encodeURIComponent(props.url);
    return `https://www.facebook.com/plugins/page.php?href=${encoded}&tabs=timeline&width=500&height=600&small_header=false&adapt_container_width=true&hide_cover=false&show_facepile=true&locale=${lang}&appId=1617227146802362`;
});
</script>

<template>
    <section v-if="url" class="fb-section">
        <div class="container">
            <h2 class="section-title">
                <svg viewBox="0 0 24 24" fill="currentColor" class="st-icon"><path d="M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07"/></svg>
                {{ t('آخر منشوراتنا على فيسبوك', 'Latest on Facebook') }}
            </h2>
            <div class="fb-frame">
                <iframe
                    :src="iframeSrc"
                    width="500"
                    height="600"
                    style="border:none; overflow:hidden;"
                    scrolling="no"
                    frameborder="0"
                    allowfullscreen="true"
                    allow="autoplay; clipboard-write; encrypted-media; picture-in-picture; web-share"
                    loading="lazy"
                ></iframe>
            </div>
            <a :href="url" target="_blank" rel="noopener" class="fb-visit">
                {{ t('زيارة صفحتنا على فيسبوك', 'Visit our Facebook page') }} ←
            </a>
        </div>
    </section>
</template>

<style scoped>
.fb-section { padding: 40px 0; }
.st-icon { width: 26px; height: 26px; color: #1877f2; }
.fb-frame {
    background: var(--card); border: 1px solid var(--border); border-radius: 12px;
    overflow: hidden; width: 500px; max-width: 100%; margin: 0 auto;
    box-shadow: 0 1px 4px rgba(0,0,0,.06);
}
.fb-frame iframe {
    width: 100%;
    display: block;
}
.fb-visit { display: block; text-align: center; margin-top: 16px; color: #1877f2; font-weight: 600; text-decoration: none; }
.fb-visit:hover { text-decoration: underline; }

@media (max-width: 540px) {
    .fb-frame { width: 100%; }
    .fb-frame iframe { height: 500px; }
}
</style>
