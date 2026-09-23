<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Sprints' },
  sprints: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  currentSprint: { type: Object, default: null },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  name: '',
  goal: '',
  start_date: '',
  end_date: '',
  status: 'planned',
  story_points: 0,
  completed_points: 0,
})

const current = computed(() => props.currentSprint)
const daysRemaining = computed(() => {
  if (!current.value?.end_date) {
    return 0
  }

  return Math.max(0, Math.ceil((new Date(current.value.end_date) - new Date()) / 86400000))
})
const progress = computed(() => {
  if (!current.value?.story_points) {
    return 0
  }

  return Math.round((Number(current.value.completed_points) / Number(current.value.story_points)) * 100)
})

const openCreate = () => {
  editing.value = null
  form.reset()
  form.status = 'planned'
  showModal.value = true
}

const openEdit = (sprint) => {
  editing.value = sprint
  Object.assign(form, {
    project_id: sprint.project_id || '',
    name: sprint.name,
    goal: sprint.goal || '',
    start_date: sprint.start_date || '',
    end_date: sprint.end_date || '',
    status: sprint.status,
    story_points: sprint.story_points,
    completed_points: sprint.completed_points,
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null }
  editing.value
    ? form.transform(() => data).put(`/agile/sprints/${editing.value.id}`, options)
    : form.transform(() => data).post('/agile/sprints', options)
}

const remove = (sprint) => {
  if (confirm(`Delete “${sprint.name}”?`)) {
    router.delete(`/agile/sprints/${sprint.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="Sprints" subtitle="Manage sprint cycles and iterations">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> New Sprint
          </button>
        </template>
      </PageHeader>

      <CreateHero title="Sprints" subtitle="Plan a cycle, set a goal, and track remaining points." pill="Sprint" />

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-4">
          <div class="box">
            <div class="box-header">
              <div class="flex items-center justify-between">
                <h5 class="box-title">{{ current?.name || 'No active sprint' }}</h5>
                <span v-if="current" class="badge bg-primary/10 text-primary">{{ current.status }}</span>
              </div>
            </div>
            <div class="box-body">
              <p v-if="!current" class="text-textmuted mb-0">Create a sprint to see progress here.</p>
              <template v-else>
                <p class="text-textmuted mb-4">{{ current.goal || 'No sprint goal yet.' }}</p>
                <div class="text-center mb-4">
                  <span class="text-4xl font-bold text-primary">{{ daysRemaining }}</span>
                  <span class="text-textmuted block">days remaining</span>
                </div>
                <div class="mb-4">
                  <div class="flex justify-between text-sm mb-1">
                    <span>Progress</span>
                    <span>{{ progress }}%</span>
                  </div>
                  <div class="progress progress-sm">
                    <div class="progress-bar bg-primary" :style="{ width: progress + '%' }"></div>
                  </div>
                  <div class="flex justify-between text-xs text-textmuted mt-1">
                    <span>{{ current.completed_points }} points</span>
                    <span>{{ current.story_points }} points</span>
                  </div>
                </div>
              </template>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-8">
          <div class="box">
            <div class="box-header"><h5 class="box-title">All Sprints</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Sprint</th>
                    <th>Duration</th>
                    <th>Status</th>
                    <th>Progress</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!sprints.length">
                    <td colspan="5" class="text-center text-textmuted py-8">No sprints have been created yet.</td>
                  </tr>
                  <tr v-for="sprint in sprints" :key="sprint.id">
                    <td class="font-medium">{{ sprint.name }}</td>
                    <td class="text-textmuted">{{ sprint.start_date || '—' }} - {{ sprint.end_date || '—' }}</td>
                    <td>
                      <span class="badge" :class="{
                        'bg-primary/10 text-primary': sprint.status === 'active',
                        'bg-success/10 text-success': sprint.status === 'completed',
                        'bg-secondary/10 text-secondary': sprint.status === 'planned',
                      }">{{ sprint.status }}</span>
                    </td>
                    <td>
                      <div class="flex items-center gap-2 min-w-[120px]">
                        <div class="progress progress-xs flex-1">
                          <div class="progress-bar bg-primary" :style="{ width: (sprint.story_points ? (sprint.completed_points / sprint.story_points) * 100 : 0) + '%' }"></div>
                        </div>
                        <span class="text-xs">{{ sprint.completed_points }}/{{ sprint.story_points }}</span>
                      </div>
                    </td>
                    <td>
                      <div class="flex gap-1">
                        <button class="ti-btn ti-btn-soft-info ti-btn-sm" type="button" @click="openEdit(sprint)">Edit</button>
                        <button class="ti-btn ti-btn-soft-danger ti-btn-sm" type="button" @click="remove(sprint)">Delete</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Sprint' : 'New Sprint' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Name</label>
              <input v-model="form.name" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Goal</label>
              <textarea v-model="form.goal" class="ti-form-control" rows="3"></textarea>
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
                <label class="ti-form-label text-sm mb-1">Start</label>
                <input v-model="form.start_date" type="date" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">End</label>
                <input v-model="form.end_date" type="date" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Story points</label>
                <input v-model="form.story_points" type="number" min="0" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Completed points</label>
                <input v-model="form.completed_points" type="number" min="0" class="ti-form-control">
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Status</label>
              <select v-model="form.status" class="ti-form-select">
                <option value="planned">Planned</option>
                <option value="active">Active</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Sprint</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
