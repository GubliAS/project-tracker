<script setup>
import { computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

defineProps({
    title: {
        type: String,
        required: true,
    },
    subtitle: {
        type: String,
        default: '',
    },
});

const page = usePage();

const breadcrumbs = computed(() => {
    const crumbs = [{ label: 'Home', href: '/' }];
    const path = page.url.split('?')[0];
    const pathParts = path.split('/').filter(Boolean);
    let currentPath = '';

    pathParts.forEach((part, index) => {
        currentPath += `/${part}`;
        const isLast = index === pathParts.length - 1;
        crumbs.push({
            label: part.charAt(0).toUpperCase() + part.slice(1).replace(/-/g, ' '),
            href: isLast ? null : currentPath,
        });
    });

    return crumbs;
});
</script>

<template>
    <div class="flex items-center justify-between page-header-breadcrumb flex-wrap gap-2 mb-4">
        <div>
            <nav>
                <ol class="breadcrumb mb-1">
                    <li
                        v-for="(crumb, index) in breadcrumbs"
                        :key="index"
                        class="breadcrumb-item"
                        :class="{ active: !crumb.href }"
                        :aria-current="!crumb.href ? 'page' : undefined"
                    >
                        <Link v-if="crumb.href" :href="crumb.href">{{ crumb.label }}</Link>
                        <span v-else>{{ crumb.label }}</span>
                    </li>
                </ol>
            </nav>
            <h1 class="page-title font-medium text-lg mb-0">{{ title }}</h1>
            <p v-if="subtitle" class="text-textmuted dark:text-textmuted/50 text-sm mt-1">{{ subtitle }}</p>
        </div>
        <div class="btn-list">
            <slot name="actions" />
        </div>
    </div>
</template>
