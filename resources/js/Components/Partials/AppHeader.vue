<script setup>
import { Link, router, usePage } from '@inertiajs/vue3'
import { computed, ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
  dark: {
    type: Boolean,
    default: false,
  },
  mobileNavOpen: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['toggle-dark', 'toggle-mobile-nav'])

const page = usePage()
const user = computed(() => page.props.auth?.user)
const currentWorkspace = computed(() => page.props.currentWorkspace)
const workspaces = computed(() => page.props.workspaces || [])
const abilities = computed(() => page.props.abilities || {})
const canSwitchWorkspaces = computed(() => Boolean(page.props.canSwitchWorkspaces))
const isWorkspaceSwitcherOpen = ref(false)
const userName = computed(() => user.value?.name || 'Account')
const userEmail = computed(() => user.value?.email || '')
const userInitials = computed(() => {
    const parts = userName.value.split(' ').filter(Boolean).slice(0, 2)
    return parts.map((part) => part[0]).join('').toUpperCase() || '?'
})

const isSearchOpen = ref(false)
const searchQuery = ref('')
const isProfileDropdownOpen = ref(false)

const toggleSearch = () => {
  isSearchOpen.value = !isSearchOpen.value
}

const toggleFullscreen = () => {
  if (!document.fullscreenElement) {
    document.documentElement.requestFullscreen()
  } else {
    document.exitFullscreen()
  }
}

const toggleProfileDropdown = () => {
  isProfileDropdownOpen.value = !isProfileDropdownOpen.value
}

const toggleWorkspaceSwitcher = () => {
  isWorkspaceSwitcherOpen.value = !isWorkspaceSwitcherOpen.value
}

const switchWorkspace = (workspaceId) => {
  isWorkspaceSwitcherOpen.value = false
  router.post('/workspace/switch', { workspace_id: workspaceId }, { preserveScroll: true })
}

const closeProfileOnOutsideClick = (event) => {
  const profileDropdown = document.getElementById('headerProfileDropdown')
  if (profileDropdown && !profileDropdown.closest('.header-element')?.contains(event.target)) {
    isProfileDropdownOpen.value = false
  }
}

onMounted(() => {
  document.addEventListener('click', closeProfileOnOutsideClick)
})

onBeforeUnmount(() => {
  document.removeEventListener('click', closeProfileOnOutsideClick)
})
</script>

<template>
  <header class="app-header pm-header" id="header">
    <div class="main-header-container container-fluid">
      <div class="header-content-left">
        <div class="header-element">
          <div class="horizontal-logo">
            <Link class="header-logo" href="/">
              <img alt="KEDEBEAH ERP Logo" class="desktop-logo" src="/assets/img/Kedebah Logo.png"/>
              <img alt="KEDEBEAH ERP Logo" class="toggle-dark" src="/assets/img/Kedebah Logo.png"/>
              <img alt="KEDEBEAH ERP Logo" class="desktop-dark" src="/assets/img/Kedebah Logo.png"/>
              <img alt="KEDEBEAH ERP Logo" class="desktop-white" src="/assets/img/Kedebah Logo.png"/>
              <img alt="KEDEBEAH ERP Logo" class="toggle-logo" src="/assets/img/Kedebah Logo.png"/>
              <img alt="KEDEBEAH ERP Logo" class="toggle-white" src="/assets/img/Kedebah Logo.png"/>
            </Link>
          </div>
        </div>

        <div class="header-element mx-lg-0">
          <a
            :aria-label="mobileNavOpen ? 'Close menu' : 'Open menu'"
            :aria-expanded="mobileNavOpen"
            class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
            :class="{ 'pm-nav-toggle-open': mobileNavOpen }"
            href="javascript:void(0);"
            @click.stop="$emit('toggle-mobile-nav')"
          >
            <span></span>
          </a>
        </div>

        <div class="header-element header-search pm-header-search md:!block !hidden my-auto auto-complete-search">
          <i class="ri-search-line pm-header-search__icon" aria-hidden="true"></i>
          <input
            v-model="searchQuery"
            autocomplete="off"
            class="header-search-bar form-control"
            placeholder="Search anything here ..."
            type="text"
          />
        </div>
      </div>

      <ul class="header-content-right">
        <li v-if="currentWorkspace || canSwitchWorkspaces" class="header-element relative">
          <button
            v-if="canSwitchWorkspaces"
            type="button"
            class="header-link"
            @click.stop="toggleWorkspaceSwitcher"
          >
            <i class="ri-building-2-line header-link-icon"></i>
            <span class="hidden md:inline text-sm font-medium ms-1">{{ currentWorkspace?.name || 'Workspace' }}</span>
          </button>
          <span v-else class="header-link pointer-events-none">
            <i class="ri-building-2-line header-link-icon"></i>
            <span class="hidden md:inline text-sm font-medium ms-1">{{ currentWorkspace?.name || 'Workspace' }}</span>
          </span>
          <ul
            v-show="canSwitchWorkspaces && isWorkspaceSwitcherOpen"
            class="main-header-dropdown hs-dropdown-menu ti-dropdown-menu pt-0 overflow-hidden header-profile-dropdown"
          >
            <li v-for="workspace in workspaces" :key="workspace.id">
              <button
                type="button"
                class="ti-dropdown-item flex items-center w-full text-start"
                :class="{ 'text-primary': workspace.id === currentWorkspace?.id }"
                @click="switchWorkspace(workspace.id)"
              >
                {{ workspace.name }}
              </button>
            </li>
            <li v-if="abilities.manage_workspace || abilities.manage_members" class="border-t">
              <Link class="ti-dropdown-item flex items-center" href="/workspace">Workspace</Link>
            </li>
            <li v-if="abilities.is_platform_admin">
              <Link class="ti-dropdown-item flex items-center" href="/admin">Admin</Link>
            </li>
          </ul>
        </li>

        <li class="header-element pm-header-search-toggle">
          <a class="header-link" href="javascript:void(0);" @click="toggleSearch">
            <i class="ri-search-line header-link-icon"></i>
          </a>
        </li>

        <li class="header-element">
          <a class="header-link" href="javascript:void(0);" :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'" @click="$emit('toggle-dark')">
            <i class="header-link-icon" :class="dark ? 'ri-sun-line' : 'ri-moon-line'"></i>
          </a>
        </li>

        <li class="header-element header-fullscreen">
          <a class="header-link" href="javascript:void(0);" aria-label="Toggle fullscreen" @click="toggleFullscreen">
            <i class="ri-fullscreen-line header-link-icon"></i>
          </a>
        </li>

        <li class="header-element notifications-dropdown">
          <a class="header-link" href="javascript:void(0);" aria-label="Notifications">
            <i class="ri-notification-3-line header-link-icon"></i>
            <span class="pm-header-pulse"></span>
          </a>
        </li>

        <li class="header-element ti-dropdown hs-dropdown pm-header-user-wrap">
          <a
            class="header-link hs-dropdown-toggle ti-dropdown-toggle pm-header-user"
            href="javascript:void(0);"
            id="headerProfileDropdown"
            @click.stop="toggleProfileDropdown"
            :aria-expanded="isProfileDropdownOpen"
          >
            <span class="avatar avatar-sm bg-primary text-white">{{ userInitials }}</span>
            <span class="pm-header-user__meta">
              <b>{{ userName }}</b>
              <small>{{ userEmail }}</small>
            </span>
            <i class="ri-arrow-down-s-line pm-header-user__caret"></i>
          </a>
          <ul
            v-show="isProfileDropdownOpen"
            class="main-header-dropdown hs-dropdown-menu ti-dropdown-menu pt-0 overflow-hidden header-profile-dropdown"
            aria-labelledby="headerProfileDropdown"
          >
            <li>
              <div class="ti-dropdown-item text-center border-b block">
                <span>{{ userName }}</span>
                <span class="block text-xs text-textmuted">{{ userEmail }}</span>
              </div>
            </li>
            <li>
              <Link class="ti-dropdown-item flex items-center" href="/profile">
                <i class="ri-user-line me-2"></i>Profile
              </Link>
            </li>
            <li>
              <Link class="ti-dropdown-item flex items-center" href="/settings">
                <i class="ri-settings-3-line me-2"></i>Settings
              </Link>
            </li>
            <li class="border-t">
              <Link class="ti-dropdown-item flex items-center w-full text-start" href="/logout" method="post" as="button">
                <i class="ri-logout-box-line me-2"></i>Log Out
              </Link>
            </li>
          </ul>
        </li>
      </ul>
    </div>

    <div v-show="isSearchOpen" class="pm-header-search-mobile md:!hidden">
      <i class="ri-search-line" aria-hidden="true"></i>
      <input
        v-model="searchQuery"
        autocomplete="off"
        class="form-control"
        placeholder="Search anything here ..."
        type="text"
      />
    </div>
  </header>
</template>
