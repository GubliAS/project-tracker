<script setup>
import Reports from './Index.vue';
defineProps({ title: String, stats: Object, tasksByStatus: Object, resourceUtilization: Object, qualityByStatus: Object, projectProgress: Array, teamWorkloads: Array });
</script>
<template><Reports :title="title || 'Reports & Analytics'" :stats="stats" :tasks-by-status="tasksByStatus" :resource-utilization="resourceUtilization" :quality-by-status="qualityByStatus" :project-progress="projectProgress" :team-workloads="teamWorkloads" /></template>
