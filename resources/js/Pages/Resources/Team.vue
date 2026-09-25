<script setup>
import { computed, ref } from 'vue'
import { router, useForm } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Team Resources' },
  resources: { type: Array, default: () => [] },
})

const showModal = ref(false)
const editing = ref(null)
const form = useForm({
  name: '',
  email: '',
  type: 'human',
  role_or_category: '',
  cost_per_hour: 0,
  availability_status: 'available',
  availability_percent: 100,
})

const availability = (member) => {
  if (member.availability_percent !== null && member.availability_percent !== undefined) {
    return Number(member.availability_percent)
  }

  return { available: 100, allocated: 60, unavailable: 25 }[member.availability_status] ?? 0
}

const fullyAvailable = computed(() => props.resources.filter((member) => availability(member) === 100).length)
const partial = computed(() => props.resources.filter((member) => availability(member) < 100 && availability(member) > 50).length)
const overAllocated = computed(() => props.resources.filter((member) => availability(member) <= 50).length)

const openCreate = () => {
  editing.value = null
  form.reset()
  form.type = 'human'
  form.availability_status = 'available'
  form.availability_percent = 100
  showModal.value = true
}

const openEdit = (member) => {
  editing.value = member
  Object.assign(form, {
    name: member.name,
    email: member.email || '',
    type: 'human',
    role_or_category: member.role_or_category || '',
    cost_per_hour: member.cost_per_hour || 0,
    availability_status: member.availability_status,
    availability_percent: availability(member),
  })
  showModal.value = true
}

const submit = () => {
  const options = { onSuccess: () => { showModal.value = false; form.reset() } }
  editing.value ? form.put(`/resources/${editing.value.id}`, options) : form.post('/resources', options)
}

const remove = (member) => {
  if (confirm(`Delete “${member.name}”?`)) {
    router.delete(`/resources/${member.id}`, { preserveScroll: true })
  }
}
</script>

<template>
  <AppLayout title="Team Resources">
    <div class="pm-dash">
      <PageHeader title="Team Resources" subtitle="Manage team members and allocations">
        <template #actions>
          <button class="ti-btn ti-btn-primary" type="button" @click="openCreate">
            <i class="ri-user-add-line me-1"></i> Add Member
          </button>
        </template>
      </PageHeader>

      <CreateHero title="Team Resources" subtitle="Add people and keep allocations visible." pill="Team" />

      <div class="grid grid-cols-12 gap-6">
        <div class="col-span-12 xl:col-span-3">
          <div class="box">
            <div class="box-body text-center">
              <span class="avatar avatar-lg bg-primary/10 text-primary mb-3"><i class="ri-team-line text-2xl"></i></span>
              <h4 class="text-2xl font-bold">{{ resources.length }}</h4>
              <p class="text-textmuted">Team Members</p>
            </div>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box">
            <div class="box-body text-center">
              <span class="avatar avatar-lg bg-success/10 text-success mb-3"><i class="ri-check-double-line text-2xl"></i></span>
              <h4 class="text-2xl font-bold">{{ fullyAvailable }}</h4>
              <p class="text-textmuted">Fully Available</p>
            </div>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box">
            <div class="box-body text-center">
              <span class="avatar avatar-lg bg-warning/10 text-warning mb-3"><i class="ri-time-line text-2xl"></i></span>
              <h4 class="text-2xl font-bold">{{ partial }}</h4>
              <p class="text-textmuted">Partially Allocated</p>
            </div>
          </div>
        </div>
        <div class="col-span-12 xl:col-span-3">
          <div class="box">
            <div class="box-body text-center">
              <span class="avatar avatar-lg bg-danger/10 text-danger mb-3"><i class="ri-user-unfollow-line text-2xl"></i></span>
              <h4 class="text-2xl font-bold">{{ overAllocated }}</h4>
              <p class="text-textmuted">Over-allocated</p>
            </div>
          </div>
        </div>

        <div class="col-span-12">
          <div class="box">
            <div class="box-header"><h5 class="box-title">Team Members</h5></div>
            <div class="box-body p-0">
              <table class="table table-hover whitespace-nowrap">
                <thead>
                  <tr>
                    <th>Member</th>
                    <th>Role</th>
                    <th>Email</th>
                    <th>Availability</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr v-if="!resources.length">
                    <td colspan="6" class="text-center text-textmuted py-8">No team members have been added yet.</td>
                  </tr>
                  <tr v-for="member in resources" :key="member.id">
                    <td>
                      <div class="flex items-center gap-3">
                        <span class="avatar avatar-sm bg-primary/10 text-primary avatar-rounded">
                          {{ member.name.split(' ').map((part) => part[0]).join('') }}
                        </span>
                        <span class="font-medium">{{ member.name }}</span>
                      </div>
                    </td>
                    <td>{{ member.role_or_category || '—' }}</td>
                    <td class="text-textmuted">{{ member.email || '—' }}</td>
                    <td>
                      <div class="flex items-center gap-2">
                        <div class="progress progress-xs flex-1 max-w-[80px]">
                          <div
                            class="progress-bar"
                            :class="{
                              'bg-success': availability(member) === 100,
                              'bg-warning': availability(member) < 100 && availability(member) > 50,
                              'bg-danger': availability(member) <= 50,
                            }"
                            :style="{ width: availability(member) + '%' }"
                          ></div>
                        </div>
                        <span class="text-xs">{{ availability(member) }}%</span>
                      </div>
                    </td>
                    <td><span class="badge bg-primary/10 text-primary">{{ member.availability_status }}</span></td>
                    <td>
                      <div class="flex gap-1">
                        <button class="pm-table-action pm-table-action--primary" type="button" title="Edit" @click="openEdit(member)"><i class="ri-pencil-line"></i></button>
                        <button class="pm-table-action pm-table-action--danger" type="button" title="Delete" @click="remove(member)"><i class="ri-delete-bin-line"></i></button>
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
            <h3 class="text-base font-semibold">{{ editing ? 'Edit Member' : 'Add Member' }}</h3>
            <button class="ti-btn ti-btn-sm ti-btn-icon ti-btn-light" type="button" @click="showModal = false"><i class="ri-close-line"></i></button>
          </div>
          <div class="px-6 py-5 space-y-4">
            <div>
              <label class="ti-form-label text-sm mb-1">Name</label>
              <input v-model="form.name" class="ti-form-control" required>
              <p v-if="form.errors.name" class="text-danger text-xs mt-1">{{ form.errors.name }}</p>
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label class="ti-form-label text-sm mb-1">Email</label>
                <input v-model="form.email" type="email" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Role</label>
                <input v-model="form.role_or_category" class="ti-form-control">
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Cost / Hour</label>
                <input v-model="form.cost_per_hour" type="number" min="0" step="0.01" class="ti-form-control" required>
              </div>
              <div>
                <label class="ti-form-label text-sm mb-1">Availability %</label>
                <input v-model="form.availability_percent" type="number" min="0" max="100" class="ti-form-control">
              </div>
            </div>
            <div>
              <label class="ti-form-label text-sm mb-1">Status</label>
              <select v-model="form.availability_status" class="ti-form-select">
                <option value="available">Available</option>
                <option value="allocated">Allocated</option>
                <option value="unavailable">Unavailable</option>
              </select>
            </div>
          </div>
          <div class="px-6 py-4 border-t border-defaultborder/60 flex justify-end gap-3 bg-light rounded-b-xl">
            <button class="ti-btn ti-btn-light" type="button" @click="showModal = false">Cancel</button>
            <button class="ti-btn ti-btn-primary" :disabled="form.processing">Save Member</button>
          </div>
        </form>
      </div>
    </div>
  </AppLayout>
</template>
