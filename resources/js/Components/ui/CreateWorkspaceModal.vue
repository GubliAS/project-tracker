<script setup>
import { computed, useId, watch } from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'

const open = defineModel({ type: Boolean, default: false })

const page = usePage()
const uid = useId()
const currencies = computed(() => page.props.currencies || [])
const form = useForm({
  name: '',
  currency: page.props.currency?.code || 'USD',
})

watch(open, (isOpen) => {
  if (isOpen) {
    form.currency = page.props.currency?.code || 'USD'
  }
})

function close() {
  open.value = false
  form.reset()
  form.clearErrors()
}

function submit() {
  form.post('/workspaces', {
    preserveScroll: true,
    onSuccess: () => close(),
  })
}
</script>

<template>
  <Teleport to="body">
    <div
      v-if="open"
      class="pm-workspace-create"
      role="dialog"
      aria-modal="true"
      :aria-labelledby="`${uid}-title`"
      @click.self="close"
    >
      <form class="pm-workspace-create__card" @submit.prevent="submit">
        <p :id="`${uid}-title`" class="pm-workspace-create__title">Create workspace</p>
        <p class="pm-workspace-create__hint">Add another company or team. You will be its workspace admin.</p>
        <label class="ti-form-label" :for="`${uid}-name`">Name</label>
        <input
          :id="`${uid}-name`"
          v-model="form.name"
          type="text"
          class="ti-form-control"
          placeholder="Second company"
          required
          autofocus
        />
        <p v-if="form.errors.name" class="text-danger text-xs mt-1">{{ form.errors.name }}</p>
        <label class="ti-form-label mt-3" :for="`${uid}-currency`">Currency</label>
        <select :id="`${uid}-currency`" v-model="form.currency" class="ti-form-select">
          <option v-for="option in currencies" :key="option.code" :value="option.code">
            {{ option.symbol }} — {{ option.label }}
          </option>
        </select>
        <p v-if="form.errors.currency" class="text-danger text-xs mt-1">{{ form.errors.currency }}</p>
        <div class="pm-workspace-create__actions">
          <button type="button" class="ti-btn ti-btn-light btn-wave" @click="close">Cancel</button>
          <button type="submit" class="ti-btn ti-btn-primary btn-wave" :disabled="form.processing">
            Create workspace
          </button>
        </div>
      </form>
    </div>
  </Teleport>
</template>
