<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Quality Control',
    },
    qualityChecks: {
        type: Array,
        default: () => [],
    },
    projects: {
        type: Array,
        default: () => [],
    },
    summary: {
        type: Object,
        default: () => ({ pending: 0, passed: 0, failed: 0, total: 0 }),
    },
});

const page = usePage();
const showModal = ref(false);
const editingCheck = ref(null);

const form = useForm({
    project_id: '',
    title: '',
    check_type: 'testing',
    status: 'pending',
    notes: '',
});

const flashMessage = computed(() => page.props.flash?.message);

const typeLabel = (type) => {
    const labels = {
        code_review: 'Code Review',
        testing: 'Testing',
        security_audit: 'Security Audit',
        compliance: 'Compliance',
    };

    return labels[type] || type;
};

const statusClass = (status) => {
    const classes = {
        pending: 'bg-warning/10 text-warning',
        passed: 'bg-success/10 text-success',
        failed: 'bg-danger/10 text-danger',
    };

    return classes[status] || 'bg-secondary/10 text-secondary';
};

const percent = (count) => {
    if (!props.summary.total) {
        return 0;
    }

    return Math.round((count / props.summary.total) * 100);
};

const openCreateModal = () => {
    editingCheck.value = null;
    form.reset();
    form.clearErrors();
    form.check_type = 'testing';
    form.status = 'pending';
    showModal.value = true;
};

const openEditModal = (check) => {
    editingCheck.value = check;
    form.clearErrors();
    form.project_id = check.project_id || '';
    form.title = check.title;
    form.check_type = check.check_type;
    form.status = check.status;
    form.notes = check.notes || '';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
    editingCheck.value = null;
};

const submitCheck = () => {
    const payload = {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showModal.value = false;
            editingCheck.value = null;
        },
    };

    const data = {
        project_id: form.project_id || null,
        title: form.title,
        check_type: form.check_type,
        status: form.status,
        notes: form.notes || null,
    };

    if (editingCheck.value) {
        form.transform(() => data).put(`/quality/${editingCheck.value.id}`, payload);
        return;
    }

    form.transform(() => data).post('/quality', payload);
};

const updateStatus = (check, status) => {
    router.put(`/quality/${check.id}`, { status }, { preserveScroll: true });
};

const deleteCheck = (check) => {
    if (!window.confirm(`Delete quality check "${check.title}"?`)) {
        return;
    }

    router.delete(`/quality/${check.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Track audits, testing, and compliance outcomes">
            <template #actions>
                <button type="button" class="ti-btn ti-btn-primary btn-wave" @click="openCreateModal">
                    <i class="ri-add-line me-1"></i> Log QA Check
                </button>
            </template>
        </PageHeader>

        <div v-if="flashMessage" class="alert alert-success mb-4">
            {{ flashMessage }}
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Passed</p>
                    <h3 class="mb-2">{{ summary.passed }}</h3>
                    <div class="progress progress-xs">
                        <div class="progress-bar bg-success" :style="{ width: percent(summary.passed) + '%' }"></div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Pending</p>
                    <h3 class="mb-2">{{ summary.pending }}</h3>
                    <div class="progress progress-xs">
                        <div class="progress-bar bg-warning" :style="{ width: percent(summary.pending) + '%' }"></div>
                    </div>
                </div>
            </div>
            <div class="box">
                <div class="box-body">
                    <p class="text-textmuted mb-1">Failed</p>
                    <h3 class="mb-2">{{ summary.failed }}</h3>
                    <div class="progress progress-xs">
                        <div class="progress-bar bg-danger" :style="{ width: percent(summary.failed) + '%' }"></div>
                    </div>
                </div>
            </div>
        </div>

        <div class="box">
            <div class="box-header">
                <h6 class="box-title mb-0">Quality Checks</h6>
            </div>
            <div class="box-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover whitespace-nowrap">
                        <thead>
                            <tr>
                                <th>Check</th>
                                <th>Project</th>
                                <th>Type</th>
                                <th>Status</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="check in qualityChecks" :key="check.id">
                                <td class="font-medium">{{ check.title }}</td>
                                <td>{{ check.project?.name || 'No project' }}</td>
                                <td>{{ typeLabel(check.check_type) }}</td>
                                <td>
                                    <select
                                        class="ti-form-select ti-form-select-sm"
                                        :value="check.status"
                                        @change="updateStatus(check, $event.target.value)"
                                    >
                                        <option value="pending">Pending</option>
                                        <option value="passed">Passed</option>
                                        <option value="failed">Failed</option>
                                    </select>
                                </td>
                                <td class="max-w-[240px] truncate">{{ check.notes || '—' }}</td>
                                <td>
                                    <div class="flex gap-1">
                                        <button type="button" class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" @click="openEditModal(check)">
                                            <i class="ri-edit-line"></i>
                                        </button>
                                        <button type="button" class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" @click="deleteCheck(check)">
                                            <i class="ri-delete-bin-line"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr v-if="!qualityChecks.length">
                                <td colspan="6" class="text-center text-textmuted py-6">
                                    No quality checks logged yet.
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div v-if="showModal" style="position: fixed; inset: 0; z-index: 1055; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <div class="box w-full max-w-xl mb-0">
                <div class="box-header flex items-center justify-between">
                    <h6 class="box-title mb-0">{{ editingCheck ? 'Update Quality Check' : 'Log QA Check' }}</h6>
                    <button type="button" class="ti-btn ti-btn-sm ti-btn-light" @click="closeModal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form @submit.prevent="submitCheck">
                    <div class="box-body space-y-4">
                        <div>
                            <label class="form-label">Title</label>
                            <input v-model="form.title" type="text" class="ti-form-control" required>
                            <p v-if="form.errors.title" class="text-danger text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="form-label">Project</label>
                                <select v-model="form.project_id" class="ti-form-select">
                                    <option value="">No project</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">
                                        {{ project.name }}
                                    </option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Check Type</label>
                                <select v-model="form.check_type" class="ti-form-select" required>
                                    <option value="code_review">Code Review</option>
                                    <option value="testing">Testing</option>
                                    <option value="security_audit">Security Audit</option>
                                    <option value="compliance">Compliance</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label">Status</label>
                                <select v-model="form.status" class="ti-form-select" required>
                                    <option value="pending">Pending</option>
                                    <option value="passed">Passed</option>
                                    <option value="failed">Failed</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label">Notes</label>
                            <textarea v-model="form.notes" class="ti-form-control" rows="3"></textarea>
                        </div>
                    </div>
                    <div class="box-footer flex justify-end gap-2">
                        <button type="button" class="ti-btn ti-btn-light" @click="closeModal">Cancel</button>
                        <button type="submit" class="ti-btn ti-btn-primary" :disabled="form.processing">
                            {{ editingCheck ? 'Update Check' : 'Save Check' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
