<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
});
</script>

<template>
    <div class="card">
        <div class="card-body">
            <h2 class="h5">{{ title }}</h2>
            <p class="text-muted mb-0">
                Placeholder page. Convert markup from the reference Vue app when you
                are assigned this section.
            </p>
        </div>
    </div>
</template>
