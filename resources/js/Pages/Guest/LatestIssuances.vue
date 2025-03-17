<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const issuances = ref(pageProps.b_issuances.data ?? []);
const pagination = ref(pageProps.b_issuances);
const outcomeAreas = ref(pageProps.outcomeAreas);
const isMobile = computed(() => window.innerWidth <= 768);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    outcome_area: pageProps.filters?.outcome_area ?? "",
});

const selectedIssuanceId = ref(null);

const applyFilters = () => {
    selectedIssuanceId.value = null;
    router.get("/latestIssuances", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["b_issuances", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.b_issuances;
            issuances.value = props.b_issuances.data;
        },
    });
};

const debouncedSearch = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedSearch);
watch(() => filters.value.outcome_area, applyFilters);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["b_issuances", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.b_issuances;
            issuances.value = props.b_issuances.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleIssuance = (issuanceId) => {
    selectedIssuanceId.value = selectedIssuanceId.value === issuanceId ? null : issuanceId;
};
</script>

<template>
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Latest Issuances
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="relative w-full md:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search issuances..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>

                <div class="relative w-full md:w-auto">
                    <select v-model="filters.outcome_area"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none">
                        <option value="">All Outcomes</option>
                        <option v-for="area in outcomeAreas" :key="area" :value="area">
                            {{ area }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="issuances.length > 0" class="space-y-4 md:px-12">
            <div v-for="issuance in issuances" :key="issuance.id"
                class="border border-gray-300 p-4 shadow-lg rounded cursor-pointer transition-all duration-300 bg-white"
                @click="toggleIssuance(issuance.id)">

                <div class="flex justify-between items-center">
                    <div class="flex-1">
                        <h2 class="text-sm font-semibold text-blue-900 mb-2">{{ issuance.title }}</h2>
                        <p class="text-xs text-gray-600 font-sm">{{ issuance.outcome_area }}</p>
                    </div>
                    <p class="text-xs font-bold text-gray-700 w-40 text-right">
                        <span class="text-red-600">Reference No:</span> {{ issuance.reference_num }}
                    </p>
                </div>

                <div class="mt-2">
                    <a :href="'/issuance_files/' + issuance.file" download
                        class="text-red-600 font-bold hover:underline" @click.stop>
                        <i class="fas fa-download"></i> Download PDF
                    </a>
                </div>

                <div class="flex justify-between items-center mt-2">
                    <span class="text-xs text-gray-600 font-medium">Click to Open PDF</span>
                    <i :class="{ 'fas fa-chevron-down': selectedIssuanceId === issuance.id, 'fas fa-chevron-right': selectedIssuanceId !== issuance.id }"
                        class="text-gray-600 text-lg transition-transform duration-300"
                        :style="{ transform: selectedIssuanceId === issuance.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                </div>

                <transition name="stretch">
                    <div v-if="selectedIssuanceId === issuance.id" class="mt-4 border-t pt-4 overflow-hidden relative">
                        <div v-if="isMobile"
                            class="border border-red-500 bg-red-100 text-red-700 p-4 rounded text-center w-full">
                            <div class="flex items-center justify-center">
                                <i class="fas fa-exclamation-triangle text-lg mr-2"></i>
                                <p class="text-xs font-semibold">
                                    PDF preview is not supported on mobile. Please use a desktop to view it or download
                                    the file above.
                                </p>
                            </div>
                        </div>

                        <div v-else class="relative">
                            <div
                                class="absolute top-2 right-5 bg-white text-xs px-3 py-1 rounded shadow-md">
                                <i class="fas fa-search-plus mr-1"></i>
                                Hold <span class="font-bold">Ctrl</span> + <span class="font-bold">Scroll</span> to zoom
                            </div>

                            <iframe :src="'/issuance_files/' + issuance.file + '#toolbar=0'" width="100%"
                                height="500px"></iframe>
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No issuances found.</p>

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

<style scoped>
.stretch-enter-active,
.stretch-leave-active {
    transition: max-height 0.4s ease-in-out, opacity 0.3s ease-in-out;
    overflow: hidden;
}

.stretch-enter-from,
.stretch-leave-to {
    max-height: 0;
    opacity: 0;
}

.stretch-enter-to,
.stretch-leave-from {
    max-height: 1000px;
    opacity: 1;
}
</style>
