<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'DoR / DoD Framework' },
  dorItems: { type: Array, default: () => [] },
  dodItems: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
})

const showModal = ref(false)
const form = useForm({
  kind: 'dor',
  text: '',
  project_id: '',
  is_checked: false,
})

const completionRate = (items) => items.length ? Math.round((items.filter((item) => item.is_checked).length / items.length) * 100) : 0

const openCreate = (kind) => {
  form.reset()
  form.kind = kind
  showModal.value = true
}

const submit = () => {
  form.transform((data) => ({ ...data, project_id: data.project_id || null })).post('/agile/definitions', {
    onSuccess: () => {
      showModal.value = false
      form.reset()
    },
  })
}

const toggle = (item) => {
  router.put(`/agile/definitions/${item.id}`, { is_checked: !item.is_checked }, { preserveScroll: true })
}

const remove = (item) => {
  if (confirm('Delete this criteria?')) {
    router.delete(`/agile/definitions/${item.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout>
    <div class="pm-dash">
      <PageHeader title="DoR / DoD Framework" subtitle="Definition of Ready and Definition of Done checklists" />

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-6">
          <div class="box">
            <div class="box-header bg-primary/10">
              <div class="flex items-center justify-between">
                <h5 class="box-title text-primary"><i class="ri-checkbox-circle-line me-2"></i>Definition of Ready (DoR)</h5>
                <span class="badge bg-primary">{{ completionRate(dorItems) }}%</span>
              </div>
            </div>
            <div class="box-body">
              <p class="text-textmuted mb-4">Criteria that must be met before a story can be taken into a sprint.</p>
              <p v-if="!dorItems.length" class="text-textmuted">No DoR criteria yet.</p>
              <ul class="space-y-3">
                <li v-for="item in dorItems" :key="item.id" class="flex items-start gap-3 p-3 bg-light rounded-lg">
                  <input type="checkbox" class="ti-form-check-input mt-1" :checked="item.is_checked" @change="toggle(item)">
                  <span class="grow" :class="{ 'line-through text-textmuted': item.is_checked }">{{ item.text }}</span>
                  <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(item)"><i class="ri-delete-bin-line"></i></button>
                </li>
              </ul>
            </div>
            <div class="box-footer">
              <button class="ti-btn ti-btn-primary ti-btn-sm" type="button" @click="openCreate('dor')"><i class="ri-add-line me-1"></i> Add Criteria</button>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-6">
          <div class="box">
            <div class="box-header bg-success/10">
              <div class="flex items-center justify-between">
                <h5 class="box-title text-success"><i class="ri-check-double-line me-2"></i>Definition of Done (DoD)</h5>
                <span class="badge bg-success">{{ completionRate(dodItems) }}%</span>
              </div>
            </div>
            <div class="box-body">
              <p class="text-textmuted mb-4">Criteria that must be met before a story is considered complete.</p>
              <p v-if="!dodItems.length" class="text-textmuted">No DoD criteria yet.</p>
              <ul class="space-y-3">
                <li v-for="item in dodItems" :key="item.id" class="flex items-start gap-3 p-3 bg-light rounded-lg">
                  <input type="checkbox" class="ti-form-check-input mt-1" :checked="item.is_checked" @change="toggle(item)">
                  <span class="grow" :class="{ 'line-through text-textmuted': item.is_checked }">{{ item.text }}</span>
                  <button class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(item)"><i class="ri-delete-bin-line"></i></button>
                </li>
              </ul>
            </div>
            <div class="box-footer">
              <button class="ti-btn ti-btn-success ti-btn-sm" type="button" @click="openCreate('dod')"><i class="ri-add-line me-1"></i> Add Criteria</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">Add {{ form.kind === 'dor' ? 'DoR' : 'DoD' }} Criteria</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Criteria</label>
              <input v-model="form.text" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Project</label>
              <select v-model="form.project_id" class="ti-form-select">
                <option value="">All projects</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Criteria</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
