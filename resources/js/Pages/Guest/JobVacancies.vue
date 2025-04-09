<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const jobs = ref((pageProps.jobs.data ?? []).map(job => ({
    ...job,
    showFullDetails: false,
    isAnimating: false
})));
const pagination = ref(pageProps.jobs);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    position: pageProps.filters?.position ?? "",
    remarks: pageProps.filters?.remarks ?? "",
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
    router.get("/jobVacancies", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["jobs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.jobs;
            jobs.value = props.jobs.data.map(job => ({
                ...job,
                showFullDetails: false,
                isAnimating: false
            }));
        },
    });
};

const debouncedApplyFilters = debounce(applyFilters, 500);

watch(() => filters.value.search, debouncedApplyFilters);
watch(() => filters.value.position, debouncedApplyFilters);
watch(() => filters.value.remarks, debouncedApplyFilters);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["jobs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.jobs;
            jobs.value = props.jobs.data.map(job => ({
                ...job,
                showFullDetails: false,
                isAnimating: false
            }));
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const toggleDetails = (job) => {
    if (job.isAnimating) return;

    job.isAnimating = true;
    if (job.showFullDetails) {
        setTimeout(() => {
            job.showFullDetails = false;
            job.isAnimating = false;
        }, 300);
    } else {
        job.showFullDetails = true;
        setTimeout(() => {
            job.isAnimating = false;
        }, 300);
    }
};
</script>

<template>
    <div class="p-2 lg:p-6 w-full">
        <div class="bg-blue-800 text-white py-2 sm:py-4 mb-4 sm:mb-6">
            <div class="container mx-auto px-4 text-center">
                <h1 class="text-2xl sm:text-4xl font-bold mb-2 sm:mb-4">Join Our Team</h1>
                <p class="text-sm sm:text-lg">Explore exciting job opportunities with us</p>
            </div>
        </div>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-4 sm:mb-6">
            <div class="flex flex-col sm:flex-row items-start gap-2 sm:gap-3">
                <div class="relative w-full sm:flex-1">
                    <i class="absolute left-3 top-2 sm:top-3 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search jobs..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none text-sm sm:text-base" />
                </div>

                <div class="relative w-full sm:w-1/2">
                    <select v-model="filters.position"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none text-sm sm:text-base">
                        <option value="">All Positions</option>
                        <option v-for="position in pageProps.positions" :key="position" :value="position">
                            {{ position }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-2 sm:px-4 mt-4 sm:mt-8">
            <div v-if="jobs.length > 0" class="space-y-4 sm:space-y-6">
                <div
                    v-for="job in jobs"
                    :key="job.id"
                    class="bg-white p-3 sm:p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300"
                >
                    <div class="flex flex-col sm:flex-row gap-4 sm:gap-6">
                        <div class="w-full sm:w-1/4">
                            <img
                                :src="job.hiring_img === 'default' ? '/img/hiring_img.jpg' : `/hiring_images/${job.hiring_img}`"
                                alt="Job Image"
                                class="w-full h-32 sm:h-48 object-cover rounded-lg"
                            />
                        </div>
                        <div class="w-full sm:w-3/4 relative pb-8 sm:pb-0">
                            <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-2">
                                <h2 class="text-lg sm:text-xl font-semibold text-blue-900">{{ job.position }}</h2>
                                <span
                                    v-if="job.remarks === 'Filled Up'"
                                    class="bg-red-100 text-red-800 px-2 py-1 sm:px-3 rounded-full text-xs sm:text-sm self-start sm:self-auto"
                                >
                                    Filled Up
                                </span>
                                <span
                                    v-else
                                    class="bg-green-200 text-green-900 px-2 py-1 sm:px-3 rounded-xl text-xs sm:text-sm self-start sm:self-auto"
                                >
                                    {{ job.remarks || 'Available' }}
                                </span>
                            </div>

                            <transition name="stretch">
                                <p v-if="job.showFullDetails" class="mt-2 sm:mt-4 text-gray-700 text-justify whitespace-pre-line text-sm sm:text-base">
                                    {{ job.details }}
                                </p>
                            </transition>
                            <p v-if="!job.showFullDetails && !job.isAnimating" class="mt-2 sm:mt-4 text-gray-700 text-justify text-sm sm:text-base">
                                {{ job.details.substring(0, 80) + (job.details.length > 80 ? '...' : '') }}
                            </p>

                            <div class="mt-4 sm:mt-6 flex flex-col sm:flex-row sm:space-x-4 space-y-2 sm:space-y-0">
                                <button
                                    v-if="job.details.length > 80"
                                    @click="toggleDetails(job)"
                                    class="text-blue-800 hover:text-blue-900 underline text-sm sm:text-base text-left"
                                >
                                    {{ job.showFullDetails ? 'Show Less' : 'Read More' }}
                                </button>
                                <a
                                    :href="job.link"
                                    target="_blank"
                                    class="inline-block bg-blue-800 text-white px-4 sm:px-6 py-1 sm:py-2 rounded-lg hover:bg-blue-900 transition-colors duration-300 text-sm sm:text-base text-center"
                                >
                                    View Details
                                </a>
                            </div>

                            <div class="absolute bottom-2 right-0 sm:bottom-0">
                                <p class="text-xs sm:text-sm text-gray-500 text-right">Posted: {{ formatDate(job.created_at) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-center text-gray-500 mt-4 text-sm sm:text-base">No jobs available.</p>

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
