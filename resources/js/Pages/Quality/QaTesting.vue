<script setup>
import AppLayout from '@/Layouts/AppLayout.vue';
import PagePlaceholder from '@/Components/PagePlaceholder.vue';

defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <AppLayout :title="title">
        <PagePlaceholder :title="title" />
    </AppLayout>
</template>
