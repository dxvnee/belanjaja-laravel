import { ref, onMounted } from 'vue';

const isDark = ref(false);

const initTheme = () => {
    if (typeof window !== 'undefined') {
        if (localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
            isDark.value = true;
            document.documentElement.classList.add('dark');
        } else {
            isDark.value = false;
            document.documentElement.classList.remove('dark');
        }
    }
};

const toggleDarkMode = () => {
    isDark.value = !isDark.value;

    if (isDark.value) {
        document.documentElement.classList.add('dark');
        localStorage.setItem('theme', 'dark'); 
    } else {
        document.documentElement.classList.remove('dark');
        localStorage.setItem('theme', 'light');
    }
};

export function useDarkMode() {
    onMounted(() => {
        initTheme();
    });

    return { isDark, toggleDarkMode, initTheme };
}