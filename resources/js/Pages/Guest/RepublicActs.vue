<template>
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Republic Acts
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row gap-3 w-full mb-4">
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
            <div class="border border-gray-300 rounded-lg overflow-hidden">
                <div v-for="(act, index) in filteredActs" :key="act.id"
                    class="grid grid-cols-12 p-5 border-b border-gray-200 bg-gray-50 hover:bg-gray-100 cursor-pointer transition-all duration-200"
                    @click="toggleAct(act.id)">
                    <div class="col-span-1 text-sm text-gray-700 flex items-center justify-center">
                        {{ getIncrementedNumber(index) }}
                    </div>

                    <div class="col-span-8 space-y-2 ml-3">
                        <h2 class="text-md font-semibold text-blue-900">{{ act.title || 'No Title Available' }}</h2>
                        <p class="text-sm text-gray-600">
                            <span class="text-red-600">Reference No:</span> {{ act.reference || 'None' }}
                        </p>
                        <div class="flex gap-2">
                            <a :href="act.link" target="_blank" 
                            class="inline-flex items-center px-4 py-2 bg-blue-600 text-white text-sm font-medium rounded-lg shadow-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <i class="fas fa-external-link-alt mr-2"></i>
                                View Full Text
                            </a>
                            <a v-if="act.download_link" :href="act.download_link" target="_blank" download 
                            class="inline-flex items-center px-4 py-2 bg-red-600 text-white text-sm font-medium rounded-lg shadow-md hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500">
                                <i class="fas fa-download mr-2"></i>
                                Download PDF
                            </a>
                        </div>
                    </div>

                    <div class="col-span-3 text-sm text-gray-600 flex items-center justify-end gap-2">
                        <div class="w-24 text-right mr-1">
                            {{ formatDate(act.date) || 'Date not available' }}
                        </div>
                        <i :class="{ 'fas fa-chevron-down': selectedActId === act.id, 'fas fa-chevron-right': selectedActId !== act.id }"
                            class="text-gray-600 text-lg w-6 text-center transition-transform duration-300"
                            :style="{ transform: selectedActId === act.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                    </div>

                    <div v-if="selectedActId === act.id && act.link"
                        class="col-span-12 mt-4 border-t pt-4">
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

const getIncrementedNumber = (index) => {
    const currentPage = pagination.value.current_page || 1;
    const perPage = pagination.value.per_page || 10;
    return (currentPage - 1) * perPage + index + 1;
};

const filteredActs = computed(() => {
    return acts.value;
});
</script>

<style scoped>
.transition-max-height {
    transition-property: max-height;
    transition-timing-function: ease-out;
}

.pdf-page {
    display: block;
    margin: 0 auto 10px;
    border: 1px solid #ddd;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
</style>