<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const materials = ref(pageProps.materials.data ?? []);
const pagination = ref(pageProps.materials);

const filters = ref({
    search: pageProps.filters?.search ?? "",
});

const showModal = ref(false);
const currentFlipbookUrl = ref("");
const isLoading = ref(false);

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

const openInFullPage = () => {
    window.open(currentFlipbookUrl.value, '_blank');
    showModal.value = false;
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

        <div v-if="materials.length > 0" class="space-y-4 md:px-12">
            <div v-for="material in materials" :key="material.id"
                class="border border-gray-300 p-4 shadow-lg rounded bg-white">

                <div class="flex justify-between items-center">
                    <div class="flex-1 flex justify-between">
                        <h2 class="text-sm font-semibold text-blue-900 mb-2">{{ material.title }}</h2>
                        <p class="text-xs uppercase text-gray-600 font-bold">
                            {{ new Date(material.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>
                    </div>
                </div>

                <div class="mt-2 flex gap-4">
                    <a :href="route('guest.knowledgeMaterials.download', material)"
                        class="text-red-600 font-bold hover:underline">
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                    <button @click="openFlipbook(route('guest.knowledgeMaterials.download', material))"
                            class="text-blue-600 font-bold hover:underline">
                        <i class="fas fa-book-open"></i> View Flipbook
                    </button>
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
                    <div class="text-center">
                        <div class="book">
                            <div class="inner">
                                <div class="left"></div>
                                <div class="middle"></div>
                                <div class="right"></div>
                            </div>
                            <ul>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                                <li></li>
                            </ul>
                        </div>
                        <p class="mt-4 text-gray-800">Preparing flipbook...</p>
                    </div>
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
    </div>
</template>

<style scoped>
.book {
    width: 60px;
    height: 80px;
    position: relative;
    perspective: 150px;
    margin: 0 auto;
}

.book .inner {
    position: absolute;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
    animation: flip 2s infinite ease-in-out;
}

.book .left, .book .middle, .book .right {
    position: absolute;
    width: 33.33%;
    height: 100%;
    background: #3b82f6;
    transform-origin: right center;
}

.book .left {
    left: 0;
    animation: leftPage 2s infinite ease-in-out;
}

.book .middle {
    left: 33.33%;
    background: #1d4ed8;
}

.book .right {
    right: 0;
    transform-origin: left center;
    animation: rightPage 2s infinite ease-in-out;
}

.book ul {
    position: absolute;
    left: 0;
    top: 0;
    width: 100%;
    height: 100%;
    transform-style: preserve-3d;
}

.book ul li {
    position: absolute;
    list-style: none;
    background: #fff;
    border: 1px solid #ddd;
    width: 100%;
    height: 100%;
    opacity: 0;
    animation: pages 2s infinite ease-in-out;
}

.book ul li:nth-child(1) { animation-delay: 0.05s; }
.book ul li:nth-child(2) { animation-delay: 0.1s; }
.book ul li:nth-child(3) { animation-delay: 0.15s; }
.book ul li:nth-child(4) { animation-delay: 0.2s; }
.book ul li:nth-child(5) { animation-delay: 0.25s; }
.book ul li:nth-child(6) { animation-delay: 0.3s; }
.book ul li:nth-child(7) { animation-delay: 0.35s; }
.book ul li:nth-child(8) { animation-delay: 0.4s; }
.book ul li:nth-child(9) { animation-delay: 0.45s; }
.book ul li:nth-child(10) { animation-delay: 0.5s; }
.book ul li:nth-child(11) { animation-delay: 0.55s; }
.book ul li:nth-child(12) { animation-delay: 0.6s; }
.book ul li:nth-child(13) { animation-delay: 0.65s; }
.book ul li:nth-child(14) { animation-delay: 0.7s; }
.book ul li:nth-child(15) { animation-delay: 0.75s; }
.book ul li:nth-child(16) { animation-delay: 0.8s; }
.book ul li:nth-child(17) { animation-delay: 0.85s; }

@keyframes flip {
    0%, 100% { transform: rotateY(-20deg); }
    50% { transform: rotateY(20deg); }
}

@keyframes leftPage {
    0%, 100% { transform: rotateY(0); }
    50% { transform: rotateY(-15deg); }
}

@keyframes rightPage {
    0%, 100% { transform: rotateY(0); }
    50% { transform: rotateY(15deg); }
}

@keyframes pages {
    0%, 100% { opacity: 0; transform: translateZ(5px); }
    50% { opacity: 0.5; transform: translateZ(0); }
}
</style>
