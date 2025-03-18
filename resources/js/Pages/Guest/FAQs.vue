<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const faqs = ref(pageProps.faqs.data ?? []);
const pagination = ref(pageProps.faqs);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    program: pageProps.filters?.program ?? "",
    outcome_area: pageProps.filters?.outcome_area ?? "",
});

const selectedFaqId = ref(null);

const applyFilters = () => {
    selectedFaqId.value = null;
    router.get("/faqs", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["faqs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.faqs;
            faqs.value = props.faqs.data;
        },
    });
};

const debouncedSearch = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedSearch);
watch(() => filters.value.program, applyFilters);
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
        only: ["faqs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.faqs;
            faqs.value = props.faqs.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleFaq = (faqId) => {
    selectedFaqId.value = selectedFaqId.value === faqId ? null : faqId;
};
</script>

<template>
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Frequently Asked Questions
        </h1>

        <!-- Filters Section -->
        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <!-- Search Input -->
                <div class="relative w-full md:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search FAQs..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>

                <!-- Program Filter -->
                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.program"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none">
                        <option value="">All Programs</option>
                        <option v-for="program in pageProps.programs" :key="program" :value="program">
                            {{ program }}
                        </option>
                    </select>
                </div>

                <!-- Outcome Area Filter -->
                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.outcome_area"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none">
                        <option value="">All Outcome Areas</option>
                        <option v-for="area in pageProps.outcomeAreas" :key="area" :value="area">
                            {{ area }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <!-- FAQ List -->
        <div v-if="faqs.length > 0" class="space-y-4 md:px-12">
            <div v-for="faq in faqs" :key="faq.id"
                class="border border-gray-300 p-4 shadow-lg rounded cursor-pointer transition-all duration-300 bg-white"
                @click="toggleFaq(faq.id)">

                <!-- Question -->
                <div class="flex justify-between items-center">
                    <h2 class="text-sm font-semibold text-blue-900">{{ faq.questions }}</h2>
                    <i :class="{ 'fas fa-chevron-down': selectedFaqId === faq.id, 'fas fa-chevron-right': selectedFaqId !== faq.id }"
                        class="text-gray-600 text-lg transition-transform duration-300"
                        :style="{ transform: selectedFaqId === faq.id ? 'rotate(180deg)' : 'rotate(0deg)' }"></i>
                </div>

                <!-- Dropdown Content -->
                <transition name="stretch">
                    <div v-if="selectedFaqId === faq.id" class="mt-4 border-t pt-4 overflow-hidden relative">
                        <!-- Program -->
                        <p class="text-xs text-gray-600 mb-2">
                            <span class="font-semibold">Program:</span> {{ faq.program }}
                        </p>

                        <!-- Outcome Area -->
                        <p class="text-xs text-gray-600 mb-2">
                            <span class="font-semibold">Outcome Area:</span> {{ faq.outcome_area }}
                        </p>

                        <!-- Answer -->
                        <div class="text-sm text-gray-700">
                            <span class="font-semibold">Answer:</span> {{ faq.answers }}
                        </div>
                    </div>
                </transition>
            </div>
        </div>

        <!-- No Results Message -->
        <p v-else class="text-center text-gray-500 mt-4">No FAQs found.</p>

        <!-- Pagination -->
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