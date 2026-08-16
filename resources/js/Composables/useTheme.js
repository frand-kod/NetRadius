import { ref, watchEffect } from 'vue';

const isDark = ref(
    typeof localStorage !== 'undefined' && (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches))
);

watchEffect(() => {
    if (isDark.value) {
        document.documentElement.classList.add('dark');
        if (typeof localStorage !== 'undefined') localStorage.theme = 'dark';
    } else {
        document.documentElement.classList.remove('dark');
        if (typeof localStorage !== 'undefined') localStorage.theme = 'light';
    }
});

export function useTheme() {
    const toggleTheme = () => {
        isDark.value = !isDark.value;
    };

    return { isDark, toggleTheme };
}
