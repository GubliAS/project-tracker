<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Time Tracking' },
  entries: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  resources: { type: Array, default: () => [] },
  tasks: { type: Array, default: () => [] },
})

const showModal = ref(false)
const form = useForm({
  project_id: '',
  resource_id: '',
  task_id: '',
  entry_date: new Date().toISOString().slice(0, 10),
  hours: 1,
  description: '',
})

const hoursFor = (predicate) => props.entries.filter(predicate).reduce((sum, entry) => sum + Number(entry.hours || 0), 0)
const today = new Date().toISOString().slice(0, 10)
const startOfWeek = (() => {
  const date = new Date()
  date.setDate(date.getDate() - date.getDay())
  return date.toISOString().slice(0, 10)
})()
const startOfMonth = new Date().toISOString().slice(0, 7)

const todayHours = computed(() => hoursFor((entry) => (entry.entry_date || '').startsWith(today)))
const weeklyTotal = computed(() => hoursFor((entry) => entry.entry_date >= startOfWeek))
const monthlyTotal = computed(() => hoursFor((entry) => (entry.entry_date || '').startsWith(startOfMonth)))

const submit = () => {
  form.transform((data) => ({
    ...data,
    project_id: data.project_id || null,
    resource_id: data.resource_id || null,
    task_id: data.task_id || null,
  })).post('/resources/time-tracking', {
    onSuccess: () => {
      showModal.value = false
      form.reset()
      form.entry_date = today
      form.hours = 1
    },
  })
}

const remove = (entry) => {
  if (confirm('Delete this time entry?')) {
    router.delete(`/resources/time-tracking/${entry.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout title="Time Tracking">
    <div class="pm-dash">
      <PageHeader title="Time Tracking" subtitle="Track time spent on projects and tasks">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="showModal = true">
            <i class="ri-time-line me-1"></i> Log Time
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-primary/10 text-primary"><i class="ri-time-line text-2xl"></i></span><div><p class="text-textmuted text-sm">Today</p><h4 class="text-xl font-bold">{{ todayHours }} hrs</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-success/10 text-success"><i class="ri-calendar-check-line text-2xl"></i></span><div><p class="text-textmuted text-sm">This Week</p><h4 class="text-xl font-bold">{{ weeklyTotal }} hrs</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-info/10 text-info"><i class="ri-calendar-line text-2xl"></i></span><div><p class="text-textmuted text-sm">This Month</p><h4 class="text-xl font-bold">{{ monthlyTotal }} hrs</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-warning/10 text-warning"><i class="ri-list-check-2 text-2xl"></i></span><div><p class="text-textmuted text-sm">Entries</p><h4 class="text-xl font-bold">{{ entries.length }}</h4></div></div></div></div>
        </div>

        <div class="col-span-12">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Recent Time Entries</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Task</th>
                    <th>Project</th>
                    <th>Team Member</th>
                    <th>Date</th>
                    <th>Hours</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!entries.length">
                    <td colspan="6" class="text-center text-textmuted py-8">No time entries have been logged yet.</td>
                  </tr>
                  <tr v-for="entry in entries" :key="entry.id">
                    <td class="font-medium">{{ entry.task?.title || entry.description || '—' }}</td>
                    <td class="text-textmuted">{{ entry.project?.name || '—' }}</td>
                    <td>{{ entry.resource?.name || '—' }}</td>
                    <td>{{ entry.entry_date }}</td>
                    <td><span class="badge bg-primary/10 text-primary">{{ entry.hours }} hrs</span></td>
                    <td>
                      <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(entry)"><i class="ri-delete-bin-line"></i></button>
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
            <h3 class="text-base font-semibold">Log Time</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Team Member</label>
              <select v-model="form.resource_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="resource in resources" :key="resource.id" :value="resource.id">{{ resource.name }}</option>
              </select>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Task</label>
              <select v-model="form.task_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="task in tasks" :key="task.id" :value="task.id">{{ task.title }}</option>
              </select>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Date</label>
                <input v-model="form.entry_date" type="date" class="ti-form-control" required>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Hours</label>
                <input v-model="form.hours" type="number" min="0.25" max="24" step="0.25" class="ti-form-control" required>
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Description</label>
              <input v-model="form.description" class="ti-form-control">
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Entry</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
