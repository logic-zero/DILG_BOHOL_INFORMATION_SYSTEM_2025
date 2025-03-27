<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.charters);
const chartersList = ref(pageProps.charters.data ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingCharter = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const charterToDelete = ref(null);
const isUploadModalOpen = ref(false);
const uploadProgress = ref(0);
const isUploading = ref(false);
const cancelUpload = ref(() => {});
const uploadStartTime = ref(null);
const estimatedTimeRemaining = ref('');
const thumbnailPreview = ref(null);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No results found";
});

const filters = ref({
    search: pageProps.filters?.search ?? "",
});

watch(
    () => filters.value.search,
    debounce(() => {
        applyFilters();
    }, 500)
);

const fetchCharters = (url = "/admin/citizens-charter") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["charters", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.charters;
            chartersList.value = props.charters.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const applyFilters = () => fetchCharters();
const goToPage = (url) => url && fetchCharters(url);

const form = useForm({
    id: null,
    title: "",
    file: null,
    thumbnail: null,
});

const openModal = (charter = null) => {
    isEditMode.value = !!charter;
    editingCharter.value = charter;
    isModalOpen.value = true;
    errorMessage.value = "";
    thumbnailPreview.value = null;

    if (charter) {
        form.id = charter.id;
        form.title = charter.title;
        form.file = null;
        form.thumbnail = null;
        if (charter.thumbnail) {
            thumbnailPreview.value = `/storage/${charter.thumbnail}`;
        }
    } else {
        form.reset();
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    errorMessage.value = "";
    thumbnailPreview.value = null;
};

const showSuccessMessage = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 3000);
};

const handleThumbnailChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 5 * 1024 * 1024) {
            errorMessage.value = "Thumbnail must be less than 5MB";
            return;
        }
        form.thumbnail = file;
        thumbnailPreview.value = URL.createObjectURL(file);
    } else {
        form.thumbnail = null;
        thumbnailPreview.value = null;
    }
};

const submitCharter = () => {
    errorMessage.value = "";

    if (!form.title || (!isEditMode.value && !form.file)) {
        errorMessage.value = "Please fill in all required fields.";
        return;
    }

    isUploadModalOpen.value = true;
    isUploading.value = true;
    uploadProgress.value = 0;
    uploadStartTime.value = new Date();

    const formData = new FormData();
    formData.append("title", form.title);
    if (form.file) formData.append("file", form.file);
    if (form.thumbnail) formData.append("thumbnail", form.thumbnail);

    const source = axios.CancelToken.source();
    cancelUpload.value = () => {
        source.cancel("Upload cancelled by user");
        isUploading.value = false;
        isUploadModalOpen.value = false;
    };

    const calculateETA = () => {
        if (uploadProgress.value > 0 && uploadProgress.value < 100) {
            const timeElapsed = (new Date() - uploadStartTime.value) / 1000;
            const totalEstimatedTime = timeElapsed * (100 / uploadProgress.value);
            const remainingTime = totalEstimatedTime - timeElapsed;
            estimatedTimeRemaining.value = `${Math.ceil(remainingTime)}s remaining`;
        }
    };

    const config = {
        onUploadProgress: (progressEvent) => {
            const percentCompleted = Math.round(
                (progressEvent.loaded * 100) / progressEvent.total
            );
            uploadProgress.value = percentCompleted;
            calculateETA();
        },
        cancelToken: source.token,
    };

    const url = isEditMode.value
        ? `/admin/citizens-charter/${form.id}`
        : "/admin/citizens-charter";

    axios
        .post(url, formData, config)
        .then((response) => {
            showSuccessMessage(response.data.success);
            fetchCharters();
            closeModal();
        })
        .catch((error) => {
            if (!axios.isCancel(error)) {
                errorMessage.value =
                    error.response?.data?.message || "An error occurred during upload.";
            }
        })
        .finally(() => {
            isUploading.value = false;
        });
};

const openDeleteModal = (charter) => {
    charterToDelete.value = charter;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    charterToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteCharter = async () => {
    if (!charterToDelete.value) return;

    try {
        await form.delete(`/admin/citizens-charter/${charterToDelete.value.id}`);
        chartersList.value = chartersList.value.filter(
            (o) => o.id !== charterToDelete.value.id
        );
        showSuccessMessage("Video deleted successfully!");
        closeDeleteModal();

        if (chartersList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/citizens-charter?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete video.";
    }
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            CITIZENS CHARTER
        </h1>
        <transition name="fade">
            <div v-if="showSuccess" class="fixed top-20 right-5 bg-green-500 text-white p-3 rounded shadow-lg z-50 flex items-center gap-2">
                <i class="fas fa-check-circle text-white text-lg"></i>
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
            <button @click="openModal()" class="bg-blue-800 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-blue-900 w-full md:w-auto">
                <i class="fas fa-plus-circle"></i>
                Add Video
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search videos..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="space-y-4">
            <div v-for="charter in chartersList" :key="charter.id" class="border max-w-5xl mx-auto rounded shadow-xl bg-gray-100 p-4">
                <h2 class="text-xl font-extrabold text-blue-800 pb-4 bg-gray-200 border border-gray-500 max-w-5xl mx-auto p-1 mb-2 text-center">
                    {{ charter.title }}
                </h2>

                <div class="max-w-5xl mx-auto overflow-hidden border border-gray-500">
                    <video
                        controls
                        class="w-full aspect-[3/2]"
                        :poster="charter.thumbnail ? `/storage/${charter.thumbnail}` : '/img/default-video-thumbnail.jpg'"
                        preload="none"
                    >
                        <source :src="`/storage/${charter.file}`" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>

                <div class="mt-4 flex max-w-5xl mx-auto gap-2">
                    <button @click="openModal(charter)" class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button @click="openDeleteModal(charter)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)" v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url"></button>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit Video" : "Add Video" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Title</label>
                <input v-model="form.title" placeholder="Enter video title" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Video File (MP4 - max 100MB)</label>
                <input
                    type="file"
                    @change="form.file = $event.target.files[0]"
                    class="border p-2 w-full my-2"
                    accept="video/mp4,video/quicktime,video/x-msvideo"
                />

                <label class="font-bold block text-gray-700">Thumbnail (Optional, JPEG/PNG/JPG - max 5MB)</label>
                <input
                    type="file"
                    @change="handleThumbnailChange"
                    class="border p-2 w-full my-2"
                    accept="image/jpeg,image/png,image/jpg,image/gif"
                />

                <div v-if="thumbnailPreview" class="mt-2">
                    <img :src="thumbnailPreview" class="w-full aspect-video object-cover rounded border" alt="Thumbnail preview">
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button
                        @click="submitCharter"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1"
                        :disabled="isUploading"
                    >
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button
                        @click="closeModal"
                        class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500"
                        :disabled="isUploading"
                    >
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isUploadModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 z-50">
            <div class="bg-white p-6 rounded-lg shadow-xl w-full max-w-md mx-4">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ isUploading ? 'Uploading Video' : 'Upload Complete' }}
                    </h2>
                    <div class="flex items-center space-x-2">
                        <span class="text-sm font-medium text-gray-600">
                            {{ uploadProgress }}%
                        </span>
                        <span v-if="isUploading" class="text-xs text-gray-500">
                            {{ estimatedTimeRemaining }}
                        </span>
                    </div>
                </div>

                <div class="relative pt-1 mb-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <span class="inline-block h-3 w-3 rounded-full bg-blue-500 animate-pulse" v-if="isUploading"></span>
                        </div>
                    </div>
                    <div class="overflow-hidden h-3 mb-2 text-xs flex rounded-full bg-gray-200">
                        <div
                            :style="`width: ${uploadProgress}%`"
                            class="shadow-none flex flex-col text-center whitespace-nowrap text-white justify-center bg-gradient-to-r from-blue-500 to-blue-600 transition-all duration-300 ease-out"
                        ></div>
                    </div>
                </div>

                <div class="flex flex-col space-y-2">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>File:</span>
                        <span class="font-medium truncate max-w-xs">{{ form.file?.name || 'No file selected' }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Thumbnail:</span>
                        <span class="font-medium truncate max-w-xs">{{ form.thumbnail?.name || 'No thumbnail selected' }}</span>
                    </div>
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Status:</span>
                        <span :class="{
                            'text-blue-600': isUploading,
                            'text-green-600': !isUploading
                        }">
                            {{ isUploading ? 'Uploading...' : 'Completed' }}
                        </span>
                    </div>
                </div>

                <div class="mt-6 flex justify-end space-x-3">
                    <button
                        v-if="isUploading"
                        @click="cancelUpload"
                        class="px-4 py-2 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Cancel
                    </button>
                    <button
                        v-else
                        @click="isUploadModalOpen = false"
                        class="px-4 py-2 bg-blue-600 rounded-md text-sm font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500"
                    >
                        Done
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4 text-center">Are you sure?</h2>
                <p class="text-center mb-4">
                    Do you really want to delete
                    <strong>{{ charterToDelete?.title }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteCharter" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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

<style>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.5s;
}

.fade-enter,
.fade-leave-to {
    opacity: 0;
}
</style>
