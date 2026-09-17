<script setup>
import { computed, nextTick, ref, watch } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({ title: { type: String, default: 'Project Chat' }, projects: { type: Array, default: () => [] }, selectedProjectId: { type: Number, default: null }, messages: { type: Array, default: () => [] } });
const messageList = ref(null);
const activeProjectId = ref(props.selectedProjectId);
const form = useForm({ project_id: props.selectedProjectId || '', message: '' });
const activeProject = computed(() => props.projects.find((project) => project.id === activeProjectId.value));
watch(() => props.selectedProjectId, (value) => { activeProjectId.value = value; form.project_id = value || ''; nextTick(scrollToLatest); });
watch(() => props.messages, () => nextTick(scrollToLatest));
function selectProject(projectId) { router.get('/chat', { project: projectId }, { preserveState: true, preserveScroll: true }); }
function send() { form.post('/chat', { preserveScroll: true, onSuccess: () => { form.reset('message'); form.project_id = activeProjectId.value || ''; } }); }
function scrollToLatest() { if (messageList.value) messageList.value.scrollTop = messageList.value.scrollHeight; }
</script>

<template><AppLayout :title="title"><PageHeader :title="title" subtitle="Discuss work in the context of each project" /><div class="box overflow-hidden"><div class="grid grid-cols-1 lg:grid-cols-[18rem_1fr] min-h-[34rem]"><aside class="border-e border-defaultborder"><div class="p-4 border-b border-defaultborder"><h6 class="mb-0">Project Channels</h6></div><div class="p-2 space-y-1"><button v-for="project in projects" :key="project.id" class="w-full rounded-lg px-3 py-3 text-left transition" :class="activeProjectId === project.id ? 'bg-primary text-white' : 'hover:bg-light dark:hover:bg-black/10'" @click="selectProject(project.id)"><i class="ri-hashtag me-2"></i>{{ project.name }}</button><p v-if="!projects.length" class="p-3 text-sm text-textmuted">Create a project to start a channel.</p></div></aside><section class="flex min-h-[34rem] flex-col"><div class="flex items-center justify-between border-b border-defaultborder p-4"><div><h6 class="mb-0">{{ activeProject?.name || 'Select a project' }}</h6><span class="text-xs text-textmuted">{{ messages.length }} messages</span></div><span class="avatar avatar-sm bg-success/10 text-success"><i class="ri-discuss-line"></i></span></div><div ref="messageList" class="flex-1 space-y-4 overflow-y-auto p-4 max-h-[28rem]"><div v-for="message in messages" :key="message.id" class="flex gap-3"><span class="avatar avatar-sm bg-primary/10 text-primary">{{ (message.user?.name || 'S').slice(0, 1).toUpperCase() }}</span><div><div class="flex items-center gap-2"><span class="font-medium text-sm">{{ message.user?.name || 'System user' }}</span><span class="text-xs text-textmuted">{{ new Date(message.created_at).toLocaleString() }}</span></div><p class="mb-0 mt-1 whitespace-pre-line">{{ message.message }}</p></div></div><div v-if="!messages.length" class="py-12 text-center text-textmuted"><i class="ri-chat-3-line text-4xl"></i><p class="mt-3 mb-0">No messages in this channel yet.</p></div></div><form class="flex gap-2 border-t border-defaultborder p-4" @submit.prevent="send"><input v-model="form.message" class="ti-form-control" :disabled="!activeProjectId" placeholder="Write a message…"><button class="ti-btn ti-btn-primary" :disabled="form.processing || !activeProjectId || !form.message.trim()"><i class="ri-send-plane-2-line"></i></button></form></section></div></div></AppLayout></template>
