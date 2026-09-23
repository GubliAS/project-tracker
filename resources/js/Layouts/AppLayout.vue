<script setup>
import { computed, onMounted, onBeforeUnmount, nextTick, ref } from 'vue';
import { Head, router, usePage } from '@inertiajs/vue3';
import AppHeader from '@/Components/Partials/AppHeader.vue';
import AppSidebar from '@/Components/Partials/AppSidebar.vue';
import AppFooter from '@/Components/Partials/AppFooter.vue';

const props = defineProps({
    title: {
        type: String,
        default: '',
    },
});

const page = usePage();
const pageTitle = computed(() => props.title || page.props.title || '');
const flashMessage = computed(() => page.props.flash?.message || '');

const THEME_STORAGE_KEY = 'pm-theme';

const getStoredTheme = () => {
    try {
        return localStorage.getItem(THEME_STORAGE_KEY) === 'dark';
    } catch (e) {
        return false;
    }
};

const isDarkMode = ref(getStoredTheme());

const applyChromeTheme = (dark) => {
    const theme = dark ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.setAttribute('data-header-styles', theme);
    document.documentElement.setAttribute('data-menu-styles', theme);

    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme);
    } catch (e) {
        // localStorage unavailable (private mode, etc.) - theme just won't persist
    }
};

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    applyChromeTheme(isDarkMode.value);
};

const isMobileNavOpen = ref(false);

const toggleMobileNav = () => {
    isMobileNavOpen.value = !isMobileNavOpen.value;
};

const closeMobileNav = () => {
    isMobileNavOpen.value = false;
};

let stopNavigateListener = null;

onMounted(() => {
    document.documentElement.setAttribute('data-nav-layout', 'horizontal');
    document.documentElement.setAttribute('data-nav-style', 'menu-click');
    applyChromeTheme(isDarkMode.value);

    stopNavigateListener = router.on('navigate', closeMobileNav);

    nextTick(() => {
        setTimeout(() => {
            if (window.HSStaticMethods?.autoInit) {
                window.HSStaticMethods.autoInit();
            }
        }, 200);
    });
});

onBeforeUnmount(() => {
    stopNavigateListener?.();
});
</script>

<template>
    <div class="page" :class="{ dark: isDarkMode }">
        <Head :title="pageTitle" />
        <div class="pm-chrome">
            <AppHeader :dark="isDarkMode" :mobile-nav-open="isMobileNavOpen" @toggle-dark="toggleDarkMode" @toggle-mobile-nav="toggleMobileNav" />
            <AppSidebar :mobile-nav-open="isMobileNavOpen" @close-mobile-nav="closeMobileNav" />
        </div>

        <div class="main-content app-content" @click="closeMobileNav">
            <div class="container-fluid">
                <div v-if="flashMessage" class="pm-flash" role="status">{{ flashMessage }}</div>
                <slot />
            </div>
        </div>

        <AppFooter />
    </div>
</template>
