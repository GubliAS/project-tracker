<script setup>
import { computed } from 'vue'
import { Form, Link, usePage } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'
import CreateHero from '@/Components/ui/CreateHero.vue'

const props = defineProps({
  title: { type: String, default: 'Settings' },
  workspace: { type: Object, default: null },
})

const page = usePage()
const abilities = computed(() => page.props.abilities || {})
const currencies = computed(() => page.props.currencies || [])
const canEditCurrency = computed(() => Boolean(abilities.value.manage_workspace || abilities.value.is_platform_admin))
const currentCurrency = computed(() => {
  const code = props.workspace?.currency || page.props.currency?.code
  return currencies.value.find((option) => option.code === code) || page.props.currency
})
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" :subtitle="workspace?.name || 'Workspace defaults'">
        <template #actions>
          <Link href="/profile" class="ti-btn ti-btn-light btn-wave">Profile</Link>
          <Link v-if="abilities.manage_workspace" href="/workspace/settings" class="ti-btn ti-btn-light btn-wave">
            Workspace
          </Link>
        </template>
      </PageHeader>

      <CreateHero
        :title="title"
        :subtitle="workspace?.name ? `Defaults for ${workspace.name}.` : 'Workspace defaults for money and new projects.'"
        pill="Settings"
        icon="ri-settings-3-line"
        src="/assets/img/settings-illustration.png"
      />

      <div class="box mb-4">
        <div class="box-header">
          <h6 class="box-title mb-0">Default currency</h6>
        </div>
        <div class="box-body">
          <Form v-if="canEditCurrency" action="/settings" method="put" #default="{ errors, processing }">
            <div class="max-w-xl space-y-4">
              <div>
                <label class="ti-form-label" for="workspace-currency">Workspace currency</label>
                <select id="workspace-currency" name="currency" class="ti-form-select" :value="workspace?.currency" required>
                  <option v-for="option in currencies" :key="option.code" :value="option.code">
                    {{ option.symbol }} — {{ option.label }}
                  </option>
                </select>
                <p v-if="errors.currency" class="text-danger text-xs mt-1">{{ errors.currency }}</p>
                <p class="text-textmuted text-xs mt-2 mb-0">
                  New projects default to this currency. Existing projects keep the currency they were created with.
                </p>
              </div>
              <button type="submit" class="ti-btn ti-btn-primary btn-wave" :disabled="processing">Save settings</button>
            </div>
          </Form>
          <div v-else class="max-w-xl">
            <p class="mb-1">
              <span class="font-medium">{{ currentCurrency?.symbol }}</span>
              {{ currentCurrency?.label || currentCurrency?.code }}
            </p>
            <p class="text-textmuted text-xs mb-0">
              Only a workspace admin can change the default currency.
            </p>
          </div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
