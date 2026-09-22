<script setup>
import { computed, onMounted, nextTick, ref } from 'vue';
import { Head, usePage } from '@inertiajs/vue3';
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

const isDarkMode = ref(false);

const applyChromeTheme = (dark) => {
    const theme = dark ? 'dark' : 'light';
    document.documentElement.classList.toggle('dark', dark);
    document.documentElement.setAttribute('data-header-styles', theme);
    document.documentElement.setAttribute('data-menu-styles', theme);
};

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    applyChromeTheme(isDarkMode.value);
};

onMounted(() => {
    document.documentElement.setAttribute('data-nav-layout', 'horizontal');
    document.documentElement.setAttribute('data-nav-style', 'menu-click');
    applyChromeTheme(isDarkMode.value);

    nextTick(() => {
        setTimeout(() => {
            if (window.HSStaticMethods?.autoInit) {
                window.HSStaticMethods.autoInit();
            }
        }, 200);
    });
});
</script>

<template>
    <div class="page" :class="{ dark: isDarkMode }">
        <Head :title="pageTitle" />
        <AppHeader :dark="isDarkMode" @toggle-dark="toggleDarkMode" />
        <AppSidebar />

        <div class="main-content app-content">
            <div class="container-fluid">
                <slot />
            </div>
        </div>

        <AppFooter />
    </div>
</template>
