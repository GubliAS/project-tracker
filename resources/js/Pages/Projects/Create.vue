<script setup>
import { computed, ref } from 'vue'
import { router, useForm, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'
import CurrencyPrefix from '@/Components/ui/CurrencyPrefix.vue'
import { useCurrency } from '@/composables/useCurrency'

defineProps({
  title: { type: String, default: 'Create New Project' },
})

const page = usePage()
const { currencies } = useCurrency()

const form = useForm({
  project_type: '',
  name: '',
  description: '',
  start_date: '',
  end_date: '',
  budget: '',
  currency: page.props.currency?.code || 'USD',
  priority: 'medium',
  status: 'planning',
  team: '',
  client: '',
  document_category: 'other',
  settings: {
    phases: '',
    milestones: '',
    deliverables: '',
    sprintDuration: '',
    sprintGoal: '',
    velocity: '',
    methodology: '',
    sprintLength: '',
    phaseCount: '',
  },
})

const isDragging = ref(false)
const selectedDocuments = ref([])
const showPredictiveFields = computed(() => form.project_type === 'predictive')
const showAgileFields = computed(() => form.project_type === 'agile')
const showHybridFields = computed(() => form.project_type === 'hybrid')
const documentError = computed(() => {
  if (form.errors.documents) {
    return form.errors.documents
  }

  const indexed = Object.keys(form.errors)
    .filter((key) => key.startsWith('documents.'))
    .map((key) => form.errors[key])

  return indexed[0] || ''
})

const addDocuments = (fileList) => {
  const incoming = Array.from(fileList || []).filter((file) => file instanceof File)
  selectedDocuments.value = [...selectedDocuments.value, ...incoming]
}

const removeDocument = (index) => {
  selectedDocuments.value = selectedDocuments.value.filter((_, current) => current !== index)
}

const handleSubmit = () => {
  form
    .transform((data) => ({
      ...data,
      documents: selectedDocuments.value,
    }))
    .post('/projects', { forceFormData: true })
}

const handleCancel = () => {
  router.visit('/projects')
}
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Add a new project to your portfolio">
        <template #actions>
          <button type="button" class="ti-btn ti-btn-light" @click="handleCancel">Cancel</button>
          <button type="button" class="ti-btn ti-btn-primary" :disabled="form.processing" @click="handleSubmit">
            <i class="ri-save-line me-1"></i> Save Project
          </button>
        </template>
      </PageHeader>

      <CreateHero :title="title" subtitle="Name the work, pick a method, and set dates." pill="New project" />

      <div class="pm-project-form grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-8">
          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Project Information</h5>
            </div>
            <div class="box-body">
              <div class="grid grid-cols-12 gap-x-5 gap-y-5">
                <div class="col-span-12">
                  <label class="ti-form-label">Project Type <span class="pm-project-form__req">*</span></label>
                  <select v-model="form.project_type" class="ti-form-select">
                    <option value="">Select project type</option>
                    <option value="hybrid">Hybrid</option>
                    <option value="predictive">Predictive</option>
                    <option value="agile">Agile</option>
                  </select>
                  <p class="pm-project-form__hint">Select the project methodology to customize form fields</p>
                </div>

                <div class="col-span-12">
                  <label class="ti-form-label">Project Name <span class="pm-project-form__req">*</span></label>
                  <input v-model="form.name" type="text" class="ti-form-control" placeholder="Enter project name">
                  <p v-if="form.errors.name" class="pm-project-form__error">{{ form.errors.name }}</p>
                </div>
                <div class="col-span-12">
                  <label class="ti-form-label">Description</label>
                  <textarea v-model="form.description" class="ti-form-control" rows="4" placeholder="Enter project description"></textarea>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Start Date <span class="pm-project-form__req">*</span></label>
                  <input v-model="form.start_date" type="date" class="ti-form-control">
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">End Date <span class="pm-project-form__req">*</span></label>
                  <input v-model="form.end_date" type="date" class="ti-form-control">
                  <p v-if="form.errors.end_date" class="pm-project-form__error">{{ form.errors.end_date }}</p>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Currency</label>
                  <select v-model="form.currency" class="ti-form-select">
                    <option v-for="option in currencies" :key="option.code" :value="option.code">
                      {{ option.symbol }} — {{ option.label }}
                    </option>
                  </select>
                  <p class="pm-project-form__hint">Defaults to the workspace currency. This project keeps its own code if the workspace default later changes.</p>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Budget</label>
                  <div class="input-group">
                    <CurrencyPrefix :code="form.currency" />
                    <input v-model="form.budget" type="number" class="ti-form-control" placeholder="0.00">
                  </div>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Priority</label>
                  <select v-model="form.priority" class="ti-form-select">
                    <option value="low">Low</option>
                    <option value="medium">Medium</option>
                    <option value="high">High</option>
                  </select>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Status</label>
                  <select v-model="form.status" class="ti-form-select">
                    <option value="planning">Planning</option>
                    <option value="active">In Progress</option>
                    <option value="on_hold">On Hold</option>
                    <option value="completed">Completed</option>
                  </select>
                </div>
                <div class="col-span-12 md:col-span-6">
                  <label class="ti-form-label">Assigned Team</label>
                  <select v-model="form.team" class="ti-form-select">
                    <option value="">Select team</option>
                    <option value="Development Team">Development Team</option>
                    <option value="Marketing Team">Marketing Team</option>
                    <option value="Design Team">Design Team</option>
                    <option value="QA Team">QA Team</option>
                  </select>
                </div>

                <template v-if="showPredictiveFields">
                  <div class="col-span-12 pm-project-form__section">
                    <h6>Predictive Project Settings</h6>
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Number of Phases</label>
                    <input v-model="form.settings.phases" type="number" class="ti-form-control" placeholder="e.g. 5" min="1">
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Key Milestones</label>
                    <input v-model="form.settings.milestones" type="text" class="ti-form-control" placeholder="e.g. Requirements, Design, Development">
                  </div>
                  <div class="col-span-12">
                    <label class="ti-form-label">Major Deliverables</label>
                    <textarea v-model="form.settings.deliverables" class="ti-form-control" rows="3" placeholder="List major deliverables separated by commas"></textarea>
                  </div>
                </template>

                <template v-if="showAgileFields">
                  <div class="col-span-12 pm-project-form__section">
                    <h6>Agile Project Settings</h6>
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Sprint Duration (weeks)</label>
                    <select v-model="form.settings.sprintDuration" class="ti-form-select">
                      <option value="">Select duration</option>
                      <option value="1">1 week</option>
                      <option value="2">2 weeks</option>
                      <option value="3">3 weeks</option>
                      <option value="4">4 weeks</option>
                    </select>
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Initial Velocity (story points)</label>
                    <input v-model="form.settings.velocity" type="number" class="ti-form-control" placeholder="e.g. 20" min="1">
                  </div>
                  <div class="col-span-12">
                    <label class="ti-form-label">Sprint Goal</label>
                    <textarea v-model="form.settings.sprintGoal" class="ti-form-control" rows="2" placeholder="Describe the primary goal for sprints"></textarea>
                  </div>
                </template>

                <template v-if="showHybridFields">
                  <div class="col-span-12 pm-project-form__section">
                    <h6>Hybrid Project Settings</h6>
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Primary Methodology</label>
                    <select v-model="form.settings.methodology" class="ti-form-select">
                      <option value="">Select methodology</option>
                      <option value="agile-first">Agile-First Hybrid</option>
                      <option value="predictive-first">Predictive-First Hybrid</option>
                      <option value="balanced">Balanced Hybrid</option>
                    </select>
                  </div>
                  <div class="col-span-12 md:col-span-6">
                    <label class="ti-form-label">Sprint Length (weeks)</label>
                    <select v-model="form.settings.sprintLength" class="ti-form-select">
                      <option value="">Select length</option>
                      <option value="1">1 week</option>
                      <option value="2">2 weeks</option>
                      <option value="3">3 weeks</option>
                      <option value="4">4 weeks</option>
                    </select>
                  </div>
                  <div class="col-span-12">
                    <label class="ti-form-label">Number of Phases</label>
                    <input v-model="form.settings.phaseCount" type="number" class="ti-form-control" placeholder="e.g. 3" min="1">
                  </div>
                </template>
              </div>
            </div>
          </div>
        </div>

        <div class="col-span-12 xl:col-span-4">
          <div class="box">
            <div class="box-header">
              <h5 class="box-title">Client Information</h5>
            </div>
            <div class="box-body">
              <div class="pm-project-form__stack">
                <div>
                  <label class="ti-form-label">Client Name</label>
                  <input v-model="form.client" type="text" class="ti-form-control" placeholder="Enter client name">
                </div>
                <div>
                  <label class="ti-form-label">Category</label>
                  <select v-model="form.document_category" class="ti-form-select">
                    <option value="planning">Planning</option>
                    <option value="design">Design</option>
                    <option value="technical">Technical</option>
                    <option value="financial">Financial</option>
                    <option value="quality">Quality</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div>
                  <label class="ti-form-label">Project Documents</label>
                  <label
                    class="pm-project-form__drop"
                    :class="{ 'is-dragover': isDragging }"
                    @dragover.prevent="isDragging = true"
                    @dragleave.prevent="isDragging = false"
                    @drop.prevent="isDragging = false; addDocuments($event.dataTransfer.files)"
                  >
                    <input
                      type="file"
                      multiple
                      class="pm-project-form__drop-input"
                      @change="addDocuments($event.target.files); $event.target.value = ''"
                    >
                    <i class="ri-upload-cloud-2-line" aria-hidden="true"></i>
                    <p>Drag &amp; drop files here or click to browse</p>
                    <span class="pm-project-form__drop-hint">PDF, Office, images, text, CSV, or zip — up to 20 MB each</span>
                  </label>
                  <p v-if="documentError" class="pm-project-form__error">{{ documentError }}</p>
                  <ul v-if="selectedDocuments.length" class="pm-project-form__files">
                    <li v-for="(file, index) in selectedDocuments" :key="`${file.name}-${index}`">
                      <span>{{ file.name }}</span>
                      <button type="button" class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" @click="removeDocument(index)">
                        <i class="ri-close-line"></i>
                      </button>
                    </li>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
