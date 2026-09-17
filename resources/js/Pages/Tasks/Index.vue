<script setup>
import { computed, ref } from 'vue';
import { router, useForm, usePage } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: {
        type: String,
        default: 'Kanban Board',
    },
    tasks: {
        type: Array,
        default: () => [],
    },
    projects: {
        type: Array,
        default: () => [],
    },
    users: {
        type: Array,
        default: () => [],
    },
});

const page = usePage();
const showModal = ref(false);

const columns = [
    { key: 'todo', label: 'To Do', icon: 'ri-checkbox-blank-circle-line', accent: 'bg-info' },
    { key: 'in_progress', label: 'In Progress', icon: 'ri-loader-4-line', accent: 'bg-primary' },
    { key: 'review', label: 'Review', icon: 'ri-eye-line', accent: 'bg-warning' },
    { key: 'done', label: 'Done', icon: 'ri-checkbox-circle-line', accent: 'bg-success' },
];

const form = useForm({
    title: '',
    description: '',
    project_id: '',
    user_id: '',
    priority: 'medium',
    due_date: '',
    status: 'todo',
});

const tasksByStatus = computed(() => {
    return columns.reduce((grouped, column) => {
        grouped[column.key] = props.tasks.filter((task) => task.status === column.key);
        return grouped;
    }, {});
});

const flashMessage = computed(() => page.props.flash?.message);

const priorityClass = (priority) => {
    const classes = {
        low: 'bg-success/10 text-success',
        medium: 'bg-warning/10 text-warning',
        high: 'bg-danger/10 text-danger',
        urgent: 'bg-danger text-white',
    };

    return classes[priority] || 'bg-secondary/10 text-secondary';
};

const formatDate = (dateStr) => {
    if (!dateStr) {
        return 'No due date';
    }

    return new Date(dateStr).toLocaleDateString('en-US', {
        month: 'short',
        day: 'numeric',
        year: 'numeric',
    });
};

const openModal = () => {
    form.reset();
    form.clearErrors();
    form.status = 'todo';
    form.priority = 'medium';
    showModal.value = true;
};

const closeModal = () => {
    showModal.value = false;
};

const submitTask = () => {
    form.transform((data) => ({
        ...data,
        project_id: data.project_id || null,
        user_id: data.user_id || null,
        due_date: data.due_date || null,
    })).post('/tasks', {
        preserveScroll: true,
        onSuccess: () => {
            form.reset();
            showModal.value = false;
        },
    });
};

const updateStatus = (task, status) => {
    if (task.status === status) {
        return;
    }

    router.put(`/tasks/${task.id}`, { status }, { preserveScroll: true });
};

const deleteTask = (task) => {
    if (!window.confirm(`Delete task "${task.title}"?`)) {
        return;
    }

    router.delete(`/tasks/${task.id}`, { preserveScroll: true });
};
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Organize work across To Do, In Progress, Review, and Done">
            <template #actions>
                <button type="button" class="ti-btn ti-btn-primary btn-wave" @click="openModal">
                    <i class="ri-add-line me-1"></i> New Task
                </button>
            </template>
        </PageHeader>

        <div v-if="flashMessage" class="alert alert-success mb-4">
            {{ flashMessage }}
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-4 gap-4">
            <div v-for="column in columns" :key="column.key" class="box">
                <div class="box-header flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="avatar avatar-xs avatar-rounded text-white" :class="column.accent">
                            <i :class="column.icon"></i>
                        </span>
                        <h6 class="box-title mb-0">{{ column.label }}</h6>
                    </div>
                    <span class="badge bg-light text-default">{{ tasksByStatus[column.key]?.length || 0 }}</span>
                </div>
                <div class="box-body space-y-3 min-h-[280px]">
                    <div
                        v-for="task in tasksByStatus[column.key]"
                        :key="task.id"
                        class="border border-defaultborder rounded-md p-3 bg-light/40"
                    >
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h6 class="font-medium mb-0">{{ task.title }}</h6>
                            <span class="badge" :class="priorityClass(task.priority)">{{ task.priority }}</span>
                        </div>
                        <p class="text-textmuted text-xs mb-3">
                            {{ task.description || 'No description provided.' }}
                        </p>
                        <div class="flex flex-wrap gap-2 text-xs text-textmuted mb-3">
                            <span>
                                <i class="ri-folder-line me-1"></i>
                                {{ task.project?.name || 'No project' }}
                            </span>
                            <span>
                                <i class="ri-user-line me-1"></i>
                                {{ task.user?.name || 'Unassigned' }}
                            </span>
                            <span>
                                <i class="ri-calendar-line me-1"></i>
                                {{ formatDate(task.due_date) }}
                            </span>
                        </div>
                        <div class="flex items-center gap-2">
                            <select
                                class="ti-form-select ti-form-select-sm"
                                :value="task.status"
                                @change="updateStatus(task, $event.target.value)"
                            >
                                <option value="todo">To Do</option>
                                <option value="in_progress">In Progress</option>
                                <option value="review">Review</option>
                                <option value="done">Done</option>
                            </select>
                            <button
                                type="button"
                                class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm"
                                @click="deleteTask(task)"
                            >
                                <i class="ri-delete-bin-line"></i>
                            </button>
                        </div>
                    </div>
                    <p v-if="!tasksByStatus[column.key]?.length" class="text-textmuted text-sm text-center py-6">
                        No tasks in this column.
                    </p>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="hs-overlay open" style="position: fixed; inset: 0; z-index: 1055; background: rgba(15, 23, 42, 0.45); display: flex; align-items: center; justify-content: center; padding: 1rem;">
            <div class="box w-full max-w-xl mb-0">
                <div class="box-header flex items-center justify-between">
                    <h6 class="box-title mb-0">Create Task</h6>
                    <button type="button" class="ti-btn ti-btn-sm ti-btn-light" @click="closeModal">
                        <i class="ri-close-line"></i>
                    </button>
                </div>
                <form @submit.prevent="submitTask">
                    <div class="box-body space-y-4">
                        <div>
                            <label class="form-label">Title</label>
                            <input v-model="form.title" type="text" class="ti-form-control" placeholder="Task title" required>
                            <p v-if="form.errors.title" class="text-danger text-xs mt-1">{{ form.errors.title }}</p>
                        </div>
                        <div>
                            <label class="form-label">Description</label>
                            <textarea v-model="form.description" class="ti-form-control" rows="3" placeholder="Describe the work"></textarea>
                            <p v-if="form.errors.description" class="text-danger text-xs mt-1">{{ form.errors.description }}</p>
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
                                <p v-if="form.errors.project_id" class="text-danger text-xs mt-1">{{ form.errors.project_id }}</p>
                            </div>
                            <div>
                                <label class="form-label">Assignee</label>
                                <select v-model="form.user_id" class="ti-form-select">
                                    <option value="">Unassigned</option>
                                    <option v-for="user in users" :key="user.id" :value="user.id">
                                        {{ user.name }}
                                    </option>
                                </select>
                                <p v-if="form.errors.user_id" class="text-danger text-xs mt-1">{{ form.errors.user_id }}</p>
                            </div>
                            <div>
                                <label class="form-label">Priority</label>
                                <select v-model="form.priority" class="ti-form-select" required>
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                    <option value="urgent">Urgent</option>
                                </select>
                                <p v-if="form.errors.priority" class="text-danger text-xs mt-1">{{ form.errors.priority }}</p>
                            </div>
                            <div>
                                <label class="form-label">Due Date</label>
                                <input v-model="form.due_date" type="date" class="ti-form-control">
                                <p v-if="form.errors.due_date" class="text-danger text-xs mt-1">{{ form.errors.due_date }}</p>
                            </div>
                        </div>
                    </div>
                    <div class="box-footer flex justify-end gap-2">
                        <button type="button" class="ti-btn ti-btn-light" @click="closeModal">Cancel</button>
                        <button type="submit" class="ti-btn ti-btn-primary" :disabled="form.processing">
                            Save Task
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
