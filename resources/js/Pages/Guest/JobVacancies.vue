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
    <div class="p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            Job Vacancies
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="relative w-full md:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" type="text" placeholder="Search jobs..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>

                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.position"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none">
                        <option value="">All Positions</option>
                        <option v-for="position in pageProps.positions" :key="position" :value="position">
                            {{ position }}
                        </option>
                    </select>
                </div>

                <div class="relative w-full md:w-1/4">
                    <select v-model="filters.remarks"
                        class="border border-gray-300 px-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none">
                        <option value="">All Remarks</option>
                        <option v-for="remark in pageProps.remarks" :key="remark" :value="remark">
                            {{ remark }}
                        </option>
                    </select>
                </div>
            </div>
        </div>

        <div class="container mx-auto px-4 mt-8">
            <div v-if="jobs.length > 0" class="space-y-6">
                <div
                    v-for="job in jobs"
                    :key="job.id"
                    class="bg-white p-6 rounded-lg shadow-md hover:shadow-lg transition-shadow duration-300"
                >
                    <div class="flex flex-col md:flex-row gap-6">
                        <div class="w-full md:w-1/4">
                            <img
                                :src="job.hiring_img === 'default' ? '/img/hiring_img.jpg' : `/storage/${job.hiring_img}`"
                                alt="Job Image"
                                class="w-full h-48 object-cover rounded-lg"
                            />
                        </div>
                        <div class="w-full md:w-3/4">
                            <div class="flex justify-between items-center">
                                <h2 class="text-xl font-semibold text-blue-900">{{ job.position }}</h2>
                                <span
                                    v-if="job.remarks === 'Filled Up'"
                                    class="bg-red-100 text-red-800 px-3 py-1 rounded-full"
                                >
                                    Filled Up
                                </span>
                                <span
                                    v-else
                                    class="bg-green-200 text-green-900 px-4 py-2 rounded-full"
                                >
                                    {{ job.remarks || 'Available' }}
                                </span>
                            </div>

                            <transition name="stretch">
                                <p v-if="job.showFullDetails" class="mt-4 text-gray-700 text-justify whitespace-pre-line">
                                    {{ job.details }}
                                </p>
                            </transition>
                            <p v-if="!job.showFullDetails && !job.isAnimating" class="mt-4 text-gray-700 text-justify">
                                {{ job.details.substring(0, 100) + (job.details.length > 100 ? '...' : '') }}
                            </p>

                            <div class="mt-6 flex space-x-4">
                                <button
                                    v-if="job.details.length > 100"
                                    @click="toggleDetails(job)"
                                    class="text-blue-800 hover:text-blue-900 underline"
                                >
                                    {{ job.showFullDetails ? 'Show Less' : 'Read More' }}
                                </button>
                                <a
                                    :href="job.link"
                                    target="_blank"
                                    class="inline-block bg-blue-800 text-white px-6 py-2 rounded-lg hover:bg-blue-900 transition-colors duration-300"
                                >
                                    View Details
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <p v-else class="text-center text-gray-500 mt-4">No jobs available.</p>

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
