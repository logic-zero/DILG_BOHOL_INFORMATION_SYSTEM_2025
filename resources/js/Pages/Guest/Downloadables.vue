<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const downloadables = ref((pageProps.downloadables.data ?? []).map(item => ({
    ...item,
    showFullTitle: false,
    isAnimating: false
})));
const pagination = ref(pageProps.downloadables);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    outcome_area: pageProps.filters?.outcome_area ?? "",
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
};

const applyFilters = () => {
    router.get("/downloadables", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["downloadables", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.downloadables;
            downloadables.value = props.downloadables.data.map(item => ({
                ...item,
                showFullTitle: false,
                isAnimating: false
            }));
        },
    });
};

const debouncedApplyFilters = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedApplyFilters);
watch(() => filters.value.outcome_area, debouncedApplyFilters);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["downloadables", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.downloadables;
            downloadables.value = props.downloadables.data.map(item => ({
                ...item,
                showFullTitle: false,
                isAnimating: false
            }));
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleTitle = (item) => {
    if (item.isAnimating) return;

    item.isAnimating = true;
    if (item.showFullTitle) {
        setTimeout(() => {
            item.showFullTitle = false;
            item.isAnimating = false;
        }, 300);
    } else {
        item.showFullTitle = true;
        setTimeout(() => {
            item.isAnimating = false;
        }, 300);
    }
};

const getFileIcon = (fileName) => {
    if (!fileName) return 'fa-file';
    const extension = fileName.split('.').pop().toLowerCase();
    switch(extension) {
        case 'pdf': return 'fa-file-pdf text-red-500';
        case 'doc':
        case 'docx': return 'fa-file-word text-blue-500';
        case 'xls':
        case 'xlsx': return 'fa-file-excel text-green-500';
        case 'ppt':
        case 'pptx': return 'fa-file-powerpoint text-orange-500';
        case 'txt': return 'fa-file-alt text-gray-500';
        default: return 'fa-file text-gray-400';
    }
};
</script>

<template>
    <div class="p-4 sm:p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Downloadable Resources
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-3">
                <div class="relative w-full sm:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search resources..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none text-sm sm:text-base" />
                </div>

                <div class="relative w-full sm:w-1/2">
                    <select v-model="filters.outcome_area"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none text-sm sm:text-base">
                        <option value="">All Outcome Areas</option>
                        <option v-for="area in pageProps.outcome_areas" :key="area" :value="area">
                            {{ area }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-2 sm:px-4 mt-4 sm:mt-8">
            <div v-if="downloadables.length > 0" class="space-y-4 sm:space-y-6">
                <div
                    v-for="item in downloadables"
                    :key="item.id"
                    class="bg-white p-3 sm:p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300"
                >
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
                        <div class="w-full sm:w-1/4 flex items-center justify-center">
                            <div class="text-center">
                                <i :class="`fas ${getFileIcon(item.file)} text-5xl sm:text-6xl mb-2`"></i>
                            </div>
                        </div>
                        <div class="w-full sm:w-3/4 relative pb-8 sm:pb-0">
                            <div class="flex justify-between">
                                <div class="flex gap-2 sm:gap-4">
                                    <span
                                        v-if="item.outcome_area"
                                        class="bg-blue-100 text-blue-800 px-2 py-1 sm:px-3 rounded-full text-xs sm:text-sm"
                                    >
                                        {{ item.outcome_area }}
                                    </span>
                                    <span
                                        v-if="item.program"
                                        class="bg-green-100 text-green-800 px-2 py-1 sm:px-3 rounded-full text-xs sm:text-sm"
                                    >
                                        {{ item.program }}
                                    </span>
                                </div>
                                <div class="text-right text-xs sm:text-sm text-gray-500">
                                    Posted: {{ formatDate(item.created_at) }}
                                </div>
                            </div>
                            <h2 v-if="item.title.length > 80 && !item.showFullTitle" class="mt-2 sm:mt-4 text-gray-700 text-justify text-sm sm:text-base">
                                {{ item.title.substring(0, 80) }}...
                                <button @click="toggleTitle(item)" class="text-blue-800 hover:text-blue-900 underline ml-1">
                                    Read More
                                </button>
                            </h2>
                            <p v-if="item.showFullTitle" class="mt-2 sm:mt-4 text-gray-700 text-justify text-sm sm:text-base">
                                {{ item.title }}
                                <button @click="toggleTitle(item)" class="text-blue-800 hover:text-blue-900 underline ml-1">
                                    Show Less
                                </button>
                            </p>

                            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                <div class="space-x-2">
                                    <a
                                        v-if="item.file"
                                        :href="`/downloadable_files/${item.file}`"
                                        download
                                        class="inline-block bg-green-600 text-white px-4 sm:px-6 py-1 sm:py-2 rounded-lg hover:bg-green-700 transition-colors duration-300 text-sm sm:text-base text-center"
                                    >
                                        <i class="fas fa-download mr-1"></i> Download
                                    </a>
                                    <a
                                        v-if="item.link"
                                        :href="item.link"
                                        target="_blank"
                                        class="inline-block bg-blue-800 text-white px-4 sm:px-6 py-1 sm:py-2 rounded-lg hover:bg-blue-900 transition-colors duration-300 text-sm sm:text-base text-center"
                                    >
                                        <i class="fas fa-external-link-alt mr-1"></i> View Link
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-center text-gray-500 mt-4 text-sm sm:text-base">No resources available.</p>

            <div class="flex flex-col sm:flex-row justify-between items-center mt-4 sm:mt-6 text-gray-700 text-sm sm:text-base">
                <span>{{ paginationInfo }}</span>
                <div class="flex flex-wrap space-x-1 sm:space-x-2 mt-2 sm:mt-0">
                    <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                        v-html="link.label" :class="{
                            'font-bold bg-blue-300 text-gray-900': link.active,
                            'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                        }" class="px-2 sm:px-4 py-1 border border-gray-300 hover:bg-gray-200 transition text-xs sm:text-sm" :disabled="!link.url">
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.stretch-enter-active,
.stretch-leave-active {
    transition: all 0.3s ease-in-out;
    overflow: hidden;
    max-height: 1000px;
}

.stretch-enter-from,
.stretch-leave-to {
    max-height: 0;
    opacity: 0;
}

.whitespace-pre-line {
    white-space: pre-line;
}
</style>