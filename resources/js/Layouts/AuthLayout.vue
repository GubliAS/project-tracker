<script setup>
import { Head } from '@inertiajs/vue3'
import { onMounted, ref } from 'vue'

defineProps({
    title: {
        type: String,
        default: '',
    },
    heading: {
        type: String,
        default: '',
    },
    subheading: {
        type: String,
        default: '',
    },
})

const THEME_STORAGE_KEY = 'pm-theme'

const getStoredTheme = () => {
    try {
        return localStorage.getItem(THEME_STORAGE_KEY) === 'dark'
    } catch (e) {
        return false
    }
}

const isDarkMode = ref(getStoredTheme())

const applyChromeTheme = (dark) => {
    const theme = dark ? 'dark' : 'light'
    document.documentElement.classList.toggle('dark', dark)
    document.documentElement.setAttribute('data-header-styles', theme)
    document.documentElement.setAttribute('data-menu-styles', theme)

    try {
        localStorage.setItem(THEME_STORAGE_KEY, theme)
    } catch (e) {
        // theme persistence is optional
    }
}

const toggleDarkMode = () => {
    isDarkMode.value = !isDarkMode.value
    applyChromeTheme(isDarkMode.value)
}

onMounted(() => {
    applyChromeTheme(isDarkMode.value)
})
</script>

<template>
    <div class="pm-auth" :class="{ dark: isDarkMode }">
        <Head :title="title" />

        <button
            type="button"
            class="pm-auth__theme"
            :aria-label="isDarkMode ? 'Switch to light mode' : 'Switch to dark mode'"
            @click="toggleDarkMode"
        >
            <i :class="isDarkMode ? 'ri-sun-line' : 'ri-moon-line'"></i>
        </button>

        <div class="pm-auth__shell">
            <aside class="pm-auth__brand">
                <div class="pm-auth__orb pm-auth__orb--one"></div>
                <div class="pm-auth__orb pm-auth__orb--two"></div>

                <img class="pm-auth__logo" src="/assets/img/Kedebah Logo.png" alt="KEDEBEAH ERP" />
                <span class="pm-focus-pill">Project Tracker</span>
                <h1>Plan, track, and deliver every engagement in one workspace.</h1>
                <p>Sign in to manage projects, sprints, quality, and the team from the same purple glass desk you already use.</p>

                <ul class="pm-auth__chips">
                    <li><i class="ri-folder-chart-line"></i> Projects</li>
                    <li><i class="ri-task-line"></i> Tasks</li>
                    <li><i class="ri-shield-check-line"></i> Quality</li>
                </ul>

                <div class="pm-auth__art">
                    <img src="/assets/img/manage-projects.png" alt="" />
                </div>
            </aside>

            <main class="pm-auth__panel">
                <div class="pm-auth__card">
                    <img class="pm-auth__card-logo" src="/assets/img/Kedebah Logo.png" alt="KEDEBEAH ERP" />
                    <h2>{{ heading }}</h2>
                    <p v-if="subheading" class="pm-auth__lede">{{ subheading }}</p>
                    <slot />
                </div>
            </main>
        </div>
    </div>
</template>
