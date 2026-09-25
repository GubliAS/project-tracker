<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Stakeholders' },
  stakeholders: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  name: '',
  role: '',
  department: '',
  influence: 'medium',
  interest: 'medium',
})

const getInfluenceClass = (level) => ({
  high: 'bg-danger/10 text-danger',
  medium: 'bg-warning/10 text-warning',
  low: 'bg-success/10 text-success',
}[level])

const openCreate = () => {
  editing.value = null
  form.reset()
  form.influence = 'medium'
  form.interest = 'medium'
  showModal.value = true
}

const openEdit = (stakeholder) => {
  editing.value = stakeholder
  Object.assign(form, {
    project_id: stakeholder.project_id || '',
    name: stakeholder.name,
    role: stakeholder.role,
    department: stakeholder.department || '',
    influence: stakeholder.influence,
    interest: stakeholder.interest,
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null }
  editing.value
    ? form.transform(() => data).put(`/initiation/stakeholders/${editing.value.id}`, options)
    : form.transform(() => data).post('/initiation/stakeholders', options)
}

const remove = (stakeholder) => {
  if (confirm(`Delete “${stakeholder.name}”?`)) {
    router.delete(`/initiation/stakeholders/${stakeholder.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="Stakeholders" subtitle="Manage project stakeholders and communication">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-user-add-line me-1"></i> Add Stakeholder
          </button>
        </template>
      </PageHeader>

      <CreateHero title="Stakeholders" subtitle="Add the people who influence or care about this work." pill="Directory" />

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-8">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Stakeholder Directory</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Name</th>
                    <th>Role</th>
                    <th>Department</th>
                    <th>Project</th>
                    <th>Influence</th>
                    <th>Interest</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!stakeholders.length">
                    <td colspan="7" class="text-center text-textmuted py-8">No stakeholders have been added yet.</td>
                  </tr>
                  <tr v-for="stakeholder in stakeholders" :key="stakeholder.id">
                    <td class="font-medium">{{ stakeholder.name }}</td>
                    <td>{{ stakeholder.role }}</td>
                    <td class="text-textmuted">{{ stakeholder.department || '—' }}</td>
                    <td>{{ stakeholder.project?.name || '—' }}</td>
                    <td><span class="badge" :class="getInfluenceClass(stakeholder.influence)">{{ stakeholder.influence }}</span></td>
                    <td><span class="badge" :class="getInfluenceClass(stakeholder.interest)">{{ stakeholder.interest }}</span></td>
                    <td>
                      <div class="flex gap-1">
                        <button class="pm-table-action pm-table-action--primary" type="button" title="Edit" @click="openEdit(stakeholder)"><i class="ri-pencil-line"></i></button>
                        <button class="pm-table-action pm-table-action--danger" type="button" title="Delete" @click="remove(stakeholder)"><i class="ri-delete-bin-line"></i></button>
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
            <div class="box-header"><h5 class="box-title">Stakeholder Matrix</h5></div>
            <div class="box-body">
              <div class="grid grid-cols-2 gap-2 text-center text-sm">
                <div class="p-4 bg-danger/10 rounded-2xl"><strong class="text-danger">Manage Closely</strong><p class="text-xs text-textmuted mt-1">High Power, High Interest</p></div>
                <div class="p-4 bg-warning/10 rounded-2xl"><strong class="text-warning">Keep Satisfied</strong><p class="text-xs text-textmuted mt-1">High Power, Low Interest</p></div>
                <div class="p-4 bg-primary/10 rounded-2xl"><strong class="text-primary">Keep Informed</strong><p class="text-xs text-textmuted mt-1">Low Power, High Interest</p></div>
                <div class="p-4 bg-success/10 rounded-2xl"><strong class="text-success">Monitor</strong><p class="text-xs text-textmuted mt-1">Low Power, Low Interest</p></div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Stakeholder' : 'Add Stakeholder' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Name</label>
              <input v-model="form.name" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Role</label>
              <input v-model="form.role" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Department</label>
              <input v-model="form.department" class="ti-form-control">
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Influence</label>
                <select v-model="form.influence" class="ti-form-select">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Interest</label>
                <select v-model="form.interest" class="ti-form-select">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Stakeholder</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
