<template>
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Republic Acts
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

        <div v-if="filteredActs.length > 0" class="space-y-4 md:px-12">
            <div v-for="(act, index) in filteredActs" :key="act.id"
                class="border border-gray-300 p-4 shadow-lg rounded cursor-pointer transition-all duration-300 bg-white"
                @click="toggleAct(act.id)">

                <div class="flex justify-between items-center">
                    <div class="flex-1 flex items-start">
                        <span class="text-sm font-bold text-gray-500 mr-2">{{ getDisplayIndex(index) }}.</span>
                        <div class="flex-1">
                            <h2 class="text-sm font-semibold text-blue-900 mb-2">{{ act.title || 'No Title Available' }}</h2>
                            <p class="text-xs text-gray-600 font-sm">
                                <span class="text-red-600">Date:</span> {{ formatDate(act.date) || 'Date not available' }}
                            </p>
                        </div>
                    </div>
                    <p class="text-xs font-bold text-gray-700 w-40 text-right">
                        <span class="text-red-600">Reference No:</span> {{ act.reference || 'None' }}
                    </p>
                </div>

                <div class="mt-2 flex gap-2">
                    <a v-if="act.download_link" :href="act.download_link" target="_blank" download
                        class="text-red-600 font-bold hover:underline" @click.stop>
                        <i class="fas fa-file-pdf"></i> Download PDF
                    </a>
                </div>

                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-600 font-medium">Click to View Document</span>
                    <i :class="{ 'fas fa-chevron-down': selectedActId === act.id, 'fas fa-chevron-right': selectedActId !== act.id }"
                        class="text-gray-600 text-lg transition-transform duration-300"
                        :style="{ transform: selectedActId === act.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                </div>

                <div :style="{ maxHeight: selectedActId === act.id ? '500px' : '0' }"
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

                        <div class="relative" v-if="act.link">
                            <iframe
                                :src="act.link"
                                class="w-full h-[500px] border border-gray-300"
                                frameborder="0"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No republic acts found matching your criteria.</p>

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
    </div>
</template>

<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const acts = ref(pageProps.pagination.data ?? []);
const pagination = ref(pageProps.pagination);
const dates = ref(pageProps.dates ?? []);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    date: pageProps.filters?.date ?? "",
});

const selectedActId = ref(null);
const isMobile = computed(() => window.innerWidth <= 768);

const getDisplayIndex = (index) => {
    return pagination.value.current_page > 1
        ? index + 1 + (pagination.value.per_page * (pagination.value.current_page - 1))
        : index + 1;
};

const filteredActs = computed(() => {
    const sortedActs = [...acts.value];

    return sortedActs.sort((a, b) => {
        const dateA = new Date(a.date);
        const dateB = new Date(b.date);
        return dateB - dateA;
    });
});

const applyFilters = () => {
    router.get("/republicActs", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["pagination", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            acts.value = props.pagination.data;
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

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["pagination"],
        onSuccess: ({ props }) => {
            pagination.value = props.pagination;
            acts.value = props.pagination.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleAct = (actId) => {
    selectedActId.value = selectedActId.value === actId ? null : actId;
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
