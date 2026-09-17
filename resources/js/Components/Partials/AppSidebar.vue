<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { navigation } from '@/config/navigation.js';

defineProps({
    collapsed: {
        type: Boolean,
        default: false,
    },
});

const page = usePage();

const isActive = (href) => {
    const url = page.url.split('?')[0];
    if (href === '/') {
        return url === '/';
    }

    return url === href || url.startsWith(`${href}/`);
};
</script>

<template>
    <!-- TODO: Replace with converted template sidebar -->
    <aside class="app-sidebar border-end" :class="{ collapsed }">
        <nav class="p-3">
            <ul class="nav flex-column gap-2">
                <template v-for="item in navigation" :key="item.label">
                    <li v-if="item.href" class="nav-item">
                        <Link
                            :href="item.href"
                            class="nav-link"
                            :class="{ active: isActive(item.href) }"
                        >
                            {{ item.label }}
                        </Link>
                    </li>
                    <li v-else class="nav-item">
                        <span class="nav-link disabled fw-semibold">{{ item.label }}</span>
                        <ul class="nav flex-column ms-3">
                            <li v-for="child in item.children" :key="child.href" class="nav-item">
                                <Link
                                    :href="child.href"
                                    class="nav-link py-1"
                                    :class="{ active: isActive(child.href) }"
                                >
                                    {{ child.label }}
                                </Link>
                            </li>
                        </ul>
                    </li>
                </template>
            </ul>
        </nav>
    </aside>
</template>
