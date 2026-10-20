<script setup>
import { usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const page = usePage();
const flash = computed(() => page.props.flash || {});
const toasts = ref([]);
let idCounter = 0;

function addToast(message, type = 'success') {
    const id = ++idCounter;
    toasts.value.push({ id, message, type });
    setTimeout(() => removeToast(id), 4500);
}

function removeToast(id) {
    toasts.value = toasts.value.filter(t => t.id !== id);
}

watch(flash, (f) => {
    if (f?.success) addToast(f.success, 'success');
    if (f?.error) addToast(f.error, 'error');
    if (f?.warning) addToast(f.warning, 'warning');
}, { immediate: true, deep: true });
</script>

<template>
    <Teleport to="body">
        <div class="toast-container">
            <TransitionGroup name="toast">
                <div v-for="toast in toasts" :key="toast.id" :class="['toast', toast.type]" @click="removeToast(toast.id)">
                    <svg v-if="toast.type === 'success'" class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M20 6L9 17l-5-5"/></svg>
                    <svg v-else-if="toast.type === 'error'" class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><circle cx="12" cy="12" r="10"/><path d="m15 9-6 6M9 9l6 6"/></svg>
                    <svg v-else class="toast-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><path d="M12 9v4m0 4h.01M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20z"/></svg>
                    <span class="toast-msg">{{ toast.message }}</span>
                    <button class="toast-close" @click.stop="removeToast(toast.id)">&times;</button>
                    <div class="toast-progress" :class="toast.type"></div>
                </div>
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-container {
    position: fixed;
    top: 24px;
    inset-inline-end: 24px;
    z-index: 10000;
    display: flex;
    flex-direction: column;
    gap: 10px;
    max-width: 400px;
    width: calc(100vw - 48px);
}
.toast {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 14px 18px;
    border-radius: 12px;
    background: #fff;
    box-shadow: 0 8px 30px rgba(0,0,0,.12);
    cursor: pointer;
    position: relative;
    overflow: hidden;
    border-inline-start: 4px solid;
}
.toast.success { border-color: #16a34a; }
.toast.error { border-color: #dc2626; }
.toast.warning { border-color: #f59e0b; }

.toast-icon { width: 22px; height: 22px; flex-shrink: 0; }
.toast.success .toast-icon { color: #16a34a; }
.toast.error .toast-icon { color: #dc2626; }
.toast.warning .toast-icon { color: #f59e0b; }

.toast-msg { flex: 1; font-size: 14px; font-weight: 600; color: var(--text, #1f2937); line-height: 1.5; }

.toast-close {
    background: none; border: none; font-size: 20px; color: #9ca3af;
    cursor: pointer; padding: 0 2px; line-height: 1;
}
.toast-close:hover { color: #4b5563; }

.toast-progress {
    position: absolute;
    bottom: 0; inset-inline-start: 0;
    height: 3px;
    animation: shrink 4.5s linear forwards;
}
.toast-progress.success { background: #16a34a; }
.toast-progress.error { background: #dc2626; }
.toast-progress.warning { background: #f59e0b; }

@keyframes shrink {
    from { width: 100%; }
    to { width: 0; }
}

.toast-enter-active { transition: all .35s ease-out; }
.toast-leave-active { transition: all .3s ease-in; }
.toast-enter-from { opacity: 0; transform: translateX(40px); }
.toast-leave-to { opacity: 0; transform: translateX(40px) scale(.95); }
</style>
