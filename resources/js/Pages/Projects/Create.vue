<script setup>
import { useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
const props = defineProps({ title: String })
const form = useForm({ name: '', description: '', status: 'planning' })
const submit = () => form.post('/projects')
</script>
<template><AppLayout :title="title"><div class="pm-dash"><PageHeader :title="title" subtitle="Create a project in the database"/><div class="box max-w-2xl"><form @submit.prevent="submit"><div class="box-body space-y-4"><div><label class="form-label">Name</label><input v-model="form.name" class="ti-form-control" required><p v-if="form.errors.name" class="text-danger text-sm">{{ form.errors.name }}</p></div><div><label class="form-label">Description</label><textarea v-model="form.description" class="ti-form-control" rows="4"/></div><div><label class="form-label">Status</label><select v-model="form.status" class="ti-form-select"><option value="planning">Planning</option><option value="active">Active</option><option value="on_hold">On hold</option><option value="completed">Completed</option></select></div></div><div class="box-footer"><button class="ti-btn ti-btn-primary" :disabled="form.processing">Create Project</button></div></form></div></div></AppLayout></template>
