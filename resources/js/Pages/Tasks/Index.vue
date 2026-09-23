<script setup>
import { computed, ref } from 'vue'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
const props = defineProps({ title: String, tasks: { type: Array, default: () => [] } })
const search = ref('')
const tasks = computed(() => props.tasks.filter((task) => task.title.toLowerCase().includes(search.value.toLowerCase())))
</script>
<template><AppLayout :title="title"><div class="pm-dash"><PageHeader :title="title" subtitle="Tasks stored in the tracker database"/><div class="box"><div class="box-header"><input v-model="search" class="ti-form-control max-w-sm" placeholder="Search tasks"></div><div class="box-body p-0"><div v-if="!tasks.length" class="p-12 text-center text-textmuted">No tasks have been created yet.</div><table v-else class="table table-hover"><thead><tr><th>Task</th><th>Project</th><th>Assignee</th><th>Status</th><th>Due</th></tr></thead><tbody><tr v-for="task in tasks" :key="task.id"><td>{{ task.title }}</td><td>{{ task.project?.name || 'Unassigned' }}</td><td>{{ task.user?.name || 'Unassigned' }}</td><td><span class="badge bg-primary/10 text-primary">{{ task.status }}</span></td><td>{{ task.due_date || '—' }}</td></tr></tbody></table></div></div></div></AppLayout></template>
