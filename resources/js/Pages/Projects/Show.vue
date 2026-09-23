<script setup>
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
const props = defineProps({ title: String, project: Object })
</script>
<template><AppLayout :title="title"><div class="pm-dash"><PageHeader :title="project.name" :subtitle="project.description || 'No description'"/><div class="box"><div class="box-header">Tasks</div><div class="box-body p-0"><div v-if="!project.tasks.length" class="p-8 text-textmuted">No tasks have been added.</div><table v-else class="table"><thead><tr><th>Task</th><th>Status</th><th>Assignee</th></tr></thead><tbody><tr v-for="task in project.tasks" :key="task.id"><td>{{ task.title }}</td><td>{{ task.status }}</td><td>{{ task.user?.name || 'Unassigned' }}</td></tr></tbody></table></div></div></div></AppLayout></template>
