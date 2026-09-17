<script setup>
import Testing from './Testing.vue';
defineProps({ title: String, testCases: Array, projects: Array, summary: Object });
</script>
<template><Testing :title="title || 'QA & Testing'" :test-cases="testCases" :projects="projects" :summary="summary" /></template>
