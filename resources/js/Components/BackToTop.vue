<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';

const visible = ref(false);

function onScroll() {
    visible.value = window.scrollY > 400;
}

function scrollTop() {
    window.scrollTo({ top: 0, behavior: 'smooth' });
}

onMounted(() => window.addEventListener('scroll', onScroll, { passive: true }));
onBeforeUnmount(() => window.removeEventListener('scroll', onScroll));
</script>

<template>
    <Transition name="btt">
        <button v-show="visible" class="back-to-top" @click="scrollTop" aria-label="Back to top">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                <path d="M18 15l-6-6-6 6"/>
            </svg>
        </button>
    </Transition>
</template>

<style scoped>
.back-to-top {
    position: fixed;
    bottom: 32px;
    inset-inline-end: 32px;
    z-index: 900;
    width: 48px; height: 48px;
    border-radius: 50%;
    border: none;
    background: var(--primary);
    color: #fff;
    cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(0,0,0,.18);
    transition: background .2s, transform .2s, box-shadow .2s;
}
.back-to-top:hover {
    background: var(--secondary);
    transform: translateY(-3px);
    box-shadow: 0 6px 20px rgba(0,0,0,.25);
}
.back-to-top:active { transform: translateY(0); }

.btt-enter-active, .btt-leave-active { transition: opacity .3s, transform .3s; }
.btt-enter-from, .btt-leave-to { opacity: 0; transform: translateY(16px); }
</style>
