<script setup>
import { router } from '@inertiajs/vue3';
import { ref, onMounted, onBeforeUnmount } from 'vue';

const progress = ref(0);
const visible = ref(false);
let trickle = null;

function start() {
    progress.value = 0;
    visible.value = true;
    trickle = setInterval(() => {
        progress.value = Math.min(progress.value + (90 - progress.value) * 0.1, 90);
    }, 200);
}

function finish() {
    clearInterval(trickle);
    progress.value = 100;
    setTimeout(() => {
        visible.value = false;
        progress.value = 0;
    }, 300);
}

let removeStart, removeFinish;

onMounted(() => {
    removeStart = router.on('start', start);
    removeFinish = router.on('finish', finish);
});

onBeforeUnmount(() => {
    removeStart?.();
    removeFinish?.();
    clearInterval(trickle);
});
</script>

<template>
    <Transition name="loading">
        <div v-if="visible" class="loading-bar" :style="{ width: progress + '%' }"></div>
    </Transition>
</template>

<style scoped>
.loading-bar {
    position: fixed;
    top: 0;
    inset-inline-start: 0;
    height: 3px;
    background: linear-gradient(90deg, var(--accent), var(--primary));
    z-index: 9999;
    transition: width .2s ease;
    box-shadow: 0 0 8px var(--accent);
}
.loading-enter-active { transition: opacity .1s; }
.loading-leave-active { transition: opacity .4s; }
.loading-enter-from, .loading-leave-to { opacity: 0; }
</style>
