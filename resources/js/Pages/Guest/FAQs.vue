<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const faqs = ref(pageProps.faqs.data ?? []);
const pagination = ref(pageProps.faqs);
const programs = ref(pageProps.programs);
const outcomeAreas = ref(pageProps.outcomeAreas);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    program: pageProps.filters?.program ?? "",
    outcome_area: pageProps.filters?.outcome_area ?? "",
});

const selectedFaqId = ref(null);

const resetOtherFilter = (filterKey) => {
    if (filterKey === "program") {
        filters.value.outcome_area = "";
    } else if (filterKey === "outcome_area") {
        filters.value.program = "";
    }
};

const applyFilters = () => {
    selectedFaqId.value = null;
    router.get("/FAQs", filters.value, {
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

watch(() => filters.value.program, (newValue) => {
    if (newValue) {
        resetOtherFilter("program");
    }
    applyFilters();
});

watch(() => filters.value.outcome_area, (newValue) => {
    if (newValue) {
        resetOtherFilter("outcome_area");
    }
    applyFilters();
});

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
    <div class="p-2 lg:p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Frequently Asked Questions
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="relative w-full md:w-1/2">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search FAQs..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>

                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.program"
                        class="appearance-none border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none pr-8">
                        <option value="">All Programs</option>
                        <option v-for="program in programs" :key="program" :value="program">
                            {{ program }}
                        </option>
                    </select>
                </div>

                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.outcome_area"
                        class="appearance-none border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none pr-8">
                        <option value="">All Outcome Areas</option>
                        <option v-for="area in outcomeAreas" :key="area" :value="area">
                            {{ area }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div v-if="faqs.length > 0" class="space-y-4 md:px-12">
            <div v-for="faq in faqs" :key="faq.id"
                class="border border-gray-300 p-4 shadow-lg rounded cursor-pointer transition-all duration-300 bg-white"
                @click="toggleFaq(faq.id)">

                <div class="flex justify-between items-center">
                    <h2 class="text-md font-semibold text-blue-900">
                        {{ faq.questions }}
                    </h2>
                    <i :class="{ 'fas fa-minus': selectedFaqId === faq.id, 'fas fa-plus': selectedFaqId !== faq.id }"
                        class="text-gray-600 text-lg transition-transform duration-300"></i>
                </div>

                <div :style="{ maxHeight: selectedFaqId === faq.id ? '500px' : '0' }"
                    class="overflow-hidden transition-max-height duration-300 ease-out">
                    <div class="mt-4 border-t pt-4">
                        <p class="text-sm font-bold text-blue-900 mb-2 text-right uppercase">
                            {{ faq.program }}
                        </p>

                        <div class="text-md text-gray-700 mb-2">
                            <span class="font-semibold">Answer:</span> {{ faq.answers }}
                        </div>

                        <p class="text-xs text-gray-600 font-bold uppercase text-right pt-2 border-t">
                            {{ faq.outcome_area }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No FAQs found.</p>

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
.transition-max-height {
    transition-property: max-height;
    transition-timing-function: ease-out;
}
</style>
