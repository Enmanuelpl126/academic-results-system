import { onMounted, ref } from 'vue';

type Appearance = 'light' | 'dark' | 'system';

export function updateTheme(_value: Appearance) {
    if (typeof window === 'undefined') {
        return;
    }
    // HARD-LOCK: Always force LIGHT theme
    document.documentElement.classList.remove('dark');
}

const setCookie = (name: string, value: string, days = 365) => {
    if (typeof document === 'undefined') {
        return;
    }

    const maxAge = days * 24 * 60 * 60;

    // Añade Secure en contexto HTTPS para reducir riesgos de exposición en tránsito.
    const isSecure = typeof window !== 'undefined' && window.location && window.location.protocol === 'https:';
    const secureAttr = isSecure ? ';Secure' : '';
    document.cookie = `${name}=${value};path=/;max-age=${maxAge};SameSite=Lax${secureAttr}`;
};

const mediaQuery = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return window.matchMedia('(prefers-color-scheme: dark)');
};

const getStoredAppearance = () => {
    if (typeof window === 'undefined') {
        return null;
    }

    return localStorage.getItem('appearance') as Appearance | null;
};

const handleSystemThemeChange = () => {
    const currentAppearance = getStoredAppearance();

    updateTheme(currentAppearance || 'system');
};

export function initializeTheme() {
    if (typeof window === 'undefined') {
        return;
    }

    // Persist explicit LIGHT preference and apply it
    try { localStorage.setItem('appearance', 'light'); } catch (_e) {}
    setCookie('appearance', 'light');
    updateTheme('light');
}

const appearance = ref<Appearance>('light');

export function useAppearance() {
    onMounted(() => {
        appearance.value = 'light';
        try { localStorage.setItem('appearance', 'light'); } catch (_e) {}
        setCookie('appearance', 'light');
    });

    function updateAppearance(_value: Appearance) {
        appearance.value = 'light';

        // Persist choice as light
        try { localStorage.setItem('appearance', 'light'); } catch (_e) {}
        setCookie('appearance', 'light');

        updateTheme('light');
    }

    return {
        appearance,
        updateAppearance,
    };
}
