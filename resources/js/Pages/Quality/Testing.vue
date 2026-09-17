<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: { type: String, default: 'QA & Testing' },
    testCases: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
    summary: { type: Object, default: () => ({ passed: 0, failed: 0, untested: 0 }) },
});

const showModal = ref(false);
const filter = ref('all');
const form = useForm({ project_id: '', title: '', status: 'pending', notes: '' });
const filteredCases = computed(() => props.testCases.filter((testCase) => filter.value === 'all' || testCase.status === filter.value));
const statusLabel = (status) => ({ passed: 'Passed', failed: 'Failed', pending: 'Untested' }[status]);
const statusClass = (status) => ({ passed: 'bg-success/10 text-success', failed: 'bg-danger/10 text-danger', pending: 'bg-warning/10 text-warning' }[status]);

function submit() {
    form.post('/quality/testing', { onSuccess: () => { form.reset(); showModal.value = false; } });
}

function updateStatus(testCase, status) {
    router.put(`/quality/testing/${testCase.id}`, { status, notes: testCase.notes || '' }, { preserveScroll: true });
}
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Execute, record, and monitor project test runs">
            <template #actions><button class="ti-btn ti-btn-primary" @click="showModal = true"><i class="ri-add-line me-1"></i> Log Test Run</button></template>
        </PageHeader>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
            <div class="box"><div class="box-body"><p class="text-textmuted mb-1">Passed</p><h3 class="text-success mb-0">{{ summary.passed }}</h3></div></div>
            <div class="box"><div class="box-body"><p class="text-textmuted mb-1">Failed</p><h3 class="text-danger mb-0">{{ summary.failed }}</h3></div></div>
            <div class="box"><div class="box-body"><p class="text-textmuted mb-1">Untested</p><h3 class="text-warning mb-0">{{ summary.untested }}</h3></div></div>
        </div>

        <div class="box">
            <div class="box-header flex flex-wrap items-center justify-between gap-3"><h6 class="box-title mb-0">Test Case Execution</h6><div class="flex gap-2"><button v-for="option in [{ key: 'all', label: 'All' }, { key: 'passed', label: 'Passed' }, { key: 'failed', label: 'Failed' }, { key: 'pending', label: 'Untested' }]" :key="option.key" class="ti-btn ti-btn-sm" :class="filter === option.key ? 'ti-btn-primary' : 'ti-btn-light'" @click="filter = option.key">{{ option.label }}</button></div></div>
            <div class="box-body p-0"><div class="table-responsive"><table class="table table-hover whitespace-nowrap"><thead><tr><th>Test Case</th><th>Project</th><th>Notes</th><th>Status</th><th>Update</th></tr></thead><tbody>
                <tr v-for="testCase in filteredCases" :key="testCase.id"><td class="font-medium">{{ testCase.title }}</td><td>{{ testCase.project?.name || 'General' }}</td><td class="max-w-xs truncate">{{ testCase.notes || 'No notes recorded' }}</td><td><span class="badge" :class="statusClass(testCase.status)">{{ statusLabel(testCase.status) }}</span></td><td><select class="ti-form-select !w-36" :value="testCase.status" @change="updateStatus(testCase, $event.target.value)"><option value="pending">Untested</option><option value="passed">Passed</option><option value="failed">Failed</option></select></td></tr>
                <tr v-if="!filteredCases.length"><td colspan="5" class="py-8 text-center text-textmuted">No test runs match this filter.</td></tr>
            </tbody></table></div></div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4"><div class="box w-full max-w-xl mb-0"><form @submit.prevent="submit"><div class="box-header flex items-center justify-between"><h6 class="box-title mb-0">Log Test Run</h6><button type="button" class="ti-btn ti-btn-icon ti-btn-light ti-btn-sm" @click="showModal = false"><i class="ri-close-line"></i></button></div><div class="box-body space-y-4"><div><label class="form-label">Test Case</label><input v-model="form.title" class="ti-form-control" required><p v-if="form.errors.title" class="text-danger text-xs mt-1">{{ form.errors.title }}</p></div><div class="grid grid-cols-1 md:grid-cols-2 gap-4"><div><label class="form-label">Project</label><select v-model="form.project_id" class="ti-form-select"><option value="">General</option><option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option></select></div><div><label class="form-label">Result</label><select v-model="form.status" class="ti-form-select"><option value="pending">Untested</option><option value="passed">Passed</option><option value="failed">Failed</option></select></div></div><div><label class="form-label">Execution Notes</label><textarea v-model="form.notes" class="ti-form-control" rows="4"></textarea></div></div><div class="box-footer flex justify-end gap-2"><button type="button" class="ti-btn ti-btn-light" @click="showModal = false">Cancel</button><button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Run</button></div></form></div></div>
    </AppLayout>
</template>
