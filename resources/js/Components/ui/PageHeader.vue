<script setup>
import { computed } from 'vue'
import { Link, usePage } from '@inertiajs/vue3'

const props = defineProps({
  title: {
    type: String,
    required: true
  },
  subtitle: {
    type: String,
    default: ''
  }
})

const page = usePage()

const breadcrumbs = computed(() => {
  const crumbs = [{ label: 'Home', to: '/' }]
  
  // Build breadcrumbs from route path
  const pathParts = page.url.split('?')[0].split('/').filter(Boolean)
  let currentPath = ''
  
  pathParts.forEach((part, index) => {
    currentPath += `/${part}`
    const isLast = index === pathParts.length - 1
    crumbs.push({
      label: part.charAt(0).toUpperCase() + part.slice(1).replace(/-/g, ' '),
      to: isLast ? null : currentPath
    })
  })
  
  return crumbs
})
</script>

<template>
  <div class="flex items-center justify-between page-header-breadcrumb flex-wrap gap-2">
    <div>
      <nav>
        <ol class="breadcrumb mb-1">
          <li 
            v-for="(crumb, index) in breadcrumbs" 
            :key="index" 
            class="breadcrumb-item"
            :class="{ 'active': !crumb.to }"
            :aria-current="!crumb.to ? 'page' : undefined"
          >
            <i
              v-if="index > 0"
              class="ri-arrow-right-s-line breadcrumb-separator"
              aria-hidden="true"
            ></i>
            <Link v-if="crumb.to" :href="crumb.to">{{ crumb.label }}</Link>
            <span v-else>{{ crumb.label }}</span>
          </li>
        </ol>
      </nav>
      <h1 class="page-title font-semibold text-xl mb-0 tracking-tight">{{ title }}</h1>
      <p v-if="subtitle" class="text-textmuted dark:text-textmuted/50 text-sm mt-1">{{ subtitle }}</p>
    </div>
    <div class="btn-list flex flex-wrap items-center gap-2">
      <slot name="actions"></slot>
    </div>
  </div>
</template>


