<script setup>
import { computed } from 'vue';
import { router } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';
const props = defineProps({ title: { type: String, default: 'Workflows' }, tasks: { type: Array, default: () => [] } });
const stages = [{ key: 'todo', label: 'To Do', color: 'bg-info' }, { key: 'in_progress', label: 'In Progress', color: 'bg-primary' }, { key: 'review', label: 'Review', color: 'bg-warning' }, { key: 'done', label: 'Done', color: 'bg-success' }];
const grouped = computed(() => Object.fromEntries(stages.map((stage) => [stage.key, props.tasks.filter((task) => task.status === stage.key)])));
function move(task, status) { router.put(`/tasks/${task.id}`, { status }, { preserveScroll: true }); }
</script>
<template><AppLayout :title="title"><PageHeader :title="title" subtitle="Visualize work handoffs and move tasks through delivery stages" /><div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4"><section v-for="stage in stages" :key="stage.key" class="box mb-0"><div class="box-header flex justify-between"><h6 class="box-title mb-0">{{ stage.label }}</h6><span class="badge" :class="stage.color">{{ grouped[stage.key].length }}</span></div><div class="box-body space-y-3"><article v-for="task in grouped[stage.key]" :key="task.id" class="rounded-lg border border-defaultborder p-3"><p class="font-medium mb-2">{{ task.title }}</p><p class="text-xs text-textmuted mb-3">{{ task.project?.name || 'General' }} · {{ task.user?.name || 'Unassigned' }}</p><select class="ti-form-select text-sm" :value="task.status" @change="move(task, $event.target.value)"><option v-for="option in stages" :key="option.key" :value="option.key">{{ option.label }}</option></select></article><p v-if="!grouped[stage.key].length" class="py-6 text-center text-sm text-textmuted">No tasks</p></div></section></div></AppLayout></template>
