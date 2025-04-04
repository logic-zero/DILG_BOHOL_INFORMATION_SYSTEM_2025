<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
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

const showModal = ref(false);
const currentFlipbookUrl = ref("");
const isLoading = ref(false);

const showAnyflipModal = ref(false);
const currentAnyflipUrl = ref("");
const currentPdfUrl = ref("");
const isAnyflipLoading = ref(false);
const isAnyflipLoaded = ref(false);

const openFlipbook = (pdfUrl) => {
    const encodedPdfUrl = encodeURIComponent(pdfUrl);
    const randomParam = Math.random().toString(36).substring(7);
    currentFlipbookUrl.value = `https://heyzine.com/api1?pdf=${encodedPdfUrl}&k=0b55f138ea199759&r=${randomParam}`;
    showModal.value = true;
    isLoading.value = true;
};

const handleIframeLoad = () => {
    isLoading.value = false;
};

const openAnyflip = (url, pdfUrl, material) => {
    currentAnyflipUrl.value = url;
    currentPdfUrl.value = pdfUrl;
    currentMaterial.value = material;
    showAnyflipModal.value = true;
    if (!isAnyflipLoaded.value) {
        isAnyflipLoading.value = true;
    }
};

const handleAnyflipIframeLoad = () => {
    isAnyflipLoading.value = false;
    isAnyflipLoaded.value = true;
};

const switchToFlipbook = () => {
    showAnyflipModal.value = false;
    openFlipbook(currentPdfUrl.value);
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

const closeAnyflipModal = (event) => {
    if (event.target.classList.contains('modal-overlay')) {
        showAnyflipModal.value = false;
    }
};

const openInFullPage = () => {
    window.open(currentFlipbookUrl.value, '_blank');
};
</script>

<template>
    <div class="p-6 w-full">
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

        <div v-if="materials.length > 0" class="space-y-2 md:px-12">
            <div v-for="material in materials" :key="material.id"
                class="border border-gray-300 shadow-lg bg-white">

                <div class="flex flex-col sm:flex-row gap-2 w-full">
                    <div class="py-4 bg-blue-800 flex-1">
                        <p class="text-sm text-white text-center whitespace-nowrap">
                            {{ new Date(material.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) }}
                        </p>
                    </div>
                    <div class="py-4 flex-1">
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
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>

        <div v-if="showModal" class="fixed inset-0 z-50 bg-black bg-opacity-5 flex items-center justify-center p-0">
            <div class="absolute inset-0 flex flex-col">
                <div class="bg-white p-2 flex justify-end">
                    <div class="space-x-2">
                        <button @click="openInFullPage"
                                class="px-3 py-1 text-sm text-blue-600 hover:bg-blue-100 rounded">
                            <i class="fas fa-expand mr-1"></i> Full Page
                        </button>
                        <button @click="showModal = false"
                                class="px-3 py-1 text-sm text-gray-600 hover:bg-gray-300 rounded">
                            <i class="fas fa-times mr-1"></i> Close
                        </button>
                    </div>
                </div>

                <div v-if="isLoading" class="flex-1 flex items-center justify-center">
                    <div class="spinner"></div>
                </div>

                <iframe
                    :src="currentFlipbookUrl"
                    @load="handleIframeLoad"
                    class="flex-1 border-0 w-full h-full"
                    :class="{ 'hidden': isLoading }"
                    allowfullscreen>
                </iframe>
            </div>
        </div>

        <div v-if="showAnyflipModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 modal-overlay" @click="closeAnyflipModal">
            <div class="relative bg-white shadow-xl w-full max-w-6xl h-[95vh] mx-auto flex flex-col">
                <div class="flex items-center justify-between py-2 px-4 border-b border-gray-200">
                    <h3 class="text-md font-bold text-blue-900">{{ currentMaterial?.title }}</h3>
                    <button @click="showAnyflipModal = false"
                            class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>

                <div class="flex-1 relative">
                    <div v-if="isAnyflipLoading && !isAnyflipLoaded" class="absolute inset-0 flex items-center justify-center">
                        <div class="spinner"></div>
                    </div>

                    <div class="absolute bottom-2 left-4 z-10 bg-white text-gray-800 px-3 py-2 rounded-md text-sm shadow-lg border border-gray-200">
                        <div class="font-medium text-gray-700 mb-1">Having trouble viewing?</div>
                        <div class="flex items-center">
                            <template v-if="currentPdfUrl">
                                <button @click="switchToFlipbook" class="flex items-center text-blue-700 hover:text-blue-800 hover:underline">
                                    <i class="fas fa-sync-alt mr-2 text-sm"></i>
                                    Alternative viewer
                                </button>
                                <span class="text-gray-500 mx-2">or</span>
                                <a :href="currentPdfUrl" class="flex items-center text-red-600 hover:text-red-700 hover:underline">
                                    <i class="fas fa-download mr-2 text-sm"></i>
                                    Download PDF
                                </a>
                            </template>
                            <template v-else>
                                <a :href="currentAnyflipUrl" target="_blank" class="flex items-center text-blue-700 hover:text-blue-800 hover:underline">
                                    <i class="fas fa-external-link-alt mr-2 text-sm"></i>
                                    Open this link
                                </a>
                            </template>
                        </div>
                    </div>

                    <div class="w-full h-full flex items-center justify-center px-4">
                        <iframe
                            v-if="showAnyflipModal"
                            :src="currentAnyflipUrl"
                            @load="handleAnyflipIframeLoad"
                            class="w-full h-full max-w-full max-h-full border-0"
                            :class="{ 'hidden': isAnyflipLoading && !isAnyflipLoaded }"
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

.modal-overlay {
    background-color: rgba(0, 0, 0, 0.5);
    backdrop-filter: blur(2px);
}

@media (max-width: 768px) {
    .modal-overlay {
        padding: 1rem;
    }
}
</style>
