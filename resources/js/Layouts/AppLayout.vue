<script setup>
import { onMounted, nextTick, ref } from 'vue';
import AppHeader from '@/Components/Partials/AppHeader.vue';
import AppSidebar from '@/Components/Partials/AppSidebar.vue';
import AppFooter from '@/Components/Partials/AppFooter.vue';

const isDarkMode = ref(false);

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value;
    document.documentElement.classList.toggle('dark', isDarkMode.value);
};

onMounted(() => {
    document.documentElement.setAttribute('data-nav-layout', 'horizontal');
    document.documentElement.setAttribute('data-nav-style', 'menu-click');
    document.documentElement.setAttribute('data-menu-styles', 'light');
    document.documentElement.setAttribute('data-header-styles', 'light');

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
        <AppHeader @toggle-dark="toggleDarkMode" />
        <AppSidebar />

        <div class="main-content app-content">
            <div class="container-fluid">
                <slot />
            </div>
        </div>

        <AppFooter />
    </div>
</template>
