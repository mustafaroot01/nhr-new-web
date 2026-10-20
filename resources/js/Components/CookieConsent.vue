<script setup>
import { ref, onMounted } from 'vue';
import { useLocale } from '../Composables/useLocale';

const { t } = useLocale();
const visible = ref(false);
const showDetails = ref(false);

// Cookie categories
const consent = ref({
    necessary: true,   // Always on — session, CSRF, locale
    analytics: false,   // Google Analytics
    marketing: false,   // Facebook Pixel, ads (future)
});

const COOKIE_KEY = 'nahr_cookie_consent';
const COOKIE_DAYS = 365;

function getConsent() {
    try {
        const raw = localStorage.getItem(COOKIE_KEY);
        return raw ? JSON.parse(raw) : null;
    } catch { return null; }
}

function saveConsent(choices) {
    try {
        const data = { ...choices, timestamp: Date.now() };
        localStorage.setItem(COOKIE_KEY, JSON.stringify(data));
    } catch { /* private browsing */ }
}

function acceptAll() {
    consent.value.analytics = true;
    consent.value.marketing = true;
    saveConsent(consent.value);
    visible.value = false;
    applyConsent(consent.value);
}

function acceptSelected() {
    saveConsent(consent.value);
    visible.value = false;
    applyConsent(consent.value);
}

function rejectOptional() {
    consent.value.analytics = false;
    consent.value.marketing = false;
    saveConsent(consent.value);
    visible.value = false;
    applyConsent(consent.value);
}

/**
 * Load or block third-party scripts based on consent.
 */
function applyConsent(c) {
    if (c.analytics) {
        loadGoogleAnalytics();
    }
    // Future: if (c.marketing) { loadFacebookPixel(); }
}

function loadGoogleAnalytics() {
    // Only load if GA ID is set (injected from settings via a meta tag or props)
    const gaId = document.querySelector('meta[name="ga-id"]')?.content;
    if (!gaId || document.getElementById('ga-script')) return;

    const s = document.createElement('script');
    s.id = 'ga-script';
    s.async = true;
    s.src = `https://www.googletagmanager.com/gtag/js?id=${gaId}`;
    document.head.appendChild(s);

    window.dataLayer = window.dataLayer || [];
    function gtag() { window.dataLayer.push(arguments); }
    gtag('js', new Date());
    gtag('config', gaId, { anonymize_ip: true });
}

onMounted(() => {
    const saved = getConsent();
    if (saved) {
        consent.value = { necessary: true, analytics: !!saved.analytics, marketing: !!saved.marketing };
        applyConsent(consent.value);
    } else {
        // First visit — show the banner after a short delay
        setTimeout(() => { visible.value = true; }, 1200);
    }
});
</script>

<template>
    <transition name="consent-slide">
        <div v-if="visible" class="cookie-banner" role="dialog" aria-label="Cookie consent">
            <div class="cookie-inner container">
                <div class="cookie-text">
                    <svg class="cookie-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"/>
                        <circle cx="8" cy="9" r="1.2" fill="currentColor"/>
                        <circle cx="15" cy="8" r="1" fill="currentColor"/>
                        <circle cx="10" cy="14" r="1.1" fill="currentColor"/>
                        <circle cx="16" cy="13" r="0.9" fill="currentColor"/>
                        <circle cx="13" cy="17" r="1" fill="currentColor"/>
                    </svg>
                    <div>
                        <p class="cookie-msg">
                            {{ t(
                                'نستخدم ملفات تعريف الارتباط (كوكيز) لتحسين تجربتك في الموقع. الكوكيز الأساسية ضرورية لعمل الموقع، بينما كوكيز التحليل تساعدنا على فهم كيفية استخدام الموقع.',
                                'We use cookies to improve your experience. Essential cookies are required for the site to function, while analytics cookies help us understand how the site is used.'
                            ) }}
                        </p>
                    </div>
                </div>

                <!-- Expandable details -->
                <transition name="expand">
                    <div v-if="showDetails" class="cookie-details">
                        <label class="cookie-opt disabled">
                            <input type="checkbox" :checked="true" disabled />
                            <div>
                                <strong>{{ t('ضرورية', 'Essential') }}</strong>
                                <span>{{ t('جلسة المتصفح، الحماية، تفضيل اللغة', 'Session, CSRF protection, language preference') }}</span>
                            </div>
                        </label>
                        <label class="cookie-opt">
                            <input type="checkbox" v-model="consent.analytics" />
                            <div>
                                <strong>{{ t('تحليلات', 'Analytics') }}</strong>
                                <span>{{ t('Google Analytics — تساعدنا نعرف عدد الزوار والصفحات الأكثر زيارة', 'Google Analytics — helps us understand visitor count and popular pages') }}</span>
                            </div>
                        </label>
                        <label class="cookie-opt">
                            <input type="checkbox" v-model="consent.marketing" />
                            <div>
                                <strong>{{ t('تسويقية', 'Marketing') }}</strong>
                                <span>{{ t('كوكيز من خدمات خارجية مثل فيسبوك (غير مفعّلة حالياً)', 'Third-party cookies like Facebook (not currently active)') }}</span>
                            </div>
                        </label>
                    </div>
                </transition>

                <div class="cookie-actions">
                    <button class="cbtn cbtn-outline" @click="showDetails = !showDetails">
                        {{ showDetails ? t('إخفاء التفاصيل', 'Hide details') : t('تخصيص', 'Customize') }}
                    </button>
                    <button v-if="showDetails" class="cbtn cbtn-secondary" @click="acceptSelected">
                        {{ t('حفظ الاختيارات', 'Save choices') }}
                    </button>
                    <button class="cbtn cbtn-ghost" @click="rejectOptional">
                        {{ t('رفض الاختيارية', 'Reject optional') }}
                    </button>
                    <button class="cbtn cbtn-primary" @click="acceptAll">
                        {{ t('قبول الكل', 'Accept all') }}
                    </button>
                </div>
            </div>
        </div>
    </transition>
</template>

<style scoped>
.cookie-banner {
    position: fixed; bottom: 0; inset-inline: 0; z-index: 9000;
    background: #fff; border-top: 1px solid var(--border);
    box-shadow: 0 -4px 24px rgba(0,0,0,.1);
    padding: 20px 0;
}
.cookie-inner { display: flex; flex-direction: column; gap: 16px; }

.cookie-text { display: flex; align-items: flex-start; gap: 14px; }
.cookie-icon { width: 32px; height: 32px; color: var(--accent); flex-shrink: 0; margin-top: 2px; }
.cookie-msg { margin: 0; font-size: 14px; line-height: 1.7; color: var(--text); max-width: 720px; }

/* Details panel */
.cookie-details {
    display: flex; flex-direction: column; gap: 10px;
    padding: 16px; background: var(--bg); border-radius: 10px;
    border: 1px solid var(--border);
}
.cookie-opt {
    display: flex; align-items: flex-start; gap: 10px; cursor: pointer;
    padding: 8px 10px; border-radius: 8px; transition: background .15s;
}
.cookie-opt:hover { background: rgba(0,0,0,.03); }
.cookie-opt.disabled { opacity: .7; cursor: default; }
.cookie-opt input[type="checkbox"] {
    width: 18px; height: 18px; margin-top: 3px; flex-shrink: 0;
    accent-color: var(--primary); cursor: inherit;
}
.cookie-opt strong { display: block; font-size: 14px; color: var(--secondary); }
.cookie-opt span { display: block; font-size: 12px; color: var(--muted); line-height: 1.5; margin-top: 2px; }

/* Buttons */
.cookie-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.cbtn {
    padding: 9px 20px; border-radius: 8px; font-size: 14px; font-weight: 600;
    cursor: pointer; border: 0; font-family: inherit; transition: all .2s;
    white-space: nowrap;
}
.cbtn-primary { background: var(--primary); color: #fff; }
.cbtn-primary:hover { background: var(--secondary); }
.cbtn-secondary { background: var(--accent); color: #fff; }
.cbtn-secondary:hover { background: #a87a16; }
.cbtn-ghost { background: transparent; color: var(--muted); }
.cbtn-ghost:hover { color: var(--text); background: var(--bg); }
.cbtn-outline { background: transparent; color: var(--primary); border: 1.5px solid var(--border); }
.cbtn-outline:hover { border-color: var(--primary); }

/* Animations */
.consent-slide-enter-active { transition: transform .4s cubic-bezier(.4,0,.2,1), opacity .4s ease; }
.consent-slide-leave-active { transition: transform .3s ease, opacity .3s ease; }
.consent-slide-enter-from, .consent-slide-leave-to { transform: translateY(100%); opacity: 0; }

.expand-enter-active, .expand-leave-active { transition: all .25s ease; overflow: hidden; }
.expand-enter-from, .expand-leave-to { opacity: 0; max-height: 0; padding-top: 0; padding-bottom: 0; }
.expand-enter-to, .expand-leave-from { max-height: 400px; }

/* Dark mode */
html.dark .cookie-banner { background: #1f2937; border-color: #374151; }
html.dark .cookie-msg { color: #e5e7eb; }
html.dark .cookie-details { background: #111827; border-color: #374151; }
html.dark .cookie-opt:hover { background: rgba(255,255,255,.04); }
html.dark .cookie-opt strong { color: #e5e7eb; }
html.dark .cookie-opt span { color: #9ca3af; }
html.dark .cbtn-outline { border-color: #374151; color: #93c5fd; }
html.dark .cbtn-outline:hover { border-color: #93c5fd; }
html.dark .cbtn-ghost { color: #9ca3af; }
html.dark .cbtn-ghost:hover { color: #e5e7eb; background: #374151; }

@media (max-width: 640px) {
    .cookie-banner { padding: 16px 0; }
    .cookie-icon { display: none; }
    .cookie-actions { justify-content: stretch; }
    .cbtn { flex: 1; text-align: center; padding: 10px 12px; font-size: 13px; }
    .cookie-msg { font-size: 13px; }
}
</style>
