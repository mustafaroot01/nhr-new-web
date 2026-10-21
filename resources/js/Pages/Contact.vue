<script setup>
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '../Layouts/AppLayout.vue';
import { useLocale } from '../Composables/useLocale';

const { localeUrl, t } = useLocale();
const page = usePage();
const contact = computed(() => page.props.settings.contact);
const flash = computed(() => page.props.flash);

const form = useForm({ name: '', email: '', phone: '', subject: '', message: '' });
function submit() {
    form.post(localeUrl('/contact'), {
        preserveScroll: true,
        onSuccess: () => form.reset(),
    });
}
</script>

<template>
    <Head :title="t('اتصل بنا', 'Contact Us')" />
    <AppLayout>
        <div class="container page">
            <h1 class="section-title">{{ t('اتصل بنا', 'Contact Us') }}</h1>
            <div class="contact-grid">
                <div class="info-col">
                    <div v-if="t(contact.address_ar, contact.address_en)" class="info-item"><span>📍</span><p>{{ t(contact.address_ar, contact.address_en) }}</p></div>
                    <div v-if="contact.phone" class="info-item"><span>☎</span><p>{{ contact.phone }}</p></div>
                    <div v-if="contact.email" class="info-item"><span>✉</span><p>{{ contact.email }}</p></div>
                    <div v-if="t(contact.hours_ar, contact.hours_en)" class="info-item"><span>🕐</span><p>{{ t(contact.hours_ar, contact.hours_en) }}</p></div>
                    <div v-if="contact.maps" class="map" v-html="contact.maps"></div>
                </div>

                <form @submit.prevent="submit" class="form-col">

                    <div class="field">
                        <label>{{ t('الاسم', 'Name') }} *</label>
                        <input v-model="form.name" type="text" :class="{ error: form.errors.name }" />
                        <span v-if="form.errors.name" class="err">{{ form.errors.name }}</span>
                    </div>
                    <div class="row">
                        <div class="field">
                            <label>{{ t('البريد الإلكتروني', 'Email') }} *</label>
                            <input v-model="form.email" type="email" :class="{ error: form.errors.email }" />
                            <span v-if="form.errors.email" class="err">{{ form.errors.email }}</span>
                        </div>
                        <div class="field">
                            <label>{{ t('الهاتف', 'Phone') }}</label>
                            <input v-model="form.phone" type="text" />
                        </div>
                    </div>
                    <div class="field">
                        <label>{{ t('الموضوع', 'Subject') }} *</label>
                        <input v-model="form.subject" type="text" :class="{ error: form.errors.subject }" />
                        <span v-if="form.errors.subject" class="err">{{ form.errors.subject }}</span>
                    </div>
                    <div class="field">
                        <label>{{ t('الرسالة', 'Message') }} *</label>
                        <textarea v-model="form.message" rows="5" :class="{ error: form.errors.message }"></textarea>
                        <span v-if="form.errors.message" class="err">{{ form.errors.message }}</span>
                    </div>
                    <button type="submit" class="btn" :disabled="form.processing">
                        {{ form.processing ? t('جارِ الإرسال...', 'Sending...') : t('إرسال', 'Send') }}
                    </button>
                </form>
            </div>
        </div>
    </AppLayout>
</template>

<style scoped>
.page { padding: 32px 0; }
.contact-grid { display: grid; grid-template-columns: 1fr 1.4fr; gap: 40px; }
.info-item { display: flex; gap: 14px; align-items: flex-start; margin-bottom: 18px; }
.info-item span { font-size: 22px; }
.info-item p { margin: 0; line-height: 1.7; }
.map { margin-top: 20px; border-radius: 10px; overflow: hidden; }
.map :deep(iframe) { width: 100%; border: 0; border-radius: 10px; }
.form-col { background: var(--card); border: 1px solid var(--border); border-radius: 12px; padding: 28px; }
.field { margin-bottom: 16px; display: flex; flex-direction: column; gap: 6px; }
.row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
label { font-weight: 600; font-size: 14px; color: var(--secondary); }
input, textarea { padding: 11px 14px; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 15px; outline: none; background: var(--card); color: var(--text); }
input:focus, textarea:focus { border-color: var(--primary); }
input.error, textarea.error { border-color: #dc2626; }
.err { color: #dc2626; font-size: 13px; }
.alert.success { background: #dcfce7; color: #166534; padding: 14px 18px; border-radius: 8px; margin-bottom: 18px; }
@media (max-width: 820px) { .contact-grid { grid-template-columns: 1fr; } .row { grid-template-columns: 1fr; } }
</style>
