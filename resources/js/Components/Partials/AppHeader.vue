<script setup>
import { ref, onMounted } from 'vue';
import { Link } from '@inertiajs/vue3';

const emit = defineEmits(['toggle-dark']);

const searchQuery = ref('');
const isProfileDropdownOpen = ref(false);

const toggleFullscreen = () => {
    if (!document.fullscreenElement) {
        document.documentElement.requestFullscreen();
    } else {
        document.exitFullscreen();
    }
};

const toggleProfileDropdown = () => {
    isProfileDropdownOpen.value = !isProfileDropdownOpen.value;
};

onMounted(() => {
    document.addEventListener('click', (event) => {
        const profileDropdown = document.getElementById('headerProfileDropdown');
        if (profileDropdown && !profileDropdown.closest('.header-element')?.contains(event.target)) {
            isProfileDropdownOpen.value = false;
        }
    });
});
</script>

<template>
    <header class="app-header sticky" id="header">
        <div class="main-header-container container-fluid">
            <div class="header-content-left">
                <div class="header-element">
                    <div class="horizontal-logo">
                        <Link class="header-logo" href="/">
                            <img
                                alt="Project Tracker Logo"
                                class="desktop-logo"
                                src="/assets/img/Kedebah Logo.png"
                            />
                            <img
                                alt="Project Tracker Logo"
                                class="toggle-dark"
                                src="/assets/img/Kedebah Logo.png"
                            />
                            <img
                                alt="Project Tracker Logo"
                                class="desktop-dark"
                                src="/assets/img/Kedebah Logo.png"
                            />
                            <img
                                alt="Project Tracker Logo"
                                class="toggle-logo"
                                src="/assets/img/Kedebah Logo.png"
                            />
                        </Link>
                    </div>
                </div>

                <div class="header-element mx-lg-0">
                    <a
                        aria-label="Hide Sidebar"
                        class="sidemenu-toggle header-link animated-arrow hor-toggle horizontal-navtoggle"
                        href="javascript:void(0);"
                    >
                        <span></span>
                    </a>
                </div>

                <div class="header-element header-search md:!block !hidden my-auto auto-complete-search">
                    <input
                        v-model="searchQuery"
                        autocomplete="off"
                        class="header-search-bar form-control"
                        placeholder="Search anything here ..."
                        type="text"
                    />
                    <a class="header-search-icon border-0" href="javascript:void(0);">
                        <i class="ri-search-line"></i>
                    </a>
                </div>
            </div>

            <ul class="header-content-right">
                <li class="header-element md:!hidden block">
                    <a class="header-link" href="javascript:void(0);">
                        <i class="bi bi-search header-link-icon"></i>
                    </a>
                </li>

                <li class="header-element">
                    <a class="header-link" href="javascript:void(0);" @click="emit('toggle-dark')">
                        <i class="ri-moon-line header-link-icon"></i>
                    </a>
                </li>

                <li class="header-element header-fullscreen">
                    <a class="header-link" href="javascript:void(0);" @click="toggleFullscreen">
                        <i class="ri-fullscreen-line header-link-icon"></i>
                    </a>
                </li>

                <li class="header-element notifications-dropdown">
                    <a class="header-link" href="javascript:void(0);">
                        <i class="ri-notification-3-line header-link-icon"></i>
                        <span class="header-icon-pulse bg-primary rounded pulse pulse-secondary"></span>
                    </a>
                </li>

                <li class="header-element ti-dropdown hs-dropdown">
                    <a
                        class="header-link hs-dropdown-toggle ti-dropdown-toggle"
                        href="javascript:void(0);"
                        id="headerProfileDropdown"
                        :aria-expanded="isProfileDropdownOpen"
                        @click="toggleProfileDropdown"
                    >
                        <div class="flex items-center">
                            <span class="avatar avatar-sm bg-primary text-white">PM</span>
                        </div>
                    </a>
                    <ul
                        v-show="isProfileDropdownOpen"
                        class="main-header-dropdown hs-dropdown-menu ti-dropdown-menu pt-0 overflow-hidden header-profile-dropdown"
                        aria-labelledby="headerProfileDropdown"
                        style="position: absolute; right: 0; top: 100%; z-index: 1000; min-width: 200px;"
                    >
                        <li>
                            <div class="ti-dropdown-item text-center border-b block">
                                <span>Project Manager</span>
                                <span class="block text-xs text-textmuted">Admin</span>
                            </div>
                        </li>
                        <li>
                            <Link class="ti-dropdown-item flex items-center" href="/profile">
                                <i class="ri-user-line me-2"></i>Profile
                            </Link>
                        </li>
                        <li>
                            <a class="ti-dropdown-item flex items-center" href="javascript:void(0);">
                                <i class="ri-settings-3-line me-2"></i>Settings
                            </a>
                        </li>
                        <li class="border-t">
                            <a class="ti-dropdown-item flex items-center" href="javascript:void(0);">
                                <i class="ri-logout-box-line me-2"></i>Log Out
                            </a>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>
    </header>
</template>

<style scoped>
.header-link-icon {
    font-size: 1.25rem;
    line-height: 1;
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.header-link {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
}

.header-logo {
    display: flex;
    align-items: center;
    text-decoration: none;
}

.header-logo img {
    max-height: 48px;
    height: auto;
    width: auto;
    object-fit: contain;
}

.header-logo:hover img {
    opacity: 0.9;
}
</style>
