<script setup>
import { computed, ref } from 'vue'
import { Link } from '@inertiajs/vue3'
import AppLayout from '@/Layouts/AppLayout.vue'
import PageHeader from '@/Components/ui/PageHeader.vue'

const props = defineProps({ title: String, projects: { type: Array, default: () => [] } })
const search = ref('')
const projects = computed(() => props.projects.filter((project) => project.name.toLowerCase().includes(search.value.toLowerCase())))
const progress = (project) => project.tasks_count ? Math.round((project.completed_tasks_count / project.tasks_count) * 100) : 0
</script>

<template>
  <AppLayout :title="title">
    <div class="pm-dash">
      <PageHeader :title="title" subtitle="Projects stored in the tracker database">
        <template #actions><Link href="/projects/create" class="ti-btn ti-btn-primary"><i class="ri-add-line me-1" />New Project</Link></template>
      </PageHeader>
      <div class="box"><div class="box-header"><input v-model="search" class="ti-form-control max-w-sm" placeholder="Search projects"></div>
        <div class="box-body p-0"><div v-if="!projects.length" class="p-12 text-center text-textmuted">No projects have been created yet.</div>
          <div v-else class="table-responsive"><table class="table table-hover"><thead><tr><th>Project</th><th>Status</th><th>Tasks</th><th>Progress</th></tr></thead><tbody><tr v-for="project in projects" :key="project.id"><td><Link :href="`/projects/${project.id}`" class="font-medium text-primary">{{ project.name }}</Link><p class="mb-0 text-xs text-textmuted">{{ project.description || 'No description' }}</p></td><td><span class="badge bg-primary/10 text-primary">{{ project.status }}</span></td><td>{{ project.tasks_count }}</td><td>{{ progress(project) }}%</td></tr></tbody></table></div>
        </div>
      </div>
    </div>
  </AppLayout>
</template>
