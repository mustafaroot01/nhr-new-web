<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { useLocale } from '../Composables/useLocale';

const { localeUrl, t } = useLocale();
const page = usePage();
const flash = computed(() => page.props.flash);

const form = useForm({ subject: '', details: '', name: '', contact: '', incident_location: '', incident_date: '' });
function submit() {
    form.post(localeUrl('/complaint'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="t('تقديم بلاغ', 'Submit a Report')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('تقديم بلاغ سري', 'Submit a Confidential Report') }}</h1>
            <div class="privacy-note">
                🔒 {{ t('كل البيانات مشفّرة وسرية. يمكنك تقديم البلاغ دون ذكر اسمك.', 'All data is encrypted and confidential. You may submit anonymously.') }}
            </div>

            <form @submit.prevent="submit" class="form">

                <div class="field">
                    <label>{{ t('موضوع البلاغ', 'Subject') }} *</label>
                    <input v-model="form.subject" type="text" :class="{ error: form.errors.subject }" />
                    <span v-if="form.errors.subject" class="err">{{ form.errors.subject }}</span>
                </div>
                <div class="field">
                    <label>{{ t('تفاصيل البلاغ', 'Details') }} *</label>
                    <textarea v-model="form.details" rows="6" :class="{ error: form.errors.details }"></textarea>
                    <span v-if="form.errors.details" class="err">{{ form.errors.details }}</span>
                </div>
                <div class="row">
                    <div class="field">
                        <label>{{ t('مكان الحادثة', 'Incident location') }}</label>
                        <input v-model="form.incident_location" type="text" />
                    </div>
                    <div class="field">
                        <label>{{ t('تاريخ الحادثة', 'Incident date') }}</label>
                        <input v-model="form.incident_date" type="date" />
                    </div>
                </div>
                <div class="optional-head">{{ t('بيانات اختيارية (للتواصل)', 'Optional (for follow-up)') }}</div>
                <div class="row">
                    <div class="field">
                        <label>{{ t('الاسم', 'Name') }}</label>
                        <input v-model="form.name" type="text" />
                    </div>
                    <div class="field">
                        <label>{{ t('وسيلة التواصل', 'Contact') }}</label>
                        <input v-model="form.contact" type="text" :placeholder="t('هاتف أو إيميل', 'Phone or email')" />
                    </div>
                </div>
                <button type="submit" class="btn btn-accent" :disabled="form.processing">
                    {{ form.processing ? t('جارِ الإرسال...', 'Sending...') : t('إرسال البلاغ', 'Submit Report') }}
                </button>
            </form>
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; max-width: 760px; margin: 0 auto; }
.privacy-note { background: #eff6ff; color: #1e40af; padding: 14px 18px; border-radius: 8px; margin-bottom: 24px; font-size: 14px; line-height: 1.6; }
.form { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; }
.field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
.optional-head { font-weight: 700; color: var(--muted); font-size: 14px; margin: 14px 0; padding-top: 14px; border-top: 1px dashed var(--border); }
label { font-weight: 600; font-size: 14px; color: var(--secondary); }
input, textarea { padding: 11px 14px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 15px; outline: none; background: var(--card); color: var(--text); }
input:focus, textarea:focus { border-color: var(--primary); }
input.error, textarea.error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 13px; }
.alert.success { background: #dcfce7; color: #166534; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px; font-weight: 600; }
@media (max-width: 680px) { .row { grid-template-columns: 1fr; } }
</style>
