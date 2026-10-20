<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { useLocale } from '../Composables/useLocale';

const page = usePage();
const { localeUrl, t } = useLocale();

const settings = computed(() => page.props.settings);
const siteName = computed(() => t(settings.value.site_name_ar, settings.value.site_name_en));
const copyright = computed(() => t(settings.value.footer.copyright_ar, settings.value.footer.copyright_en));
const contact = computed(() => settings.value.contact);
const social = computed(() => settings.value.social || {});

const year = new Date().getFullYear();

const links = computed(() => [
    { label: t('الرئيسية', 'Home'), href: localeUrl('/') },
    { label: t('الأخبار', 'News'), href: localeUrl('/posts') },
    { label: t('التقارير', 'Reports'), href: localeUrl('/reports') },
    { label: t('المكتبة الإعلامية', 'Media'), href: localeUrl('/media') },
    { label: t('الأسئلة الشائعة', 'FAQ'), href: localeUrl('/faq') },
    { label: t('تقديم بلاغ', 'Report'), href: localeUrl('/complaint') },
    { label: t('اتصل بنا', 'Contact'), href: localeUrl('/contact') },
]);

// SVG path per social network
const icons = {
    facebook: 'M24 12.07C24 5.4 18.63 0 12 0S0 5.4 0 12.07C0 18.1 4.39 23.1 10.13 24v-8.44H7.08v-3.49h3.05V9.41c0-3.02 1.79-4.69 4.53-4.69 1.31 0 2.68.24 2.68.24v2.97h-1.5c-1.49 0-1.96.93-1.96 1.89v2.25h3.33l-.53 3.49h-2.8V24C19.61 23.1 24 18.1 24 12.07',
    twitter: 'M18.9 1.15h3.68l-8.04 9.19L24 22.85h-7.41l-5.8-7.58-6.64 7.58H.46l8.6-9.83L0 1.15h7.59l5.24 6.93zm-1.29 19.5h2.04L6.48 3.24H4.3z',
    instagram: 'M12 2.16c3.2 0 3.58.01 4.85.07 1.17.05 1.8.25 2.23.41.56.22.96.48 1.38.9.42.42.68.82.9 1.38.16.42.36 1.06.41 2.23.06 1.27.07 1.65.07 4.85s-.01 3.58-.07 4.85c-.05 1.17-.25 1.8-.41 2.23-.22.56-.48.96-.9 1.38-.42.42-.82.68-1.38.9-.42.16-1.06.36-2.23.41-1.27.06-1.65.07-4.85.07s-3.58-.01-4.85-.07c-1.17-.05-1.8-.25-2.23-.41-.56-.22-.96-.48-1.38-.9-.42-.42-.68-.82-.9-1.38-.16-.42-.36-1.06-.41-2.23-.06-1.27-.07-1.65-.07-4.85s.01-3.58.07-4.85c.05-1.17.25-1.8.41-2.23.22-.56.48-.96.9-1.38.42-.42.82-.68 1.38-.9.42-.16 1.06-.36 2.23-.41 1.27-.06 1.65-.07 4.85-.07M12 0C8.74 0 8.33.01 7.05.07 5.78.13 4.9.33 4.14.63c-.79.3-1.46.72-2.12 1.38C1.36 2.67.94 3.34.63 4.14.33 4.9.13 5.78.07 7.05.01 8.33 0 8.74 0 12s.01 3.67.07 4.95c.06 1.27.26 2.15.56 2.91.3.79.72 1.46 1.38 2.12.66.66 1.33 1.08 2.12 1.38.76.3 1.64.5 2.91.56C8.33 23.99 8.74 24 12 24s3.67-.01 4.95-.07c1.27-.06 2.15-.26 2.91-.56.79-.3 1.46-.72 2.12-1.38.66-.66 1.08-1.33 1.38-2.12.3-.76.5-1.64.56-2.91.06-1.28.07-1.69.07-4.95s-.01-3.67-.07-4.95c-.06-1.27-.26-2.15-.56-2.91-.3-.79-.72-1.46-1.38-2.12-.66-.66-1.33-1.08-2.12-1.38-.76-.3-1.64-.5-2.91-.56C15.67.01 15.26 0 12 0zm0 5.84a6.16 6.16 0 1 0 0 12.32 6.16 6.16 0 0 0 0-12.32zM12 16a4 4 0 1 1 0-8 4 4 0 0 1 0 8zm6.4-10.85a1.44 1.44 0 1 0 0 2.88 1.44 1.44 0 0 0 0-2.88z',
    youtube: 'M23.5 6.2a3.02 3.02 0 0 0-2.12-2.14C19.5 3.55 12 3.55 12 3.55s-7.5 0-9.38.51A3.02 3.02 0 0 0 .5 6.2C0 8.09 0 12 0 12s0 3.91.5 5.8a3.02 3.02 0 0 0 2.12 2.14c1.88.51 9.38.51 9.38.51s7.5 0 9.38-.51a3.02 3.02 0 0 0 2.12-2.14C24 15.91 24 12 24 12s0-3.91-.5-5.8zM9.6 15.6V8.4l6.2 3.6-6.2 3.6z',
    telegram: 'M23.95 4.57l-3.62 17.06c-.27 1.2-.98 1.5-1.99.93l-5.5-4.05-2.65 2.55c-.3.3-.54.54-1.1.54l.4-5.56L19.5 6.1c.44-.39-.1-.6-.68-.22L6.4 13.84l-5.43-1.7C-.2 11.8-.2 11 1.28 10.4L22.4 2.26c.98-.36 1.84.22 1.55 2.31',
};
const socialList = computed(() => Object.entries(social.value).filter(([k, v]) => v && icons[k]));
const hasContact = computed(() => {
    const c = contact.value;
    return t(c.address_ar, c.address_en) || c.phone || c.email || t(c.hours_ar, c.hours_en);
});
</script>

<template>
    <footer class="site-footer">
        <div class="container footer-grid">
            <!-- Brand -->
            <div class="footer-about">
                <img
                    v-if="settings.logo"
                    :src="settings.logo"
                    :alt="siteName"
                    class="footer-logo-img"
                />
                <h3>{{ siteName }}</h3>
                <p>{{ t(settings.site_description_ar, settings.site_description_en) }}</p>
                <div v-if="socialList.length" class="socials">
                    <a v-for="[key, url] in socialList" :key="key" :href="url" target="_blank" rel="noopener" class="social-link" :title="key">
                        <svg viewBox="0 0 24 24" fill="currentColor"><path :d="icons[key]" /></svg>
                    </a>
                </div>
            </div>

            <!-- Quick links -->
            <div class="footer-col">
                <h4>{{ t('روابط سريعة', 'Quick Links') }}</h4>
                <ul>
                    <li v-for="l in links" :key="l.href"><Link :href="l.href">{{ l.label }}</Link></li>
                </ul>
            </div>

            <!-- Contact -->
            <div class="footer-col">
                <h4>{{ t('تواصل معنا', 'Contact Us') }}</h4>
                <ul v-if="hasContact" class="contact-list">
                    <li v-if="t(contact.address_ar, contact.address_en)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0 1 18 0z"/><circle cx="12" cy="10" r="3"/></svg>
                        <span>{{ t(contact.address_ar, contact.address_en) }}</span>
                    </li>
                    <li v-if="contact.phone">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.13.96.36 1.9.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.9.34 1.85.57 2.81.7A2 2 0 0 1 22 16.92z"/></svg>
                        <a :href="'tel:' + contact.phone" dir="ltr">{{ contact.phone }}</a>
                    </li>
                    <li v-if="contact.email">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 5L2 7"/></svg>
                        <a :href="'mailto:' + contact.email">{{ contact.email }}</a>
                    </li>
                    <li v-if="t(contact.hours_ar, contact.hours_en)">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                        <span>{{ t(contact.hours_ar, contact.hours_en) }}</span>
                    </li>
                </ul>
                <p v-else class="contact-empty">{{ t('راسلنا عبر نموذج الاتصال', 'Reach us via the contact form') }}</p>
                <Link :href="localeUrl('/contact')" class="contact-btn">{{ t('أرسل رسالة', 'Send a message') }}</Link>
            </div>
        </div>
        <div class="footer-bottom">
            <div class="container">{{ copyright || (t('جميع الحقوق محفوظة', 'All rights reserved') + ' © ' + year + ' ' + siteName) }}</div>
        </div>
    </footer>
</template>

<style scoped>
.site-footer { background: var(--dark); color: #cbd5e1; margin-top: 60px; }
.footer-grid { display: grid; grid-template-columns: 1.7fr 1fr 1.3fr; gap: 48px; padding: 52px 0 36px; }
.footer-logo-img { width: 90px; height: 90px; object-fit: contain; margin-bottom: 10px; }
.footer-about h3 { color: #fff; font-size: 19px; margin: 0 0 10px; }
.footer-about p { line-height: 1.8; font-size: 14px; margin: 0; max-width: 360px; }
.socials { display: flex; gap: 10px; margin-top: 20px; flex-wrap: wrap; }
.social-link {
    width: 40px; height: 40px; border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    background: rgba(255,255,255,.08); color: #cbd5e1; transition: all .2s;
}
.social-link svg { width: 18px; height: 18px; }
.social-link:hover { background: var(--accent); color: #fff; transform: translateY(-2px); }

.footer-col h4 { color: #fff; font-size: 16px; margin: 0 0 18px; position: relative; padding-bottom: 10px; }
.footer-col h4::after { content: ''; position: absolute; bottom: 0; inset-inline-start: 0; width: 36px; height: 3px; background: var(--accent); border-radius: 2px; }
.footer-col ul { list-style: none; margin: 0; padding: 0; }
.footer-col li { margin-bottom: 10px; }
.footer-col a, .footer-col :deep(a) { color: #cbd5e1; text-decoration: none; font-size: 14px; transition: color .2s; }
.footer-col a:hover, .footer-col :deep(a):hover { color: var(--accent); }

.contact-list li { display: flex; align-items: flex-start; gap: 10px; line-height: 1.6; }
.contact-list svg { width: 17px; height: 17px; margin-top: 2px; color: var(--accent); flex-shrink: 0; }
.contact-list span { font-size: 14px; }
.contact-empty { font-size: 14px; color: #94a3b8; margin: 0 0 16px; }
.contact-btn {
    display: inline-block; margin-top: 16px; background: var(--accent); color: #fff !important;
    padding: 9px 20px; border-radius: 8px; font-weight: 600; font-size: 14px; text-decoration: none;
}
.contact-btn:hover { background: #a87a16; }

.footer-bottom { border-top: 1px solid rgba(255,255,255,.1); padding: 18px 0; text-align: center; font-size: 13px; color: #94a3b8; }

@media (max-width: 860px) {
    .footer-grid { grid-template-columns: 1fr; gap: 32px; text-align: center; }
    .footer-about p { margin-inline: auto; }
    .footer-logo-img { margin-inline: auto; }
    .socials { justify-content: center; }
    .footer-col h4::after { inset-inline-start: 50%; transform: translateX(-50%); }
    .contact-list li { justify-content: center; text-align: start; }
}
</style>
