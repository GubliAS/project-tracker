<script setup>
import Chat from '../Chat/Index.vue';
defineProps({ title: String, projects: Array, selectedProjectId: Number, messages: Array });
</script>
<template><Chat :title="title || 'Project Chat'" :projects="projects" :selected-project-id="selectedProjectId" :messages="messages" /></template>
