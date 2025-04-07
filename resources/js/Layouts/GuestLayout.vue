<template>
    <div>
        <header class="sticky top-0 z-50">
            <nav class="bg-blue-800 text-white text-sm flex items-center justify-between xl:justify-center xl:px-32 relative">
                <Link href="/" class="font-bold text-lg mr-10 px-4 py-2 whitespace-nowrap">DILG-BOHOL PROVINCE</Link>

                <button @click="isMenuOpen = !isMenuOpen" class="text-xl xl:hidden border border-gray-400 rounded text-gray-400 focus:outline-none mx-2 px-3 py-1">
                    <span v-if="!isMenuOpen">☰</span>
                    <span v-else>✕</span>
                </button>

                <div class="hidden xl:flex xl:items-center">
                    <Link href="/" class="px-4 py-2 md:px-4 md:py-3 hover:bg-blue-900 hover:underline">Home</Link>
                    <Link href="/News" class="px-4 py-2 md:px-4 md:py-3 hover:bg-blue-900 hover:underline">News</Link>

                    <div v-for="(menu, index) in menus" :key="index" class="relative"
                        @mouseenter="setDropdown(index, true)" @mouseleave="setDropdown(index, false)">
                        <button @click="toggleDropdown(index)"
                            :class="{ 'bg-blue-900 underline': activeDropdown === index, 'hover:bg-blue-900 hover:underline': true }"
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
                        class="xl:hidden absolute top-full left-0 w-full bg-blue-800 flex flex-col shadow-md z-50">
                        <Link href="/" :class="{ 'bg-blue-900 text-white underline': page.url === '/' }"
                            class="px-4 py-2 hover:bg-blue-900 hover:underline" @click="closeMenu">Home</Link>
                        <Link href="/News"
                            :class="{ 'bg-blue-900 text-white underline': page.url === '/News' }"
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
            <div class="p-4 bg-gray-500 bg-opacity-20 flex justify-between">
                <Link href="/" class="inline-block">
                <img class="w-full max-w-[500px] h-auto" src="/img/dilg-bohol.png" alt="DILG Bohol">
                </Link>

                <div class="p-4 hidden md:block">
                    <Clock/>
                </div>
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

            <div class="w-full bg-gray-300 py-6 relative">
                <div class="max-w-screen-lg mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="flex flex-col items-center">
                        <p class="text-xs font-medium uppercase">Republic of the Philippines</p>
                        <img src="/img/govph-seal.png" class="h-32 w-32 mt-2" alt="Government Seal">
                    </div>

                    <div class="flex flex-col items-center">
                        <p class="text-xs font-medium uppercase">Government Links</p>
                        <div class="flex flex-col items-center mt-2 space-y-1">
                            <a href="https://president.gov.ph/" target="_blank" class="text-xs hover:underline">Office of the President</a>
                            <a href="https://ovp.gov.ph/" target="_blank" class="text-xs hover:underline">Office of the Vice President</a>
                            <a href="http://legacy.senate.gov.ph/" target="_blank" class="text-xs hover:underline">Senate of the Philippines</a>
                            <a href="https://www.congress.gov.ph/" target="_blank" class="text-xs hover:underline">House of Representatives</a>
                            <a href="https://sc.judiciary.gov.ph/" target="_blank" class="text-xs hover:underline">Supreme Court</a>
                            <a href="https://ca.judiciary.gov.ph/" target="_blank" class="text-xs hover:underline">Court of Appeals</a>
                            <a href="https://sb.judiciary.gov.ph/" target="_blank" class="text-xs hover:underline">Sandiganbayan</a>
                        </div>
                    </div>

                    <div class="flex flex-col items-center">
                        <VisitorCounter />
                    </div>
                </div>
                <button
                    @click="isModalOpen = true"
                    class="text-[0.5rem] absolute bottom-4 left-6 bg-gray-400 text-gray-700 py-1 px-2 rounded shadow-lg hover:bg-gray-500 transition-all duration-300 ease-in-out transform hover:scale-110 focus:outline-none"
                    aria-label="Open Modal"
                >
                    <i class="fa fa-code mr-1"></i>Developers
                </button>
            </div>
        </footer>

        <button
            v-show="showBackToTop"
            @click="scrollToTop"
            class="fixed bottom-4 right-6 bg-blue-800 text-white p-2 rounded shadow-lg hover:bg-blue-900 transition-all duration-300 ease-in-out transform hover:scale-110 focus:outline-none group"
            aria-label="Back to top"
        >
            <span
                class="absolute bottom-full left-1/2 -translate-x-1/2 mb-2 whitespace-nowrap text-blue-800 text-xs opacity-0 transition-opacity duration-300 pointer-events-none group-hover:opacity-100"
            >
                Back to top
            </span>

            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
            </svg>
        </button>

        <div v-show="isModalOpen" class="fixed inset-0 z-50 overflow-y-auto">
            <div class="fixed inset-0 bg-black bg-opacity-50" @click="isModalOpen = false"></div>

            <transition name="slide-up">
                <div v-if="isModalOpen" class="relative bg-white shadow-lg w-[90vw] sm:w-[70vw] md:w-[60vw] lg:w-[50vw] xl:w-[45vw] my-8 mx-auto">
                    <div class="px-4 py-3 bg-blue-800 text-white border-b flex justify-between items-center">
                        <div class="text-center w-full">
                            <h1>Developed by:</h1>
                            <h1>Mater Dei College Information Technology Interns</h1>
                        </div>
                        <button @click="isModalOpen = false" class="text-gray-100 font-bold ml-4 hover:text-gray-200">
                            <i class="fa-solid fa-xmark text-lg cursor-pointer"></i>
                        </button>
                    </div>

                    <div class="p-4">
                        <div class="bg-white rounded-lg p-6 max-w-4xl mx-auto">
                            <div class="flex flex-col md:flex-row gap-8">
                                <div class="flex flex-col items-center md:w-1/2">
                                    <p class="text-2xl font-serif text-gray-700 mb-4">Mater Dei College</p>
                                    <img
                                        src="/img/MDC_LOGO.png"
                                        alt="Mater Dei College Logo"
                                        class="w-64 h-64 object-contain -mt-3"
                                    />
                                </div>

                                <div class="md:w-1/2 space-y-4">
                                    <a href="https://github.com/viennarose" target="_blank" class="flex items-center group">
                                        <img src="/img/vienna.jpg" class="w-16 h-16 rounded object-cover" />
                                        <span class="ml-3 text-gray-600 group-hover:text-gray-900 transition-colors">Vienna Rose Pepito</span>
                                    </a>

                                    <a href="https://github.com/xplct-cont" target="_blank" class="flex items-center group">
                                        <img src="/img/kenn.jpg" class="w-16 h-16 rounded object-cover" />
                                        <span class="ml-3 text-gray-600 group-hover:text-gray-900 transition-colors">Kenn Secusana</span>
                                    </a>

                                    <a href="https://github.com/chadiegil" target="_blank" class="flex items-center group">
                                        <img src="/img/chadie.jpg" class="w-16 h-16 rounded object-cover" />
                                        <span class="ml-3 text-gray-600 group-hover:text-gray-900 transition-colors">Chadie Gil Augis</span>
                                    </a>

                                    <a href="https://github.com/dfkhin" target="_blank" class="flex items-center group">
                                        <img src="/img/franklin.jpg" class="w-16 h-16 rounded object-cover" />
                                        <span class="ml-3 text-gray-600 group-hover:text-gray-900 transition-colors">Franklin Pogoy</span>
                                    </a>
                                </div>
                            </div>

                            <hr class="my-6 border-gray-200">

                            <p class="text-center text-xs text-gray-600">
                                Contact No: 09096027312
                            </p>
                        </div>
                    </div>
                </div>
            </transition>
        </div>
    </div>

    <div class="bg"></div>
    <div class="bg bg2"></div>
    <div class="bg bg3"></div>
</template>

<script setup>
import { Link, usePage } from '@inertiajs/vue3';
import { ref, computed, onMounted, onUnmounted } from 'vue';
import VisitorCounter from '../Components/VisitorCounter.vue';
import Clock from '@/Components/Clock.vue';

const page = usePage();
const isMenuOpen = ref(false);
const activeDropdown = ref(null);
const showBackToTop = ref(false);
const isModalOpen = ref(false);

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

const checkScrollPosition = () => {
    const scrollPosition = window.scrollY || document.documentElement.scrollTop;
    const pageHeight = document.documentElement.scrollHeight - document.documentElement.clientHeight;
    showBackToTop.value = scrollPosition > (pageHeight * 0.5);
};

const scrollToTop = () => {
    window.scrollTo({
        top: 0,
        behavior: 'smooth'
    });
};

onMounted(() => {
    window.addEventListener('scroll', checkScrollPosition);
});

onUnmounted(() => {
    window.removeEventListener('scroll', checkScrollPosition);
});

const menus = [
    { name: "Officials", dropdown: [{ name: "LGUs", link: "/LGUs" }, { name: "Provincial Officials", link: "/provincialOfficials" }] },
    { name: "LGRRC", dropdown: [{ name: "DILG E-Library", link: "https://library.dilg.gov.ph/" }, { name: "Knowledge Materials", link: "/knowledgeMaterials" }] },
    {
        name: "Issuances", dropdown: [
            { name: "Latest Issuances", link: "/latestIssuances" },
            { name: "Joint Circulars", link: "/jointCirculars" },
            { name: "Memo Circulars", link: "https://dilg.gov.ph/issuances-archive/mc/" },
            { name: "Presidential Directives", link: "/presidentialDirectives" },
            { name: "Draft Issuances", link: "https://dilg.gov.ph/issuances-archive/draft/" },
            { name: "Republic Acts", link: "/republicActs" },
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

.bg {
    animation: slide 20s ease-in-out infinite alternate;
    background-image: linear-gradient(-60deg, rgb(226, 217, 217) 50%, white 50%);
    bottom: 0;
    left: -50%;
    opacity: 0.5;
    position: fixed;
    right: -50%;
    top: 0;
    z-index: -1;
    will-change: transform;
}

.bg2 {
    animation-direction: alternate-reverse;
    animation-duration: 25s;
}

.bg3 {
    animation-duration: 30s;
}

.slide-up-enter-active {
    transition: transform 0.3s cubic-bezier(0.22, 1, 0.36, 1);
}

.slide-up-enter-from {
    transform: translateY(100%);
}

@keyframes slide {
    0% {
        transform: translate3d(-25%, 0, 0);
    }
    100% {
        transform: translate3d(25%, 0, 0);
    }
}

@media (prefers-reduced-motion: reduce) {
    .bg, .bg2, .bg3 {
        animation: none !important;
    }
}
</style>
