<script setup>
import { computed } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
const props = defineProps({ title: String, tasks: { type: Array, default: () => [] } })
const columns = computed(() => ['todo', 'in_progress', 'review', 'done'].map((status) => ({ status, tasks: props.tasks.filter((task) => task.status === status) })))
</script>
<template><AppLayout :title="title"><div class="pm-dash"><PageHeader :title="title" subtitle="Live task status from the database"/><div class="grid grid-cols-1 gap-4 md:grid-cols-2 xl:grid-cols-4"><section v-for="column in columns" :key="column.status" class="box"><div class="box-header font-medium">{{ column.status.replace('_', ' ') }}</div><div class="box-body space-y-3"><p v-if="!column.tasks.length" class="text-sm text-textmuted">No tasks</p><article v-for="task in column.tasks" :key="task.id" class="rounded border p-3"><p class="mb-1 font-medium">{{ task.title }}</p><p class="mb-0 text-xs text-textmuted">{{ task.project?.name || 'No project' }}</p></article></div></section></div></div></AppLayout></template>
