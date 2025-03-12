<template>
    <div>
        <header>
            <nav
                class="bg-blue-800 text-white text-sm flex items-center justify-between xl:justify-center xl:px-32 relative">
                <Link href="/" class="font-bold text-lg mr-10 px-4 py-2 whitespace-nowrap">DILG-BOHOL PROVINCE</Link>

                <button @click="isMenuOpen = !isMenuOpen" class="xl:hidden text-white focus:outline-none px-3 py-2">
                    <span v-if="!isMenuOpen">☰</span>
                    <span v-else>✕</span>
                </button>

                <div class="hidden xl:flex xl:items-center">
                    <Link href="/" class="px-4 py-2 md:px-4 md:py-3 hover:bg-blue-900 hover:underline">Home</Link>
                    <Link href="/guestNews" class="px-4 py-2 md:px-4 md:py-3 hover:bg-blue-900 hover:underline">News
                    </Link>

                    <div v-for="(menu, index) in menus" :key="index" class="relative"
                        @mouseenter="setDropdown(index, true)" @mouseleave="setDropdown(index, false)">
                        <button @click="toggleDropdown(index)"
                            class="w-full whitespace-nowrap md:w-auto px-4 py-2 md:px-4 md:py-3 hover:bg-blue-900 hover:underline flex items-center gap-1">
                            {{ menu.name }}
                            <span class="text-xs transition-transform duration-200"
                                :class="{ 'rotate-180': activeDropdown === index }">▼</span>
                        </button>
                        <ul v-show="activeDropdown === index"
                            class="md:absolute left-0 top-full w-full md:w-48 bg-blue-900 text-white shadow-lg py-1.5 md:py-2 z-10">
                            <li v-for="(item, i) in menu.dropdown" :key="i"
                                class="px-4 py-1.5 md:px-4 md:py-2 hover:underline border-t border-gray-600 first:border-none">
                                <template v-if="item.link.startsWith('http')">
                                    <a :href="item.link" class="block" target="_blank" rel="noopener noreferrer">{{
                                        item.name }}</a>
                                </template>
                                <template v-else>
                                    <Link :href="item.link" class="block">{{ item.name }}</Link>
                                </template>
                            </li>
                        </ul>
                    </div>
                </div>
                <transition name="mobile-menu">
                    <div v-if="isMenuOpen"
                        class="xl:hidden absolute top-full left-0 w-full bg-blue-800 flex flex-col shadow-md">
                        <Link href="/" :class="{ 'bg-blue-900 text-white underline': page.url === '/' }"
                            class="px-4 py-2 hover:bg-blue-900 hover:underline" @click="closeMenu">Home</Link>
                        <Link href="/guestNews"
                            :class="{ 'bg-blue-900 text-white underline': page.url === '/guestNews' }"
                            class="px-4 py-2 hover:bg-blue-900 hover:underline" @click="closeMenu">News</Link>

                        <div v-for="(menu, index) in menus" :key="index" class="relative">
                            <button @click="toggleDropdown(index)"
                                class="w-full px-4 py-2 hover:bg-blue-900 hover:underline flex items-center gap-1"
                                :class="{ 'bg-blue-900 underline': activeDropdown === index || isDropdownActive(menu) }">
                                {{ menu.name }}
                                <span class="text-xs transition-transform duration-200"
                                    :class="{ 'rotate-180': activeDropdown === index }">▼</span>
                            </button>
                            <transition name="dropdown-mobile">
                                <ul v-if="activeDropdown === index" class="bg-blue-900 text-white shadow-lg py-1.5">
                                    <li v-for="(item, i) in menu.dropdown" :key="i"
                                        class="px-4 py-1.5 hover:underline border-t border-gray-600 first:border-none">
                                        <template v-if="item.link.startsWith('http')">
                                            <a :href="item.link" class="block" target="_blank" rel="noopener noreferrer"
                                                @click="closeMenu">{{ item.name }}</a>
                                        </template>
                                        <template v-else>
                                            <Link :href="item.link"
                                                :class="{ 'bg-blue-900 text-white underline font-bold': page.url === item.link }"
                                                class="block" @click="closeMenu">{{ item.name }}</Link>
                                        </template>
                                    </li>
                                </ul>
                            </transition>
                        </div>
                    </div>
                </transition>
            </nav>
        </header>

        <main>
            <div class="p-4 bg-gray-500 bg-opacity-20">
                <Link href="/" class="inline-block">
                <img class="w-full max-w-[500px] h-auto" src="/img/dilg-bohol.png" alt="DILG Bohol">
                </Link>
            </div>
            <slot />
        </main>

        <footer class="text-center mt-10">
            <div class="w-full bg-gray-900 text-gray-400 py-6">
                <a href="#" class="block text-gray-300 text-lg font-semibold hover:underline">
                    Department of the Interior and Local Government
                </a>
                <p class="text-sm mt-1">BOHOL PROVINCE</p>
                <p class="text-base font-medium mt-4">DILG FAMILY</p>
                <div class="flex justify-center gap-4 mt-4">
                    <a href="https://r7.napolcom.gov.ph/">
                        <img src="/img/napolcom.png" alt="NAPOLCOM" class="h-12">
                    </a>
                    <a href="https://region7.bfp.gov.ph/">
                        <img src="/img/bfp.png" alt="BFP" class="h-12">
                    </a>
                    <a href="https://www.bjmp.gov.ph/">
                        <img src="/img/bjmp.png" alt="BJMP" class="h-12">
                    </a>
                    <a href="https://pro7.pnp.gov.ph/">
                        <img src="/img/pnp.png" alt="PNP" class="h-12">
                    </a>
                </div>
                <p class="text-xs text-gray-500 mt-4">&copy; DILG-BOHOL PROVINCE 2023</p>
            </div>

            <div class="w-full bg-gray-300 py-6">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                    <!-- Left Column -->
                    <div class="flex flex-col items-center">
                        <p class="text-xs font-medium uppercase">Republic of the Philippines</p>
                        <img src="/img/govph-seal.png" class="h-32 w-32 mt-2" alt="Government Seal">
                    </div>

                    <!-- Middle Column -->
                    <div class="flex flex-col items-center">
                        <p class="text-xs font-medium uppercase">Government Links</p>
                        <div class="flex flex-col items-center mt-2 space-y-1">
                            <a href="https://president.gov.ph/" target="_blank" class="text-xs hover:underline">Office
                                of the
                                President</a>
                            <a href="https://ovp.gov.ph/" target="_blank" class="text-xs hover:underline">Office of the
                                Vice
                                President</a>
                            <a href="http://legacy.senate.gov.ph/" target="_blank"
                                class="text-xs hover:underline">Senate of the
                                Philippines</a>
                            <a href="https://www.congress.gov.ph/" target="_blank" class="text-xs hover:underline">House
                                of
                                Representatives</a>
                            <a href="https://sc.judiciary.gov.ph/" target="_blank"
                                class="text-xs hover:underline">Supreme
                                Court</a>
                            <a href="https://ca.judiciary.gov.ph/" target="_blank" class="text-xs hover:underline">Court
                                of
                                Appeals</a>
                            <a href="https://sb.judiciary.gov.ph/" target="_blank"
                                class="text-xs hover:underline">Sandiganbayan</a>
                        </div>
                    </div>

                    <!-- Right Column -->
                    <div class="flex flex-col items-center">
                        <!-- Still not complete -->
                        <VisitorCounter />
                    </div>
                </div>
            </div>
        </footer>

    </div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import VisitorCounter from '../Components/VisitorCounter.vue';

const page = usePage();
const isMenuOpen = ref(false);
const activeDropdown = ref(null);

const isDropdownActive = (menu) => {
    return menu.dropdown.some(item => page.url.startsWith(item.link));
};

const toggleDropdown = (index) => {
    if (window.innerWidth <= 1280) {
        activeDropdown.value = activeDropdown.value === index ? null : index;
    }
};

const closeMenu = () => {
    isMenuOpen.value = false;
    activeDropdown.value = null;
};


const setDropdown = (index, isOpen) => {
    if (window.innerWidth > 1280) {
        activeDropdown.value = isOpen ? index : null;
    }
};

const menus = [
    { name: "Officials", dropdown: [{ name: "LGUs", link: "/LGUs" }, { name: "Provincial Officials", link: "/provincialOfficials" }] },
    { name: "LGRRC", dropdown: [{ name: "DILG E-Library", link: "https://library.dilg.gov.ph/" }, { name: "Knowledge Materials", link: "/knowledgeMaterials" }] },
    {
        name: "Issuances", dropdown: [
            { name: "Latest Issuances", link: "/latestIssuances" },
            { name: "Joint Circulars", link: "https://dilg.gov.ph/issuances-archive/jc/" },
            { name: "Memo Circulars", link: "https://dilg.gov.ph/issuances-archive/mc/" },
            { name: "Presidential Directives", link: "https://dilg.gov.ph/issuances-archive/pd/" },
            { name: "Draft Issuances", link: "https://dilg.gov.ph/issuances-archive/draft/" },
            { name: "Republic Acts", link: "https://dilg.gov.ph/issuances-archive/ra/" },
            { name: "Legal Opinions", link: "/legalOpinions" },
        ]
    },
    {
        name: "About", dropdown: [
            { name: "About Us", link: "/aboutUs" },
            { name: "Organizational Structure/PDMU", link: "/organizationalStructure" },
            { name: "Field Officers", link: "/fieldOfficers" },
            { name: "Citizens Charter", link: "/citizensCharter" },
            { name: "DILG FAMILY", link: "/DILGFAMILY" },
            { name: "Contact Information", link: "/contactInformation" },
        ]
    },
    { name: "Programs & Services", dropdown: [{ name: "Projects", link: "https://subaybayan.dilg.gov.ph/projects/index?ProjectSearch%5BREGION_C%5D=07&ProjectSearch%5BPROVINCE_C%5D=012&ProjectSearch%5BCITYMUN_C%5D=&ProjectSearch%5Bbarangay%5D=&ProjectSearch%5BimageSelection%5D=&ProjectSearch%5BPROGRAM_C%5D=&ProjectSearch%5BPROJECT_TYPE%5D=&ProjectSearch%5BYEAR%5D=&ProjectSearch%5BSTATUS%5D=" }, { name: "FDP Portal", link: "https://fdpp.dilg.gov.ph/fdpp/report/index?_csrf-frontend=RBVssfr9QXZl60bNe0juzDQvRsKtxA-pS7A64znU1ugNUCqAvpV3IVCIFpQYGq38Umd29-isadsTglaWaL7moA%3D%3D&document_filter=&region_filter=07&province_filter=012&lgu_filter=&year_filter=" }] },
    { name: "Transparency at Work", dropdown: [{ name: "Downloadables", link: "/downloadables" }, { name: "FAQ's", link: "/FAQs" }, { name: "Job Vacancies", link: "/jobVacancies" }] },
];
</script>

<style>
@media (max-width: 1280px) {

    .mobile-menu-enter-active,
    .mobile-menu-leave-active {
        transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
        overflow: hidden;
    }

    .mobile-menu-enter-from,
    .mobile-menu-leave-to {
        max-height: 0;
        opacity: 0;
    }

    .mobile-menu-enter-to,
    .mobile-menu-leave-from {
        max-height: 500px;
        opacity: 1;
    }
}

@media (max-width: 1280px) {

    .dropdown-mobile-enter-active,
    .dropdown-mobile-leave-active {
        transition: max-height 0.3s ease-in-out, opacity 0.3s ease-in-out;
        overflow: hidden;
    }

    .dropdown-mobile-enter-from,
    .dropdown-mobile-leave-to {
        max-height: 0;
        opacity: 0;
    }

    .dropdown-mobile-enter-to,
    .dropdown-mobile-leave-from {
        max-height: 300px;
        opacity: 1;
    }
}
</style>
