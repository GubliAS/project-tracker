<script setup>
import TaskBoard from './Index.vue';
defineProps({ title: String, tasks: Array, projects: Array, users: Array });
</script>
<template><TaskBoard :title="title || 'Kanban Board'" :tasks="tasks" :projects="projects" :users="users" /></template>
