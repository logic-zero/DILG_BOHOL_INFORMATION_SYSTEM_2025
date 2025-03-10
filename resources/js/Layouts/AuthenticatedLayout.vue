<script setup>
import { ref, onMounted } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';

const isSidebarExpanded = ref(true);
const isDropdownOpen = ref(false);
const isLoaded = ref(false);
const page = usePage();

const isActive = (routeName) => page.url.startsWith(routeName);

onMounted(() => {
    const savedSidebarState = localStorage.getItem('isSidebarExpanded');
    isSidebarExpanded.value = savedSidebarState ? JSON.parse(savedSidebarState) : true;
    isLoaded.value = true;
});

const toggleSidebar = () => {
    isSidebarExpanded.value = !isSidebarExpanded.value;
    localStorage.setItem('isSidebarExpanded', JSON.stringify(isSidebarExpanded.value));
};

const toggleDropdown = () => isDropdownOpen.value = !isDropdownOpen.value;

const navLinks = [
    { href: '/dashboard', label: 'Dashboard', icon: 'fas fa-tachometer-alt' },
    { href: '/adminNews', label: 'News', icon: 'fas fa-newspaper' },
    { href: '/adminJobVacancies', label: 'Job Vacancies', icon: 'fas fa-briefcase' },
    { href: '/adminLGUs', label: 'LGUs', icon: 'fas fa-city' },
    { href: '/adminFAQ', label: 'FAQ', icon: 'fas fa-question-circle' },
    { href: '/adminIssuances', label: 'Issuances', icon: 'fas fa-file-alt' },
    { href: '/adminDownloadables', label: 'Downloadables', icon: 'fas fa-download' },
    { href: '/adminKnowledgeMaterials', label: 'Knowledge Materials', icon: 'fas fa-book' },
    { href: '/adminProvOfficials', label: 'Prov. Officials', icon: 'fas fa-users' },
    { href: '/adminCitizensCharter', label: 'Citizens Charter', icon: 'fas fa-file-signature' },
    { href: '/adminLogs', label: 'Logs', icon: 'fas fa-history' },
    { href: '/adminUsers', label: 'Users', icon: 'fas fa-user-cog' }
];
</script>

<template>
    <div v-if="isLoaded" class="min-h-screen bg-gray-100 flex">
        <div :class="{ 'w-64': isSidebarExpanded, 'w-20': !isSidebarExpanded }"
            class="bg-gray-900 text-white border-r border-gray-300 h-screen flex flex-col fixed overflow-hidden transition-all duration-300 ease-in-out">
            <div class="h-16 flex items-center justify-center px-4">
                <img src="/img/dilg-main.png" alt="DILG LOGO" class="w-12 h-12 object-contain" />
                <h1 v-if="isSidebarExpanded" class="text-sm font-semibold ml-3 transition-all duration-300">DILG-BOHOL PROVINCE</h1>
            </div>
            <div class="border-t border-gray-600"></div>
            <div class="flex-1 space-y-1 p-2">
                <Link v-for="item in navLinks" :key="item.href" :href="item.href"
                    class="flex items-center p-3 rounded transition duration-200 text-xs gap-x-3"
                    :class="{ 'justify-center': !isSidebarExpanded, 'bg-gray-700 text-white': isActive(item.href), 'hover:bg-gray-800': !isActive(item.href) }">
                    <i :class="`${item.icon} text-lg ${isActive(item.href) ? 'text-white' : 'text-gray-500'}`"></i>
                    <span v-if="isSidebarExpanded" class="whitespace-nowrap">{{ item.label }}</span>
                </Link>
            </div>
            <div class="flex-1"></div>
            <div class="flex justify-center py-10">
                <button @click="toggleSidebar"
                    class="w-10 h-10 rounded-full bg-gray-800 text-white flex items-center justify-center hover:bg-gray-700 transition">
                    <span v-if="isSidebarExpanded" class="text-lg">《</span>
                    <span v-else class="text-lg">》</span>
                </button>
            </div>
        </div>
        <div :class="{ 'ml-64': isSidebarExpanded, 'ml-20': !isSidebarExpanded }"
            class="flex-1 overflow-auto transition-all duration-300 ease-in-out">
            <nav class="bg-white shadow-md px-6 py-3 flex justify-between items-center relative">
                <div class="relative ml-auto">
                    <button @click="toggleDropdown" class="flex items-center space-x-2 focus:outline-none px-4 py-2 rounded">
                        <span>{{ $page.props.auth.user.name }}</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 transition-transform duration-200"
                            :class="{ 'rotate-180': isDropdownOpen }" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                    <transition name="dropdown">
                        <div v-if="isDropdownOpen" class="absolute right-0 mt-2 w-48 bg-white border rounded-lg shadow-lg z-50">
                            <Link :href="route('profile.edit')" class="block px-4 py-2 hover:bg-gray-100">Profile</Link>
                            <Link :href="route('logout')" method="post" as="button"
                                class="block w-full text-left px-4 py-2 hover:bg-gray-100">Logout</Link>
                        </div>
                    </transition>
                </div>
            </nav>
            <header class="bg-white shadow" v-if="$slots.header">
                <div class="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                    <slot name="header" />
                </div>
            </header>
            <main>
                <slot />
            </main>
        </div>
    </div>
</template>
