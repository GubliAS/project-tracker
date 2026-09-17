<script setup>
import { usePage } from '@inertiajs/vue3';

defineEmits(['toggle-sidebar']);

const page = usePage();
</script>

<template>
    <!-- TODO: Replace with converted template header -->
    <header class="app-header border-bottom">
        <div class="container-fluid py-3 d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center gap-3">
                <button type="button" class="btn btn-sm btn-outline-secondary" @click="$emit('toggle-sidebar')">
                    Menu
                </button>
                <strong>Project Tracker</strong>
            </div>
            <span class="text-muted small">{{ page.props.auth.user?.name ?? 'Guest' }}</span>
        </div>
    </header>
</template>
