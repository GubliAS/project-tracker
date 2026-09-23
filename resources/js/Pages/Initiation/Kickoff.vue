<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Project Kick-Off' },
  kickoffs: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  scheduled_on: '',
  attendees: 5,
  status: 'scheduled',
})

const latest = computed(() => props.kickoffs[0])
const objectives = computed(() => latest.value?.objectives || [])

const openCreate = () => {
  editing.value = null
  form.reset()
  form.attendees = 5
  form.status = 'scheduled'
  showModal.value = true
}

const openEdit = (kickoff) => {
  editing.value = kickoff
  Object.assign(form, {
    project_id: kickoff.project_id,
    scheduled_on: kickoff.scheduled_on,
    attendees: kickoff.attendees,
    status: kickoff.status,
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  editing.value
    ? form.put(`/initiation/kickoff/${editing.value.id}`, options)
    : form.post('/initiation/kickoff', options)
}

const remove = (kickoff) => {
  if (confirm('Delete this kick-off?')) {
    router.delete(`/initiation/kickoff/${kickoff.id}`, { preserveScroll: true })
  }
}

const toggleObjective = (index) => {
  if (!latest.value) {
    return
  }

  const next = objectives.value.map((item, i) => i === index ? { ...item, completed: !item.completed } : item)
  router.put(`/initiation/kickoff/${latest.value.id}`, { objectives: next }, { preserveScroll: true })
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="Project Kick-Off" subtitle="Initialize and launch new projects">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Schedule Kick-Off
          </button>
        </template>
      </PageHeader>

      <CreateHero title="Project Kick-Off" subtitle="Schedule a launch meeting and keep the checklist close." pill="Kick-off" />

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-8">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Kick-Off Meetings</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Project</th>
                    <th>Date</th>
                    <th>Attendees</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!kickoffs.length">
                    <td colspan="5" class="text-center text-textmuted py-8">No kick-off meetings have been scheduled yet.</td>
                  </tr>
                  <tr v-for="kickoff in kickoffs" :key="kickoff.id">
                    <td class="font-medium">{{ kickoff.project?.name || '—' }}</td>
                    <td>{{ kickoff.scheduled_on }}</td>
                    <td>{{ kickoff.attendees }} people</td>
                    <td>
                      <span class="badge" :class="kickoff.status === 'completed' ? 'bg-success/10 text-success' : 'bg-warning/10 text-warning'">{{ kickoff.status }}</span>
                    </td>
                    <td>
                      <div class="flex gap-1">
                        <button class="ti-btn ti-btn-soft-info ti-btn-sm" type="button" @click="openEdit(kickoff)">Edit</button>
                        <button class="ti-btn ti-btn-soft-danger ti-btn-sm" type="button" @click="remove(kickoff)">Delete</button>
                      </div>
                    </td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-4">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Kick-Off Checklist</h5></div>
            <div class="box-body">
              <p v-if="!objectives.length" class="text-textmuted mb-0">Checklist appears after a kick-off is scheduled with objectives.</p>
              <ul v-else class="space-y-3">
                <li v-for="(obj, index) in objectives" :key="index" class="flex items-center gap-3">
                  <input type="checkbox" class="ti-form-check-input" :checked="obj.completed" @change="toggleObjective(index)">
                  <span :class="{ 'line-through text-textmuted': obj.completed }">{{ obj.text }}</span>
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Kick-Off' : 'Schedule Kick-Off' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select" required>
                <option value="" disabled>Select a project</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Kick-Off Date</label>
                <input v-model="form.scheduled_on" type="date" class="ti-form-control" required>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Expected Attendees</label>
                <input v-model="form.attendees" type="number" min="0" class="ti-form-control">
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Status</label>
              <select v-model="form.status" class="ti-form-select">
                <option value="scheduled">Scheduled</option>
                <option value="completed">Completed</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Kick-Off</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
