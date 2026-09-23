<script setup>
import { computed } from 'vue'
import { Link, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
const props = defineProps({ title: String, projects: { type: Array, default: () => [] }, selectedProjectId: Number, messages: { type: Array, default: () => [] } })
const form = useForm({ project_id: props.selectedProjectId || '', message: '' })
const selectedProject = computed(() => props.projects.find((project) => project.id === props.selectedProjectId))
const submit = () => form.post('/chat', { onSuccess: () => form.reset('message') })
</script>
<template><AppLayout :title="title"><div class="pm-dash"><PageHeader :title="title" subtitle="Messages stored by project"/><div class="grid grid-cols-1 gap-4 xl:grid-cols-4"><aside class="box"><div class="box-header">Projects</div><div class="box-body p-0"><Link v-for="project in projects" :key="project.id" :href="`/chat?project=${project.id}`" class="block p-3 hover:bg-light" :class="project.id === selectedProjectId ? 'bg-primary/10' : ''"># {{ project.name }}</Link><p v-if="!projects.length" class="p-3 text-textmuted">No projects yet.</p></div></aside><section class="box xl:col-span-3"><div class="box-header">{{ selectedProject?.name || 'Select a project' }}</div><div class="box-body space-y-4"><p v-if="!messages.length" class="text-textmuted">No messages yet.</p><article v-for="message in messages" :key="message.id"><p class="mb-1 font-medium">{{ message.user?.name || 'System' }}</p><p class="mb-0">{{ message.message }}</p></article></div><form class="box-footer flex gap-2" @submit.prevent="submit"><input v-model="form.message" class="ti-form-control" :disabled="!selectedProjectId" placeholder="Write a message"><button class="ti-btn ti-btn-primary" :disabled="form.processing || !selectedProjectId">Send</button></form></section></div></div></AppLayout></template>
