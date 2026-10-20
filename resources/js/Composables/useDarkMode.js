import { ref, watch } from 'vue';

const isDark = ref(false);

function init() {
    const saved = localStorage.getItem('theme');
    if (saved) {
        isDark.value = saved === 'dark';
    } else {
        isDark.value = window.matchMedia('(prefers-color-scheme: dark)').matches;
    }
    apply();
}

function apply() {
    document.documentElement.classList.toggle('dark', isDark.value);
}

function toggle() {
    isDark.value = !isDark.value;
    localStorage.setItem('theme', isDark.value ? 'dark' : 'light');
    apply();
}

watch(isDark, apply);

export function useDarkMode() {
    return { isDark, toggle, init };
}
