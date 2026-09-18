<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: { type: String, default: 'Change Log' },
    changes: { type: Array, default: () => [] },
});

const activeType = ref('all');
const showModal = ref(false);
const editingChange = ref(null);
const form = useForm({
    version: '',
    title: '',
    description: '',
    type: 'feature',
    release_date: new Date().toISOString().slice(0, 10),
});

const types = [
    { value: 'all', label: 'All changes' },
    { value: 'feature', label: 'Features' },
    { value: 'improvement', label: 'Improvements' },
    { value: 'fix', label: 'Fixes' },
    { value: 'security', label: 'Security' },
];

const visibleChanges = computed(() => props.changes.filter((change) => activeType.value === 'all' || change.type === activeType.value));

function typeClass(type) {
    return {
        feature: 'bg-primary/10 text-primary',
        improvement: 'bg-info/10 text-info',
        fix: 'bg-success/10 text-success',
        security: 'bg-danger/10 text-danger',
    }[type] || 'bg-light text-textmuted';
}

function openCreate() {
    editingChange.value = null;
    form.reset();
    form.clearErrors();
    form.type = 'feature';
    form.release_date = new Date().toISOString().slice(0, 10);
    showModal.value = true;
}

function openEdit(change) {
    editingChange.value = change;
    form.clearErrors();
    form.version = change.version;
    form.title = change.title;
    form.description = change.description;
    form.type = change.type;
    form.release_date = change.release_date;
    showModal.value = true;
}

function closeModal() {
    showModal.value = false;
    form.clearErrors();
}

function submit() {
    const options = {
        onSuccess: () => {
            closeModal();
            form.reset();
        },
    };

    if (editingChange.value) {
        form.put(`/quality/change-log/${editingChange.value.id}`, options);
        return;
    }

    form.post('/quality/change-log', options);
}

function remove(change) {
    if (confirm(`Delete change record "${change.version}"?`)) {
        router.delete(`/quality/change-log/${change.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Publish a clear, searchable history of releases and project changes">
            <template #actions>
                <button class="ti-btn ti-btn-primary" @click="openCreate">
                    <i class="ri-add-line me-1"></i>
                    New Entry
                </button>
            </template>
        </PageHeader>

        <div class="box mb-4">
            <div class="box-body flex flex-wrap items-center gap-2">
                <button
                    v-for="type in types"
                    :key="type.value"
                    class="ti-btn ti-btn-sm"
                    :class="activeType === type.value ? 'ti-btn-primary' : 'ti-btn-light'"
                    @click="activeType = type.value"
                >
                    {{ type.label }}
                </button>
            </div>
        </div>

        <section class="space-y-4">
            <article v-for="change in visibleChanges" :key="change.id" class="box mb-0">
                <div class="box-body">
                    <div class="flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
                        <div class="flex min-w-0 gap-3">
                            <span class="avatar avatar-md shrink-0" :class="typeClass(change.type)">
                                <i :class="change.type === 'fix' ? 'ri-tools-line' : change.type === 'security' ? 'ri-shield-check-line' : 'ri-git-commit-line'"></i>
                            </span>
                            <div class="min-w-0">
                                <div class="mb-2 flex flex-wrap items-center gap-2">
                                    <span class="badge" :class="typeClass(change.type)">{{ change.type }}</span>
                                    <span class="font-semibold">{{ change.version }}</span>
                                    <span class="text-xs text-textmuted">{{ new Date(change.release_date).toLocaleDateString() }}</span>
                                </div>
                                <h5 class="mb-2">{{ change.title }}</h5>
                                <p class="mb-0 whitespace-pre-line text-textmuted">{{ change.description }}</p>
                            </div>
                        </div>
                        <div class="flex shrink-0 gap-2">
                            <button class="ti-btn ti-btn-soft-primary ti-btn-sm" @click="openEdit(change)">Edit</button>
                            <button class="ti-btn ti-btn-soft-danger ti-btn-sm" @click="remove(change)">Delete</button>
                        </div>
                    </div>
                </div>
            </article>

            <div v-if="!visibleChanges.length" class="box mb-0">
                <div class="box-body py-16 text-center text-textmuted">
                    <i class="ri-git-commit-line text-4xl"></i>
                    <p class="mb-0 mt-3">No change records match this filter.</p>
                </div>
            </div>
        </section>

        <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <div class="box mb-0 w-full max-w-2xl">
                <form @submit.prevent="submit">
                    <div class="box-header flex items-center justify-between">
                        <h6 class="box-title mb-0">{{ editingChange ? 'Edit Change Entry' : 'New Change Entry' }}</h6>
                        <button type="button" class="ti-btn ti-btn-icon ti-btn-light ti-btn-sm" aria-label="Close" @click="closeModal">
                            <i class="ri-close-line"></i>
                        </button>
                    </div>
                    <div class="box-body grid gap-4">
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div class="md:col-span-1">
                                <label class="form-label" for="change-version">Version</label>
                                <input id="change-version" v-model="form.version" class="ti-form-control" placeholder="v1.4.0" required>
                                <p v-if="form.errors.version" class="mt-1 text-xs text-danger">{{ form.errors.version }}</p>
                            </div>
                            <div class="md:col-span-1">
                                <label class="form-label" for="change-type">Change type</label>
                                <select id="change-type" v-model="form.type" class="ti-form-select">
                                    <option value="feature">Feature</option>
                                    <option value="improvement">Improvement</option>
                                    <option value="fix">Fix</option>
                                    <option value="security">Security</option>
                                </select>
                            </div>
                            <div class="md:col-span-1">
                                <label class="form-label" for="release-date">Release date</label>
                                <input id="release-date" v-model="form.release_date" type="date" class="ti-form-control" required>
                                <p v-if="form.errors.release_date" class="mt-1 text-xs text-danger">{{ form.errors.release_date }}</p>
                            </div>
                        </div>
                        <div>
                            <label class="form-label" for="change-title">Title</label>
                            <input id="change-title" v-model="form.title" class="ti-form-control" placeholder="Describe the release" required>
                            <p v-if="form.errors.title" class="mt-1 text-xs text-danger">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="form-label" for="change-description">Details</label>
                            <textarea id="change-description" v-model="form.description" class="ti-form-control" rows="5" placeholder="What changed and why?" required></textarea>
                            <p v-if="form.errors.description" class="mt-1 text-xs text-danger">{{ form.errors.description }}</p>
                        </div>
                    </div>
                    <div class="box-footer flex justify-end gap-2">
                        <button type="button" class="ti-btn ti-btn-light" @click="closeModal">Cancel</button>
                        <button class="ti-btn ti-btn-primary" :disabled="form.processing">{{ editingChange ? 'Save Changes' : 'Publish Entry' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
