<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({
  title: String,
  subtitle: { type: String, default: 'Live records from the project database' },
  items: { type: Array, default: () => [] },
  fields: { type: Array, default: () => [] },
  projects: { type: Array, default: () => [] },
  form: { type: Object, default: null },
})

const showModal = ref(false)
const editing = ref(null)
const canCreate = computed(() => Boolean(props.form?.storeUrl))
const canEdit = computed(() => Boolean(props.form?.updateUrl))
const canDelete = computed(() => Boolean(props.form?.destroyUrl))
const isDocumentList = computed(() => props.form?.storeUrl === '/reports/documents')

const emptyValues = () => Object.fromEntries((props.form?.fields || []).map((field) => [field.name, field.type === 'file' ? null : (field.options?.[0] || '')]))
const formState = useForm(emptyValues())

function value(item, field) {
  return field.path.split('.').reduce((current, key) => current?.[key], item) ?? '—'
}

function openCreate() {
  editing.value = null
  formState.defaults(emptyValues())
  formState.reset()
  showModal.value = true
}

function openEdit(item) {
  editing.value = item
  const values = emptyValues()
  ;(props.form?.fields || []).forEach((field) => {
    if (field.type !== 'file') {
      values[field.name] = item[field.name] ?? ''
    }
  })
  formState.defaults(values)
  formState.reset()
  showModal.value = true
}

function submit() {
  const options = {
    forceFormData: Boolean(props.form?.forceFormData || (props.form?.fields || []).some((field) => field.type === 'file')),
    onSuccess: () => {
      showModal.value = false
      formState.reset()
    },
  }

  if (editing.value && props.form?.updateUrl) {
    formState.put(`${props.form.updateUrl}/${editing.value.id}`, options)
    return
  }

  formState.post(props.form.storeUrl, options)
}

function remove(item) {
  if (!props.form?.destroyUrl || !confirm('Delete this record?')) {
    return
  }

  router.delete(`${props.form.destroyUrl}/${item.id}`, { preserveScroll: true })
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" :subtitle="subtitle">
        <template v-if="canCreate" #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-add-line me-1"></i> {{ form.createLabel || 'Add record' }}
          </button>
        </template>
      </PageHeader>
      <div class="box">
        <div class="box-body p-0">
          <div v-if="!items.length" class="p-12 text-center text-textmuted">No records have been created yet.</div>
          <div v-else class="table-responsive">
            <table class="table table-hover">
              <thead>
                <tr>
                  <th v-for="field in fields" :key="field.path">{{ field.label }}</th>
                  <th v-if="canEdit || canDelete || isDocumentList">Actions</th>
                </tr>
              </thead>
              <tbody>
                <tr v-for="item in items" :key="item.id">
                  <td v-for="field in fields" :key="field.path">{{ value(item, field) }}</td>
                  <td v-if="canEdit || canDelete || isDocumentList">
                    <div class="flex gap-1">
                      <a v-if="isDocumentList" class="ti-btn ti-btn-soft-primary ti-btn-icon ti-btn-sm" :href="`/reports/documents/${item.id}/preview`" target="_blank" rel="noreferrer"><i class="ri-eye-line"></i></a>
                      <a v-if="isDocumentList" class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" :href="`/reports/documents/${item.id}/download`"><i class="ri-download-line"></i></a>
                      <button v-if="canEdit" class="ti-btn ti-btn-soft-info ti-btn-icon ti-btn-sm" type="button" @click="openEdit(item)"><i class="ri-edit-line"></i></button>
                      <button v-if="canDelete" class="ti-btn ti-btn-soft-danger ti-btn-icon ti-btn-sm" type="button" @click="remove(item)"><i class="ri-delete-bin-line"></i></button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </div>

      <div v-if="showModal && form" class="fixed inset-0 z-[80] flex items-center justify-center bg-black/40">
        <form class="bg-white dark:bg-bodybg2 rounded-xl shadow-xl w-full max-w-lg mx-4" @submit.prevent="submit">
          <div class="px-6 py-4 border-b border-defaultborder/60 flex items-center justify-between">
            <h3 class="text-base font-semibold">{{ editing ? 'Edit record' : (form.createLabel || 'Add record') }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div v-for="field in form.fields" :key="field.name">
              <label class="ti-form-label text-sm mb-1">{{ field.label }}</label>
              <input v-if="field.type === 'text' || field.type === 'date'" v-model="formState[field.name]" :type="field.type" class="ti-form-control" :required="field.required">
              <input v-else-if="field.type === 'file'" type="file" class="ti-form-control" :required="field.required && !editing" @input="formState[field.name] = $event.target.files[0]">
              <textarea v-else-if="field.type === 'textarea'" v-model="formState[field.name]" class="ti-form-control" rows="3" :required="field.required"></textarea>
              <select v-else-if="field.type === 'select'" v-model="formState[field.name]" class="ti-form-select" :required="field.required">
                <option v-for="option in field.options" :key="option" :value="option">{{ option }}</option>
              </select>
              <select v-else-if="field.type === 'project'" v-model="formState[field.name]" class="ti-form-select">
                <option value="">Unassigned</option>
                <option v-for="project in projects" :key="project.id" :value="project.id">{{ project.name }}</option>
              </select>
              <p v-if="formState.errors[field.name]" class="text-danger text-xs mt-1">{{ formState.errors[field.name] }}</p>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="formState.processing">Save</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
