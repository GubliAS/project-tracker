<script setup>
import { ref } from 'vue';
import AppHeader from '@/Components/Partials/AppHeader.vue';
import AppSidebar from '@/Components/Partials/AppSidebar.vue';
import AppFooter from '@/Components/Partials/AppFooter.vue';

defineProps({
    title: {
        type: String,
        default: 'Project Tracker',
    },
});

const sidebarCollapsed = ref(false);

const toggleSidebar = () => {
    sidebarCollapsed.value = !sidebarCollapsed.value;
};
</script>

<template>
    <div class="app-wrapper" :class="{ 'sidebar-collapsed': sidebarCollapsed }">
        <AppHeader @toggle-sidebar="toggleSidebar" />
        <AppSidebar :collapsed="sidebarCollapsed" />

        <main class="app-content">
            <div class="content-header">
                <h1 class="h4 mb-0">{{ title }}</h1>
            </div>

            <div class="content-body">
                <slot />
            </div>
        </main>

        <AppFooter />
    </div>
</template>
