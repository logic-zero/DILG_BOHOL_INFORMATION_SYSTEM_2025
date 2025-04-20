<script setup>
import { ref, watch, computed, onMounted } from "vue";
import { usePage, router, Head } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const materials = ref(pageProps.materials.data ?? []);
const pagination = ref(pageProps.materials);
const currentMaterial = ref(null);

const filters = ref({
    search: pageProps.filters?.search ?? "",
});

const showAnyflipModal = ref(false);
const currentAnyflipUrl = ref("");
const currentPdfUrl = ref("");
const isAnyflipLoading = ref(false);
const isAnyflipLoaded = ref(false);
const iframeCache = ref(new Map());

const openAnyflip = (url, pdfUrl, material) => {
    currentAnyflipUrl.value = url;
    currentPdfUrl.value = pdfUrl;
    currentMaterial.value = material;
    showAnyflipModal.value = true;

    if (iframeCache.value.has(url)) {
        isAnyflipLoading.value = false;
        isAnyflipLoaded.value = true;
    } else {
        isAnyflipLoading.value = true;
        isAnyflipLoaded.value = false;
    }
};

const handleAnyflipIframeLoad = (url) => {
    iframeCache.value.set(url, true);
    if (url === currentAnyflipUrl.value) {
        isAnyflipLoading.value = false;
        isAnyflipLoaded.value = true;
    }
};

const applyFilters = () => {
    router.get("/knowledgeMaterials", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["materials", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.materials;
            materials.value = props.materials.data;
        },
    });
};

const debouncedSearch = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedSearch);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const visiblePages = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const pages = [];

    if (last <= 1) return pages;

    if (current !== 1) {
        pages.push({ label: '« First', url: pagination.value.first_page_url });
    }

    if (current > 1) {
        pages.push({ label: '‹ Prev', url: pagination.value.prev_page_url });
    }

    pages.push({
        label: current.toString(),
        url: pagination.value.path + '?page=' + current,
        active: true
    });

    if (current < last) {
        pages.push({ label: 'Next ›', url: pagination.value.next_page_url });
    }

    if (current !== last) {
        pages.push({ label: 'Last »', url: pagination.value.last_page_url });
    }

    return pages;
});

const pageOptions = computed(() => {
    const pages = [];
    for (let i = 1; i <= pagination.value.last_page; i++) {
        pages.push({
            value: i,
            label: i.toString(),
            url: pagination.value.path + '?page=' + i
        });
    }
    return pages;
});

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["materials", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.materials;
            materials.value = props.materials.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const closeAnyflipModal = () => {
    showAnyflipModal.value = false;
};

onMounted(() => {
    if (materials.value.length > 0) {
        const firstMaterial = materials.value[0];
        const url = firstMaterial.link;
        if (!iframeCache.value.has(url)) {
            const iframe = document.createElement('iframe');
            iframe.src = url;
            iframe.style.display = 'none';
            iframe.onload = () => handleAnyflipIframeLoad(url);
            document.body.appendChild(iframe);
        }
    }
});
</script>

<template>
    <Head>
        <meta http-equiv="Content-Security-Policy" content="upgrade-insecure-requests">
    </Head>
    <div class="p-2 lg:p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Knowledge Materials
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="relative w-full md:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search materials..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>
            </div>
        </div>

        <div v-if="materials.length > 0" class="md:px-12">
            <div v-for="material in materials" :key="material.id"
                class="border border-gray-300 shadow-lg bg-white">
                <div class="flex flex-row gap-2 w-full">
                    <div class="py-4 bg-blue-800 flex-1">
                        <p class="text-sm text-white text-center whitespace-nowrap">
                            {{ new Date(material.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </p>
                    </div>
                    <div class="py-4 px-2 flex-[2]">
                        <button @click="openAnyflip(material.link, material.file ? route('guest.knowledgeMaterials.download', material) : null, material)" class="w-full">
                            <h2 class="text-sm text-center font-semibold text-blue-900 hover:underline">{{ material.title }}</h2>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No materials found.</p>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-1 mt-2 sm:mt-0">
                <button v-for="(link, index) in visiblePages" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-2 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>

        <div v-if="pagination.last_page > 1" class="flex justify-center mt-2">
            <select
                v-model="pagination.current_page"
                @change="goToPage(pagination.path + '?page=' + pagination.current_page)"
                class="px-2 py-1 text-sm border border-gray-300 bg-white focus:outline-none focus:border-gray-400 w-auto pr-7"
            >
                <option
                    v-for="page in pageOptions"
                    :key="page.value"
                    :value="page.value"
                >
                    Page {{ page.label }}
                </option>
            </select>
        </div>

        <div v-if="showAnyflipModal" class="fixed inset-0 z-50 flex items-center justify-center p-1 bg-black bg-opacity-50">
            <div class="relative bg-white shadow-xl w-full max-w-6xl h-[90vh] lg:h-[95vh] mx-auto flex flex-col">
                <div class="flex items-center justify-between py-2 px-4 border-b border-gray-200">
                    <h3 class="text-md font-bold text-blue-900">{{ currentMaterial?.title }}</h3>
                    <button @click="closeAnyflipModal"
                            class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="flex-1 relative">
                    <div v-if="isAnyflipLoading && !iframeCache.has(currentAnyflipUrl)" class="absolute inset-0 flex items-center justify-center">
                        <div class="spinner"></div>
                    </div>

                    <div class="absolute bottom-2 left-4 z-10 bg-white text-gray-800 px-3 py-2 rounded-md text-sm shadow-lg border border-gray-200">
                        <div class="font-medium text-gray-700 mb-1">Having trouble viewing?</div>
                        <div class="flex items-center space-x-4">
                            <a :href="currentAnyflipUrl" target="_blank" class="flex items-center text-blue-700 hover:text-blue-800 hover:underline">
                                <i class="fas fa-external-link-alt mr-2 text-sm"></i>
                                Open in new tab
                            </a>
                            <a v-if="currentPdfUrl" :href="currentPdfUrl" class="flex items-center text-red-600 hover:text-red-700 hover:underline">
                                <i class="fas fa-download mr-2 text-sm"></i>
                                Download PDF
                            </a>
                        </div>
                    </div>

                    <iframe
                        v-show="!isAnyflipLoading || iframeCache.has(currentAnyflipUrl)"
                        :src="currentAnyflipUrl"
                        @load="handleAnyflipIframeLoad(currentAnyflipUrl)"
                        class="w-full h-full border-0"
                        seamless="seamless"
                        scrolling="no"
                        frameborder="0"
                        allowtransparency="true"
                        allowfullscreen="true">
                    </iframe>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.spinner {
    width: 50px;
    height: 50px;
    border: 5px solid #f3f3f3;
    border-top: 5px solid #3b82f6;
    border-radius: 50%;
    animation: spin 1s linear infinite;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}
</style>
