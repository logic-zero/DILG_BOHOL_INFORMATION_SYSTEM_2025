<template>
    <div class="p-2 lg:p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Joint Circulars
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row gap-3 w-full">
                <div class="relative w-full md:w-3/4">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input
                    v-model="filters.search"
                    type="text"
                    placeholder="Search by title or reference number..."
                    class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none"
                    />
                </div>

                <div class="flex items-center w-full md:w-1/4">
                    <select
                    id="date"
                    v-model="filters.date"
                    class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none"
                    >
                    <option value="">All Dates</option>
                    <option v-for="date in dates" :key="date" :value="date">
                        {{ formatDate(date) }}
                    </option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="filteredCirculars.length > 0" class="space-y-4 md:px-12">
            <div v-for="(circular, index) in filteredCirculars" :key="circular.id"
                class="border border-gray-300 p-4 shadow-lg rounded cursor-pointer transition-all duration-300 bg-white"
                @click="toggleCircular(circular.id)">

                <div class="flex justify-between items-center">
                    <div class="flex-1 flex items-start">
                        <span class="text-sm font-bold text-gray-500 mr-2">{{ getDisplayIndex(index) }}.</span>
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-blue-900 mb-2">{{ circular.title || 'No Title Available' }}</h2>
                            <p class="text-xs text-gray-600 font-sm">
                                <span class="text-red-600">Date:</span> {{ formatDate(circular.date) || 'Date not available' }}
                            </p>
                        </div>
                        <p class="text-xs font-bold text-gray-700 w-40 text-right">
                            <span class="text-red-600">Reference No:</span> {{ circular.reference || 'None' }}
                        </p>
                    </div>
                </div>

                <div class="mt-2">
                    <a v-if="circular.download_link" :href="circular.download_link" target="_blank" download
                        class="text-red-600 font-bold hover:underline" @click.stop>
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-600 font-medium">Click to View Document</span>
                    <i :class="{ 'fas fa-chevron-down': selectedCircularId === circular.id, 'fas fa-chevron-right': selectedCircularId !== circular.id }"
                        class="text-gray-600 text-lg transition-transform duration-300"
                        :style="{ transform: selectedCircularId === circular.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                </div>

                <div :style="{ maxHeight: selectedCircularId === circular.id ? '500px' : '0' }"
                    class="overflow-hidden transition-max-height duration-300 ease-out">
                    <div class="mt-4 border-t pt-4">
                        <div v-if="isMobile"
                            class="border border-red-500 bg-red-100 text-red-700 p-4 rounded text-center w-full">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-lg mr-2"></i>
                                <p class="text-xs font-semibold">
                                    Document preview is not supported on mobile. Please use a desktop to view it or use
                                    the links above.
                                </p>
                            </div>
                        </div>

                        <div class="relative" v-if="circular.link">
                            <div class="absolute top-2 right-5 flex gap-2">
                                <div class="hidden md:block bg-white text-xs px-3 py-1 rounded shadow-md">
                                    <i class="fas fa-search-plus mr-1"></i>
                                    Hold <span class="font-bold">Ctrl</span> + <span class="font-bold">Scroll</span> to zoom
                                </div>
                            </div>

                            <iframe
                                :src="circular.file ? '/joint_circulars/' + circular.file + '#toolbar=0' : ''"
                                class="w-full h-[500px] border border-gray-300"
                                frameborder="0"
                                @load="iframeLoaded"
                                @error="iframeError"
                                allowfullscreen>
                            </iframe>

                            <div v-if="loading" class="absolute inset-0 flex items-center justify-center bg-gray-100">
                                <div class="animate-spin rounded-full h-12 w-12 border-t-2 border-b-2 border-blue-500"></div>
                            </div>

                            <div v-if="error" class="absolute inset-0 flex items-center justify-center bg-red-100">
                                <div class="text-red-600 text-center p-4">
                                    <i class="fas fa-exclamation-triangle text-2xl mb-2"></i>
                                    <p>Failed to load the document.
                                        <a :href="circular.download_link" class="text-blue-600 hover:underline">Download it instead</a>.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="text-center mt-8">
                <a href="https://www.dilg.gov.ph/issuances-archive/jc/" target="_blank"
                   class="inline-block bg-blue-800 hover:bg-blue-900 text-white font-bold py-2 px-6 rounded-lg transition duration-300">
                   <i class="fas fa-external-link-alt mr-2"></i>Visit Official DILG Website
                </a>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No joint circulars found matching your criteria.</p>

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
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const circulars = ref(pageProps.pagination.data ?? []);
const pagination = ref(pageProps.pagination);
const dates = ref(pageProps.dates ?? []);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    date: pageProps.filters?.date ?? "",
});

const selectedCircularId = ref(null);
const isMobile = computed(() => window.innerWidth <= 768);

const getDisplayIndex = (index) => {
    return pagination.value.current_page > 1
        ? index + 1 + (pagination.value.per_page * (pagination.value.current_page - 1))
        : index + 1;
};

const filteredCirculars = computed(() => {
    const sortedCirculars = [...circulars.value];
    return sortedCirculars.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return dateB - dateA;
    });
});

const applyFilters = () => {
    router.get("/jointCirculars", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["pagination", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            circulars.value = props.pagination.data;
        },
    });
};

const debouncedSearch = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedSearch);
watch(() => filters.value.date, applyFilters);

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

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["pagination"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            circulars.value = props.pagination.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleCircular = (circularId) => {
    selectedCircularId.value = selectedCircularId.value === circularId ? null : circularId;
};

const formatDate = (dateString) => {
    if (!dateString) return "N/A";
    const date = new Date(dateString);
    return date.toLocaleDateString("en-US", { year: "numeric", month: "short", day: "numeric" });
};
</script>

<style scoped>
.transition-max-height {
    transition-property: max-height;
    transition-timing-function: ease-out;
}
</style>
