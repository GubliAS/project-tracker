<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Budget Management' },
  items: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  summary: { type: Object, default: () => ({}) },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  project_id: '',
  category: '',
  allocated: 0,
  spent: 0,
  status: 'on-track',
})

const formatCurrency = (amount) => new Intl.NumberFormat('en-US', { style: 'currency', currency: 'USD', maximumFractionDigits: 0 }).format(amount || 0)
const percent = (item) => item.allocated > 0 ? Math.round((Number(item.spent) / Number(item.allocated)) * 100) : 0

const openCreate = () => {
  editing.value = null
  form.reset()
  form.status = 'on-track'
  showModal.value = true
}

const openEdit = (item) => {
  editing.value = item
  Object.assign(form, {
    project_id: item.project_id || '',
    category: item.category,
    allocated: item.allocated,
    spent: item.spent,
    status: item.status,
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  const data = { ...form.data(), project_id: form.project_id || null }
  editing.value
    ? form.transform(() => data).put(`/resources/budget/${editing.value.id}`, options)
    : form.transform(() => data).post('/resources/budget', options)
}

const remove = (item) => {
  if (confirm(`Delete “${item.category}”?`)) {
    router.delete(`/resources/budget/${item.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout title="Budget Management">
    <div class="pm-dash">
      <PageHeader title="Budget Management" subtitle="Track and manage project budgets">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> Add Expense
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-primary/10 text-primary"><i class="ri-money-dollar-circle-line text-2xl"></i></span><div><p class="text-textmuted text-sm">Total Budget</p><h4 class="text-xl font-bold">{{ formatCurrency(summary.total_budget) }}</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-warning/10 text-warning"><i class="ri-shopping-cart-line text-2xl"></i></span><div><p class="text-textmuted text-sm">Spent</p><h4 class="text-xl font-bold">{{ formatCurrency(summary.spent) }}</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-success/10 text-success"><i class="ri-wallet-line text-2xl"></i></span><div><p class="text-textmuted text-sm">Remaining</p><h4 class="text-xl font-bold">{{ formatCurrency(summary.remaining) }}</h4></div></div></div></div>
        </div>
        <div class="col-span-12 md:col-span-6 xl:col-span-3">
          <div class="box"><div class="box-body"><div class="flex items-center gap-4"><span class="avatar avatar-lg bg-info/10 text-info"><i class="ri-line-chart-line text-2xl"></i></span><div><p class="text-textmuted text-sm">Projected</p><h4 class="text-xl font-bold">{{ formatCurrency(summary.projected) }}</h4></div></div></div></div>
        </div>

        <div class="col-span-12">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Budget Breakdown by Category</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Category</th>
                    <th>Project</th>
                    <th>Allocated</th>
                    <th>Spent</th>
                    <th>Progress</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!items.length">
                    <td colspan="7" class="text-center text-textmuted py-8">No budget items have been created yet.</td>
                  </tr>
                  <tr v-for="item in items" :key="item.id">
                    <td class="font-medium">{{ item.category }}</td>
                    <td>{{ item.project?.name || '—' }}</td>
                    <td>{{ formatCurrency(item.allocated) }}</td>
                    <td>{{ formatCurrency(item.spent) }}</td>
                    <td>
                      <div class="flex items-center gap-2 min-w-[150px]">
                        <div class="progress progress-sm flex-1">
                          <div class="progress-bar" :class="{ 'bg-success': item.status === 'under', 'bg-primary': item.status === 'on-track', 'bg-danger': item.status === 'over' }" :style="{ width: percent(item) + '%' }"></div>
                        </div>
                        <span class="text-xs">{{ percent(item) }}%</span>
                      </div>
                    </td>
                    <td>
                      <span class="badge" :class="{ 'bg-success/10 text-success': item.status === 'under', 'bg-primary/10 text-primary': item.status === 'on-track', 'bg-danger/10 text-danger': item.status === 'over' }">{{ item.status }}</span>
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
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Budget Item' : 'Add Budget Item' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Category</label>
              <input v-model="form.category" class="ti-form-control" required>
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
                <label class="ti-form-label text-sm mb-1">Allocated</label>
                <input v-model="form.allocated" type="number" min="0" step="0.01" class="ti-form-control" required>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Spent</label>
                <input v-model="form.spent" type="number" min="0" step="0.01" class="ti-form-control" required>
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Status</label>
              <select v-model="form.status" class="ti-form-select">
                <option value="on-track">On track</option>
                <option value="under">Under</option>
                <option value="over">Over</option>
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
