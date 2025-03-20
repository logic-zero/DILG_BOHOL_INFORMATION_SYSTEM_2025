<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.jobs);
const jobsList = ref((pageProps.jobs.data ?? []).map(job => ({
    ...job,
    showFullDetails: false // Add a property to track whether full details are shown
})));
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingJob = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const jobToDelete = ref(null);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No results found";
});

const formatDate = (dateString) => {
    const date = new Date(dateString);
    const options = {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: 'numeric',
        minute: 'numeric',
        hour12: true,
    };
    return date.toLocaleString('en-US', options).replace(' at ', ', ');
};

const filters = ref({
    search: pageProps.filters?.search ?? "",
});

watch(
    () => filters.value.search,
    debounce(() => {
        fetchJobs();
    }, 500)
);

const fetchJobs = (url = "/admin/jobs") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["jobs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.jobs;
            jobsList.value = props.jobs.data.map(job => ({
                ...job,
                showFullDetails: false // Reset showFullDetails on fetch
            }));
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const goToPage = (url) => url && fetchJobs(url);

const form = useForm({
    id: null,
    hiring_img: null, // Change to handle file upload
    position: "",
    details: "",
    link: "",
    remarks: "Available", // Set default value for remarks
});

const openModal = (job = null) => {
    isEditMode.value = !!job;
    editingJob.value = job;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (job) {
        form.id = job.id;
        form.hiring_img = null; // Reset file input
        form.position = job.position;
        form.details = job.details;
        form.link = job.link;
        form.remarks = job.remarks;
    } else {
        form.reset();
        form.remarks = "Available"; // Set default value for remarks when adding a new job
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    form.remarks = "Available"; // Reset remarks to default value
    errorMessage.value = "";
};

const showSuccessMessage = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 3000);
};

const submitJob = () => {
    errorMessage.value = "";

    if (!form.position) return (errorMessage.value = "Position is required.");
    if (!form.details) return (errorMessage.value = "Details are required.");
    if (!form.link) return (errorMessage.value = "Link is required.");

    const url = isEditMode.value ? `/admin/jobs/${form.id}` : "/admin/jobs";

    // Use FormData to handle file uploads
    const formData = new FormData();
    formData.append("position", form.position);
    formData.append("details", form.details);
    formData.append("link", form.link);
    formData.append("remarks", form.remarks);
    if (form.hiring_img) {
        formData.append("hiring_img", form.hiring_img);
    }

    router.post(url, formData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.jobs;
            jobsList.value = [...props.jobs.data];
            showSuccessMessage(
                isEditMode.value
                    ? "Job updated successfully!"
                    : "Job added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (job) => {
    jobToDelete.value = job;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    jobToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteJob = async () => {
    if (!jobToDelete.value) return;

    try {
        await form.delete(`/admin/jobs/${jobToDelete.value.id}`);
        jobsList.value = jobsList.value.filter((j) => j.id !== jobToDelete.value.id);
        showSuccessMessage("Job deleted successfully!");
        closeDeleteModal();

        if (jobsList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/jobs?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete job.";
    }
};

const toggleDetails = (job) => {
    job.showFullDetails = !job.showFullDetails;
};
</script>

<template>
    <div class="p-4">
        <h1
            class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 dark:text-white border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            Jobs Vacancies
        </h1>

        <transition name="fade">
            <div v-if="showSuccess"
                class="fixed top-20 right-5 bg-green-500 text-white p-3 rounded shadow-lg z-50 flex items-center gap-2">
                <i class="fas fa-check-circle text-white text-lg"></i>
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
            <button @click="openModal()"
                class="bg-blue-800 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-blue-900 w-full md:w-auto">
                <i class="fas fa-plus-circle"></i>
                Add Job
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="fetchJobs" type="text"
                    placeholder="Search Jobs..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <button @click="fetchJobs"
                    class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded hover:bg-gray-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[15%]">Remarks</th>
                        <th class="p-3 text-left w-[10%]">Image</th>
                        <th class="p-3 text-left w-[15%]">Position</th>
                        <th class="p-3 text-left w-[15%]">Details</th>
                        <th class="p-3 text-left w-[15%]">Date Posted</th>
                        <th class="p-3 text-left w-[15%]">Document Link</th>
                        <th class="p-3 text-center w-[15%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="job in jobsList" :key="job.id"
                        class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words">
                            {{ job.remarks }}
                        </td>
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            <img 
                                :src="job.hiring_img === 'default' ? '/img/hiring_img.jpg' : `/storage/${job.hiring_img}`" 
                                alt="Hiring Image" 
                                class="w-16 h-16 object-cover rounded" 
                            />
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ job.position }}
                        </td>
                        <td class="p-3 text-gray-600 text-justify break-words">
                            {{ job.showFullDetails ? job.details : job.details.substring(0, 100) + (job.details.length > 100 ? '...' : '') }}
                            <button
                                v-if="job.details.length > 100"
                                @click="toggleDetails(job)"
                                class="text-blue-500 hover:underline ml-2"
                            >
                                {{ job.showFullDetails ? 'Show Less' : 'Read More' }}
                            </button>
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ formatDate(job.created_at) }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            <a :href="job.link" target="_blank" class="text-blue-500 hover:underline">{{ job.link }}</a>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(job)"
                                    class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(job)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition"
                    :disabled="!link.url"></button>
            </div>
        </div>

        <!-- Add/Edit Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 shadow-lg w-full max-w-lg mx-4 max-h-[95vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit Job" : "Add Job" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Remarks</label>
                <input v-model="form.remarks" placeholder="Enter Remarks" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Image</label>
                <input type="file" @change="form.hiring_img = $event.target.files[0]" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Position</label>
                <input v-model="form.position" placeholder="Enter Position" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Details</label>
                <textarea v-model="form.details" placeholder="Enter Details" class="border p-2 w-full my-2"></textarea>

                <label class="font-bold block text-gray-700">Document Link</label>
                <input v-model="form.link" placeholder="Enter Link" class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitJob"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <!-- Delete Modal -->
        <div v-if="isDeleteModalOpen"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4 text-center">Are you sure?</h2>
                <p class="text-center mb-4">
                    Do you really want to delete
                    <strong>{{ jobToDelete?.position }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteJob" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                        Yes, Delete
                    </button>
                    <button @click="closeDeleteModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>