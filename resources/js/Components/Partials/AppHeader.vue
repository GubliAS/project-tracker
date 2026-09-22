<script setup>
import { Link } from '@inertiajs/vue3'
import { ref, onMounted, onBeforeUnmount } from 'vue'

defineProps({
  dark: {
    type: Boolean,
    default: false,
  },
})

defineEmits(['toggle-dark'])

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
  <header class="app-header sticky pm-header" id="header">
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
          <a aria-label="Hide Sidebar" class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle" href="javascript:void(0);">
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
            <span class="avatar avatar-sm bg-primary text-white">PM</span>
            <span class="pm-header-user__meta">
              <b>Project Manager</b>
              <small>Admin</small>
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
                <span>Project Manager</span>
                <span class="block text-xs text-textmuted">Admin</span>
              </div>
            </li>
            <li><a class="ti-dropdown-item flex items-center" href="javascript:void(0);"><i class="ri-user-line me-2"></i>Profile</a></li>
            <li><a class="ti-dropdown-item flex items-center" href="javascript:void(0);"><i class="ri-settings-3-line me-2"></i>Settings</a></li>
            <li class="border-t"><a class="ti-dropdown-item flex items-center" href="javascript:void(0);"><i class="ri-logout-box-line me-2"></i>Log Out</a></li>
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
