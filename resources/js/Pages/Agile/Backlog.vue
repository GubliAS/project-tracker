<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Product Backlog' },
  items: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  sprints: { type: Array, default: () => [] },
})

const typeFilter = ref('all')
const priorityFilter = ref('all')
const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  sprint_id: '',
  title: '',
  type: 'story',
  priority: 'medium',
  points: 0,
  status: 'backlog',
})

const filtered = computed(() => props.items.filter((item) => {
  const typeOk = typeFilter.value === 'all' || item.type === typeFilter.value
  const priorityOk = priorityFilter.value === 'all' || item.priority === priorityFilter.value
  return typeOk && priorityOk
}))

const getTypeClass = (type) => ({ epic: 'bg-purple-500/10 text-purple-500', feature: 'bg-primary/10 text-primary', story: 'bg-info/10 text-info' }[type])
const getPriorityClass = (priority) => ({ high: 'bg-danger/10 text-danger', medium: 'bg-warning/10 text-warning', low: 'bg-success/10 text-success' }[priority])

const openCreate = () => {
  editing.value = null
  form.reset()
  form.type = 'story'
  form.priority = 'medium'
  form.status = 'backlog'
  showModal.value = true
}

const openEdit = (item) => {
  editing.value = item
  Object.assign(form, {
    project_id: item.project_id || '',
    sprint_id: item.sprint_id || '',
    title: item.title,
    type: item.type,
    priority: item.priority,
    points: item.points,
    status: item.status,
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null, sprint_id: form.sprint_id || null }
  editing.value
    ? form.transform(() => data).put(`/agile/backlog/${editing.value.id}`, options)
    : form.transform(() => data).post('/agile/backlog', options)
}

const remove = (item) => {
  if (confirm(`Delete “${item.title}”?`)) {
    router.delete(`/agile/backlog/${item.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="Product Backlog" subtitle="Prioritize and manage backlog items">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Add Item
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-3">
          <div class="box h-full"><div class="box-body text-center"><span class="avatar avatar-lg avatar-rounded pm-icon-tile bg-primary/10 text-primary mx-auto mb-3"><i class="ri-stack-line text-2xl"></i></span><h4 class="text-2xl font-bold">{{ items.length }}</h4><p class="text-textmuted">Total Items</p></div></div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box h-full"><div class="box-body text-center"><span class="avatar avatar-lg avatar-rounded pm-icon-tile bg-success/10 text-success mx-auto mb-3"><i class="ri-checkbox-circle-line text-2xl"></i></span><h4 class="text-2xl font-bold">{{ items.filter((item) => item.status === 'ready').length }}</h4><p class="text-textmuted">Ready for Sprint</p></div></div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box h-full"><div class="box-body text-center"><span class="avatar avatar-lg avatar-rounded pm-icon-tile bg-danger/10 text-danger mx-auto mb-3"><i class="ri-fire-line text-2xl"></i></span><h4 class="text-2xl font-bold">{{ items.filter((item) => item.priority === 'high').length }}</h4><p class="text-textmuted">High Priority</p></div></div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box h-full"><div class="box-body text-center"><span class="avatar avatar-lg avatar-rounded pm-icon-tile bg-info/10 text-info mx-auto mb-3"><i class="ri-bar-chart-horizontal-line text-2xl"></i></span><h4 class="text-2xl font-bold">{{ items.reduce((sum, item) => sum + Number(item.points || 0), 0) }}</h4><p class="text-textmuted">Total Points</p></div></div>
        </div>

        <div class="col-span-12">
          <div class="box">
            <div class="box-header flex items-center justify-between">
              <h5 class="box-title">Backlog Items</h5>
              <div class="flex gap-2">
                <select v-model="typeFilter" class="ti-form-select form-select-sm w-auto">
                  <option value="all">All Types</option>
                  <option value="epic">Epic</option>
                  <option value="feature">Feature</option>
                  <option value="story">Story</option>
                </select>
                <select v-model="priorityFilter" class="ti-form-select form-select-sm w-auto">
                  <option value="all">All Priorities</option>
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
            </div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Title</th>
                    <th>Type</th>
                    <th>Priority</th>
                    <th>Points</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!filtered.length">
                    <td colspan="6" class="text-center text-textmuted py-8">No backlog items match these filters.</td>
                  </tr>
                  <tr v-for="item in filtered" :key="item.id">
                    <td class="font-medium">{{ item.title }}</td>
                    <td><span class="badge" :class="getTypeClass(item.type)">{{ item.type }}</span></td>
                    <td><span class="badge" :class="getPriorityClass(item.priority)">{{ item.priority }}</span></td>
                    <td>{{ item.points }}</td>
                    <td>
                      <span class="badge" :class="{
                        'bg-primary/10 text-primary': item.status === 'in-progress',
                        'bg-success/10 text-success': item.status === 'ready' || item.status === 'done',
                        'bg-secondary/10 text-secondary': item.status === 'backlog',
                      }">{{ item.status }}</span>
                    </td>
                    <td>
                      <div class="flex gap-1">
                        <button class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" type="button" @click="openEdit(item)"><i class="ri-edit-line"></i></button>
                        <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(item)"><i class="ri-delete-bin-line"></i></button>
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
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Item' : 'Add Item' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Title</label>
              <input v-model="form.title" class="ti-form-control" required>
            </div>
            <div class="grid grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Type</label>
                <select v-model="form.type" class="ti-form-select">
                  <option value="epic">Epic</option>
                  <option value="feature">Feature</option>
                  <option value="story">Story</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Priority</label>
                <select v-model="form.priority" class="ti-form-select">
                  <option value="high">High</option>
                  <option value="medium">Medium</option>
                  <option value="low">Low</option>
                </select>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Points</label>
                <input v-model="form.points" type="number" min="0" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Status</label>
                <select v-model="form.status" class="ti-form-select">
                  <option value="backlog">Backlog</option>
                  <option value="ready">Ready</option>
                  <option value="in-progress">In progress</option>
                  <option value="done">Done</option>
                </select>
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Sprint</label>
              <select v-model="form.sprint_id" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="sprint in sprints" :key="sprint.id" :value="sprint.id">{{ sprint.name }}</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Item</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
