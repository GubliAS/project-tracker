<script setup>
import { computed, ref } from 'vue';
import { router, useForm } from '@inertiajs/vue3';
import AppLayout from '@/Layouts/AppLayout.vue';
import PageHeader from '@/Components/ui/PageHeader.vue';

const props = defineProps({
    title: { type: String, default: 'Lessons Learned' },
    lessons: { type: Array, default: () => [] },
    projects: { type: Array, default: () => [] },
});

const selectedCategory = ref('all');
const showModal = ref(false);
const editingLesson = ref(null);
const form = useForm({
    title: '',
    category: 'Delivery',
    impact_level: 'medium',
    recommendation: '',
    project_id: '',
});

const categories = computed(() => ['all', ...new Set(props.lessons.map((lesson) => lesson.category))]);
const visibleLessons = computed(() => props.lessons.filter((lesson) => selectedCategory.value === 'all' || lesson.category === selectedCategory.value));

function impactClass(level) {
    return {
        low: 'bg-success/10 text-success',
        medium: 'bg-warning/10 text-warning',
        high: 'bg-danger/10 text-danger',
    }[level] || 'bg-light text-textmuted';
}

function openCreate() {
    editingLesson.value = null;
    form.reset();
    form.clearErrors();
    form.category = 'Delivery';
    form.impact_level = 'medium';
    showModal.value = true;
}

function openEdit(lesson) {
    editingLesson.value = lesson;
    form.clearErrors();
    form.title = lesson.title;
    form.category = lesson.category;
    form.impact_level = lesson.impact_level;
    form.recommendation = lesson.recommendation;
    form.project_id = lesson.project_id || '';
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

    if (editingLesson.value) {
        form.put(`/reports/lessons/${editingLesson.value.id}`, options);
        return;
    }

    form.post('/reports/lessons', options);
}

function remove(lesson) {
    if (confirm(`Delete lesson "${lesson.title}"?`)) {
        router.delete(`/reports/lessons/${lesson.id}`, { preserveScroll: true });
    }
}
</script>

<template>
    <AppLayout :title="title">
        <PageHeader :title="title" subtitle="Turn project experience into practical guidance for the next delivery">
            <template #actions>
                <button class="ti-btn ti-btn-primary" @click="openCreate">
                    <i class="ri-add-line me-1"></i>
                    Record Lesson
                </button>
            </template>
        </PageHeader>

        <div class="box mb-4">
            <div class="box-body flex flex-wrap items-center gap-2">
                <button
                    v-for="category in categories"
                    :key="category"
                    class="ti-btn ti-btn-sm capitalize"
                    :class="selectedCategory === category ? 'ti-btn-primary' : 'ti-btn-light'"
                    @click="selectedCategory = category"
                >
                    {{ category === 'all' ? 'All categories' : category }}
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-4 lg:grid-cols-2 xl:grid-cols-3">
            <article v-for="lesson in visibleLessons" :key="lesson.id" class="box mb-0">
                <div class="box-body flex h-full flex-col">
                    <div class="flex items-start justify-between gap-3">
                        <span class="badge capitalize" :class="impactClass(lesson.impact_level)">{{ lesson.impact_level }} impact</span>
                        <div class="flex gap-1">
                            <button class="ti-btn ti-btn-icon ti-btn-soft-primary ti-btn-sm" aria-label="Edit lesson" @click="openEdit(lesson)"><i class="ri-pencil-line"></i></button>
                            <button class="ti-btn ti-btn-icon ti-btn-soft-danger ti-btn-sm" aria-label="Delete lesson" @click="remove(lesson)"><i class="ri-delete-bin-line"></i></button>
                        </div>
                    </div>
                    <p class="mb-2 mt-4 text-xs uppercase tracking-wide text-textmuted">{{ lesson.category }} · {{ lesson.project?.name || 'General' }}</p>
                    <h5 class="mb-3">{{ lesson.title }}</h5>
                    <p class="mb-0 whitespace-pre-line text-textmuted">{{ lesson.recommendation }}</p>
                </div>
            </article>

            <div v-if="!visibleLessons.length" class="box col-span-full mb-0">
                <div class="box-body py-16 text-center text-textmuted">
                    <i class="ri-lightbulb-line text-4xl"></i>
                    <p class="mb-0 mt-3">No lessons have been recorded for this category.</p>
                </div>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/50 p-4" @click.self="closeModal">
            <div class="box mb-0 w-full max-w-2xl">
                <form @submit.prevent="submit">
                    <div class="box-header flex items-center justify-between">
                        <h6 class="box-title mb-0">{{ editingLesson ? 'Edit Lesson' : 'Record Lesson' }}</h6>
                        <button type="button" class="ti-btn ti-btn-icon ti-btn-light ti-btn-sm" aria-label="Close" @click="closeModal"><i class="ri-close-line"></i></button>
                    </div>
                    <div class="box-body grid gap-4">
                        <div>
                            <label class="form-label" for="lesson-title">Key takeaway</label>
                            <input id="lesson-title" v-model="form.title" class="ti-form-control" placeholder="State the lesson clearly" required>
                            <p v-if="form.errors.title" class="mt-1 text-xs text-danger">{{ form.errors.title }}</p>
                        </div>
                        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
                            <div>
                                <label class="form-label" for="lesson-category">Category</label>
                                <input id="lesson-category" v-model="form.category" class="ti-form-control" placeholder="Delivery" required>
                                <p v-if="form.errors.category" class="mt-1 text-xs text-danger">{{ form.errors.category }}</p>
                            </div>
                            <div>
                                <label class="form-label" for="lesson-impact">Project impact</label>
                                <select id="lesson-impact" v-model="form.impact_level" class="ti-form-select">
                                    <option value="low">Low</option>
                                    <option value="medium">Medium</option>
                                    <option value="high">High</option>
                                </select>
                            </div>
                            <div>
                                <label class="form-label" for="lesson-project">Project</label>
                                <select id="lesson-project" v-model="form.project_id" class="ti-form-select">
                                    <option value="">General</option>
                                    <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
                                </select>
                            </div>
                        </div>
                        <div>
                            <label class="form-label" for="lesson-recommendation">Recommendation</label>
                            <textarea id="lesson-recommendation" v-model="form.recommendation" class="ti-form-control" rows="5" placeholder="Describe what the team should repeat, avoid, or improve." required></textarea>
                            <p v-if="form.errors.recommendation" class="mt-1 text-xs text-danger">{{ form.errors.recommendation }}</p>
                        </div>
                    </div>
                    <div class="box-footer flex justify-end gap-2">
                        <button type="button" class="ti-btn ti-btn-light" @click="closeModal">Cancel</button>
                        <button class="ti-btn ti-btn-primary" :disabled="form.processing">{{ editingLesson ? 'Save Changes' : 'Save Lesson' }}</button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
