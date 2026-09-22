<script setup>
import { onMounted, onUnmounted, ref } from 'vue'
import { Link, router, usePage } from '@inertiajs/vue3'

const page = usePage()
const openMenus = ref([])

const menuItems = [
  {
    id: 'dashboard',
    label: 'Dashboard',
    icon: 'ri-home-line',
    href: '/'
  },
  {
    id: 'projects',
    label: 'Projects',
    icon: 'ri-folder-line',
    href: '/projects',
    children: [
      { label: 'Projects List', href: '/projects' },
      { label: 'Create Project', href: '/projects/create' },
      { label: 'Project Details', href: '/projects/1' }
    ]
  },
  {
    id: 'initiation',
    label: 'Initiation',
    icon: 'ri-rocket-line',
    href: '/initiation',
    children: [
      { label: 'Kick-Off', href: '/initiation/kickoff' },
      { label: 'Stakeholders', href: '/initiation/stakeholders' }
    ]
  },
  {
    id: 'agile',
    label: 'Agile',
    icon: 'ri-loop-left-line',
    href: '/agile',
    children: [
      { label: 'Sprints', href: '/agile/sprints' },
      { label: 'Backlog', href: '/agile/backlog' },
      { label: 'DoR / DoD', href: '/agile/definitions' }
    ]
  },
  {
    id: 'tasks',
    label: 'Tasks',
    icon: 'ri-checkbox-circle-line',
    href: '/tasks',
    children: [
      { label: 'Task List', href: '/tasks' },
      { label: 'Kanban Board', href: '/tasks/kanban' },
      { label: 'Workflows', href: '/tasks/workflows' }
    ]
  },
  {
    id: 'resources',
    label: 'Resources',
    icon: 'ri-team-line',
    href: '/resources/team',
    children: [
      { label: 'Team', href: '/resources/team' },
      { label: 'Time Tracking', href: '/resources/time-tracking' },
      { label: 'Budget', href: '/resources/budget' },
      { label: 'Milestones', href: '/resources/milestones' },
      { label: 'Gantt Chart', href: '/resources/gantt' }
    ]
  },
  {
    id: 'quality',
    label: 'Quality',
    icon: 'ri-shield-check-line',
    href: '/quality/qa-testing',
    children: [
      { label: 'QA & Testing', href: '/quality/qa-testing' },
      { label: 'Risks & Issues', href: '/quality/risks' },
      { label: 'Change Log', href: '/quality/change-log' }
    ]
  },
  {
    id: 'reports',
    label: 'Reports',
    icon: 'ri-bar-chart-box-line',
    href: '/reports/analytics',
    children: [
      { label: 'Analytics', href: '/reports/analytics' },
      { label: 'Documents', href: '/reports/documents' },
      { label: 'Lessons Learned', href: '/reports/lessons-learned' }
    ]
  },
  {
    id: 'chat',
    label: 'Chat',
    icon: 'ri-chat-3-line',
    href: '/chat'
  }
]

const toggleMenu = (menuId) => {
  const index = openMenus.value.indexOf(menuId)
  if (index > -1) {
    openMenus.value.splice(index, 1)
  } else {
    openMenus.value = [menuId]
  }
}

const closeMenus = () => {
  openMenus.value = []
}

onMounted(() => {
  const stop = router.on('navigate', closeMenus)
  onUnmounted(stop)
})

const isMenuOpen = (menuId) => {
  return openMenus.value.includes(menuId)
}

const normalizePath = (path) => path.split('?')[0].replace(/\/$/, '') || '/'

const isActive = (href) => {
  const currentPath = normalizePath(page.url)
  const targetPath = normalizePath(href)

  if (targetPath === '/') {
    return currentPath === '/' || currentPath === '/dashboard'
  }

  return currentPath === targetPath || currentPath.startsWith(`${targetPath}/`)
}

const isChildActive = (children) => {
  return children?.some(child => isActive(child.href))
}
</script>

<template>
  <aside class="app-sidebar sticky pm-nav" id="sidebar">
    <div class="container-xl">
      <div class="main-sidebar" id="sidebar-scroll">
        <nav class="main-menu-container nav nav-pills sub-open">
          <!-- Slide Left Arrow -->
          <div class="slide-left" id="slide-left">
            <svg fill="#7b8191" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
              <path d="M13.293 6.293 7.586 12l5.707 5.707 1.414-1.414L10.414 12l4.293-4.293z"></path>
            </svg>
          </div>

          <!-- Menu Items -->
          <ul class="main-menu">
            <li
              v-for="item in menuItems"
              :key="item.id"
              class="slide"
              :class="{
                'has-sub': item.children,
                'open': isMenuOpen(item.id),
                'active': isChildActive(item.children) || isActive(item.href)
              }"
            >
              <template v-if="item.children">
                <a
                  href="#"
                  class="side-menu__item"
                  :class="{ 'active': isChildActive(item.children) }"
                  @click.prevent="toggleMenu(item.id)"
                >
                  <i :class="[item.icon, 'side-menu__icon']"></i>
                  <span class="side-menu__label">{{ item.label }}</span>
                  <i class="ri-arrow-down-s-line side-menu__angle"></i>
                </a>
                <ul
                  v-if="isMenuOpen(item.id)"
                  class="pm-dropdown-menu"
                >
                  <li v-for="child in item.children" :key="child.href">
                    <Link
                      :href="child.href"
                      :class="{ 'active': isActive(child.href) }"
                    >
                      {{ child.label }}
                    </Link>
                  </li>
                </ul>
              </template>

              <!-- Simple menu item (no children) -->
              <template v-else>
                <Link
                  :href="item.href"
                  class="side-menu__item"
                  :class="{ 'active': isActive(item.href) }"
                >
                  <i :class="[item.icon, 'side-menu__icon']"></i>
                  <span class="side-menu__label">{{ item.label }}</span>
                </Link>
              </template>
            </li>
          </ul>

          <!-- Slide Right Arrow -->
          <div class="slide-right" id="slide-right">
            <svg fill="#7b8191" height="24" viewBox="0 0 24 24" width="24" xmlns="http://www.w3.org/2000/svg">
              <path d="M10.707 17.707 16.414 12l-5.707-5.707-1.414 1.414L13.586 12l-4.293 4.293z"></path>
            </svg>
          </div>
        </nav>
      </div>
    </div>
  </aside>
</template>

<style>
.pm-dropdown-menu {
  position: absolute !important;
  top: calc(100% + 0.4rem) !important;
  left: 0 !important;
  min-width: 13.5rem !important;
  background-color: #fff !important;
  border: 1px solid var(--pm-border-soft, rgb(15 23 42 / 0.08)) !important;
  border-radius: 1rem !important;
  box-shadow: 0 16px 40px rgb(15 23 42 / 0.12) !important;
  padding: 0.4rem !important;
  z-index: 1100 !important;
  margin: 0 !important;
  list-style: none !important;
  display: block !important;
}

.pm-dropdown-menu li {
  display: block !important;
}

.pm-dropdown-menu a {
  display: block !important;
  padding: 0.55rem 0.85rem !important;
  color: #374151 !important;
  white-space: nowrap !important;
  text-decoration: none !important;
  border-radius: 0.7rem !important;
  font-size: 0.875rem !important;
  font-weight: 500 !important;
}

.pm-dropdown-menu a:hover {
  background-color: rgb(var(--primary) / 0.1) !important;
  color: rgb(var(--primary)) !important;
}

.pm-dropdown-menu a.active {
  color: rgb(var(--primary)) !important;
  background-color: rgb(var(--primary) / 0.12) !important;
}

.dark .pm-dropdown-menu {
  background-color: rgb(var(--custom-white)) !important;
  border-color: var(--pm-border-soft) !important;
  box-shadow: 0 16px 40px rgb(4 6 16 / 0.45) !important;
}

.dark .pm-dropdown-menu a {
  color: rgb(var(--default-text-color) / 0.86) !important;
}

.dark .pm-dropdown-menu a:hover,
.dark .pm-dropdown-menu a.active {
  background-color: rgb(var(--primary) / 0.16) !important;
  color: #c8c4ff !important;
}
</style>
