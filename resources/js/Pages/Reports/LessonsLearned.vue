<script setup>
import Lessons from './Lessons.vue';
defineProps({ title: String, lessons: Array, projects: Array });
</script>
<template><Lessons :title="title || 'Lessons Learned'" :lessons="lessons" :projects="projects" /></template>
