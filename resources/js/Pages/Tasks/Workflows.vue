<script setup>
import { ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: { type: String, default: 'Workflows' },
  workflows: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  name: '',
  stages: '',
})

const openCreate = () => {
  editing.value = null
  form.reset()
  showModal.value = true
}

const openEdit = (workflow) => {
  editing.value = workflow
  form.name = workflow.name
  form.stages = (workflow.stages || []).join(', ')
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  editing.value
    ? form.put(`/tasks/workflows/${editing.value.id}`, options)
    : form.post('/tasks/workflows', options)
}

const remove = (workflow) => {
  if (confirm(`Delete “${workflow.name}”?`)) {
    router.delete(`/tasks/workflows/${workflow.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout title="Workflows">
    <div class="pm-dash">
      <PageHeader title="Workflows" subtitle="Manage task workflows and stages">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> New Workflow
          </button>
        </template>
      </PageHeader>

      <div class="grid grid-cols-12 gap-6">
        <div v-if="!workflows.length" class="col-span-12">
          <div class="box"><div class="box-body py-12 text-center text-textmuted">No workflows have been created yet.</div></div>
        </div>
        <div v-for="workflow in workflows" :key="workflow.id" class="col-span-12 lg:col-span-6 xl:col-span-4">
          <div class="box">
            <div class="box-header flex items-center justify-between">
              <h5 class="box-title">{{ workflow.name }}</h5>
              <button class="ti-btn ti-btn-sm ti-btn-soft-danger ti-btn-icon" type="button" @click="remove(workflow)"><i class="ri-delete-bin-line"></i></button>
            </div>
            <div class="box-body">
              <div class="flex flex-wrap gap-2 mb-4">
                <span v-for="(stage, idx) in (workflow.stages || [])" :key="idx" class="badge bg-light text-defaulttextcolor">
                  {{ stage }}
                  <i v-if="idx < workflow.stages.length - 1" class="ri-arrow-right-s-line ms-1"></i>
                </span>
              </div>
            </div>
            <div class="box-footer">
              <button class="ti-btn ti-btn-soft-primary ti-btn-sm w-full" type="button" @click="openEdit(workflow)">Edit Workflow</button>
            </div>
          </div>
        </div>
      </div>

      <div v-if="showModal" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Workflow' : 'New Workflow' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Name</label>
              <input v-model="form.name" class="ti-form-control" required>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Stages</label>
              <input v-model="form.stages" class="ti-form-control" placeholder="Backlog, In Progress, Review, Done">
              <p class="text-xs text-textmuted mt-1">Comma-separated stage names.</p>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Workflow</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
