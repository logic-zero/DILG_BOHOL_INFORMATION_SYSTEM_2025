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

const openFlipbook = (pdfUrl) => {
    const encodedPdfUrl = encodeURIComponent(pdfUrl);
    const randomParam = Math.random().toString(36).substring(7);
    currentFlipbookUrl.value = `https://heyzine.com/api1?pdf=${encodedPdfUrl}&k=de98c2e60096a623&r=${randomParam}`;
    showModal.value = true;
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

        <div v-if="showModal" class="fixed inset-0 z-50 bg-white flex items-center justify-center p-0">
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

                <iframe :src="currentFlipbookUrl"
                        class="flex-1 border-0 w-full h-full"
                        allowfullscreen>
                </iframe>
            </div>
        </div>
    </div>
</template>
