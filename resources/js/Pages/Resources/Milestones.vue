<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Milestones' },
  milestones: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  title: '',
  due_date: '',
  status: 'upcoming',
})

const statusClass = (status) => ({
  completed: 'bg-success',
  in_progress: 'bg-primary',
  delayed: 'bg-danger',
  upcoming: 'bg-gray-300',
}[status] || 'bg-gray-300')

const badgeClass = (status) => ({
  completed: 'bg-success/10 text-success',
  in_progress: 'bg-primary/10 text-primary',
  delayed: 'bg-danger/10 text-danger',
  upcoming: 'bg-secondary/10 text-secondary',
}[status] || 'bg-secondary/10 text-secondary')

const openCreate = () => {
  editing.value = null
  form.reset()
  form.status = 'upcoming'
  showModal.value = true
}

const openEdit = (milestone) => {
  editing.value = milestone
  Object.assign(form, {
    project_id: milestone.project_id || '',
    title: milestone.title,
    due_date: milestone.due_date || '',
    status: milestone.status,
  })
  showModal.value = true
}

const submit = () => {
  const payload = {
    onSuccess: () => {
      showModal.value = false
      form.reset()
    },
  }
  const data = { ...form.data(), project_id: form.project_id || null }
  editing.value
    ? form.transform(() => data).put(`/resources/milestones/${editing.value.id}`, payload)
    : form.transform(() => data).post('/resources/milestones', payload)
}

const remove = (milestone) => {
  if (confirm(`Delete “${milestone.title}”?`)) {
    router.delete(`/resources/milestones/${milestone.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout title="Milestones">
    <div class="pm-dash">
      <PageHeader title="Milestones" subtitle="Track project milestones and deliverables">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Add Milestone
          </button>
        </template>
      </PageHeader>

      <CreateHero title="Milestones" subtitle="Mark the dates that tell you the work is landing." pill="Milestone" />

      <div class="box">
        <div class="box-body">
          <p v-if="!milestones.length" class="text-center text-textmuted py-8 mb-0">No milestones have been created yet.</p>
          <div v-else class="relative">
            <div class="absolute left-8 top-0 bottom-0 w-0.5 bg-gray-200 dark:bg-gray-700"></div>
            <div class="space-y-8">
              <div v-for="milestone in milestones" :key="milestone.id" class="relative flex items-start gap-4 pl-8">
                <span class="absolute left-6 w-4 h-4 rounded-full border-2 !border-defaultborder dark:!border-defaultborder/10" :class="statusClass(milestone.status)"></span>
                <div class="flex-1 p-4 bg-light rounded-lg">
                  <div class="flex items-center justify-between mb-2">
                    <h6 class="font-medium">{{ milestone.title }}</h6>
                    <div class="flex items-center gap-2">
                      <span class="badge" :class="badgeClass(milestone.status)">{{ milestone.status }}</span>
                      <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" type="button" @click="openEdit(milestone)"><i class="ri-edit-line"></i></button>
                      <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(milestone)"><i class="ri-delete-bin-line"></i></button>
                    </div>
                  </div>
                  <div class="flex items-center gap-4 text-sm text-textmuted">
                    <span><i class="ri-calendar-line me-1"></i>{{ milestone.due_date || 'No due date' }}</span>
                    <span><i class="ri-folder-line me-1"></i>{{ milestone.project?.name || 'Unassigned' }}</span>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Milestone' : 'Add Milestone' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Title</label>
              <input v-model="form.title" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Due date</label>
                <input v-model="form.due_date" type="date" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="form.status" class="ti-form-select">
                  <option value="upcoming">Upcoming</option>
                  <option value="in_progress">In progress</option>
                  <option value="completed">Completed</option>
                  <option value="delayed">Delayed</option>
                </select>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Milestone</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
