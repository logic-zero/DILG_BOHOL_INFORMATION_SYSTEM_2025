<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.materials);
const materialsList = ref(pageProps.materials.data ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingMaterial = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const materialToDelete = ref(null);
const isUploadModalOpen = ref(false);
const uploadProgress = ref(0);
const isUploading = ref(false);
const cancelUpload = ref(() => {});
const uploadStartTime = ref(null);
const estimatedTimeRemaining = ref('');

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
        fetchMaterials();
    }, 500)
);

const fetchMaterials = (url = "/admin/knowledge-materials") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["materials", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.materials;
            materialsList.value = props.materials.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const goToPage = (url) => url && fetchMaterials(url);

const visiblePages = computed(() => {
    const current = pagination.value.current_page;
    const last = pagination.value.last_page;
    const pages = [];

    if (last <= 1) return pages;

    if (current !== 1) {
        pages.push({ label: '« First', url: pagination.value.first_page_url });
    }

    if (current > 1) {
        pages.push({ label: '‹ Prev', url: pagination.value.prev_page_url });
    }

    pages.push({
        label: current.toString(),
        url: pagination.value.path + '?page=' + current,
        active: true
    });

    if (current < last) {
        pages.push({ label: 'Next ›', url: pagination.value.next_page_url });
    }

    if (current !== last) {
        pages.push({ label: 'Last »', url: pagination.value.last_page_url });
    }

    return pages;
});

const form = useForm({
    id: null,
    title: "",
    link: "",
    date: "",
    file: null,
});

const openModal = (material = null) => {
    isEditMode.value = !!material;
    editingMaterial.value = material;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (material) {
        form.id = material.id;
        form.title = material.title;
        form.link = material.link;
        form.date = material.date;
        form.file = null;
    } else {
        form.reset();
    }
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
    errorMessage.value = "";
};

const showSuccessMessage = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 3000);
};

const submitMaterial = () => {
    errorMessage.value = "";

    if (!form.title) return (errorMessage.value = "Title is required.");
    if (!form.date) return (errorMessage.value = "Date is required.");
    if (!isEditMode.value && !form.file && !form.link) return (errorMessage.value = "File or Link is required.");

    isUploadModalOpen.value = true;
    isUploading.value = true;
    uploadProgress.value = 0;
    uploadStartTime.value = new Date();

    const formattedData = new FormData();
    formattedData.append("title", form.title);
    formattedData.append("link", form.link);
    formattedData.append("date", form.date);
    if (form.file) formattedData.append("file", form.file);

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
        ? `/admin/knowledge-materials/${form.id}`
        : "/admin/knowledge-materials";

    axios
        .post(url, formattedData, config)
        .then((response) => {
            showSuccessMessage(response.data.success);
            fetchMaterials();
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

const openDeleteModal = (material) => {
    materialToDelete.value = material;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    materialToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteMaterial = async () => {
    if (!materialToDelete.value) return;

    try {
        await form.delete(`/admin/knowledge-materials/${materialToDelete.value.id}`);
        materialsList.value = materialsList.value.filter((i) => i.id !== materialToDelete.value.id);
        showSuccessMessage("Material deleted successfully!");
        closeDeleteModal();

        if (materialsList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/knowledge-materials?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete Material.";
    }
};

const downloadFile = (material) => {
    window.open(`/admin/knowledge-materials/${material.id}/download`, '_blank');
};
</script>

<template>
    <div class="p-4">
        <h1
            class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            KNOWLEDGE MATERIALS
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
                Add Material
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="fetchMaterials" type="text"
                    placeholder="Search Materials..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[25%]">Title</th>
                        <th class="p-3 text-left w-[15%]">Date</th>
                        <th class="p-3 text-left w-[20%]">File</th>
                        <th class="p-3 text-left w-[15%]">Link</th>
                        <th class="p-3 text-center w-[25%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="material in materialsList" :key="material.id"
                        class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ material.title }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ new Date(material.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            <button v-if="material.file" @click="downloadFile(material)"
                                class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                Download PDF
                            </button>
                            <span v-else class="text-gray-400">No file</span>
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            <a v-if="material.link" :href="material.link" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
                                <i class="fas fa-link"></i>
                                Open Link
                            </a>
                            <span v-else class="text-gray-400">No link</span>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(material)"
                                    class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(material)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="material in materialsList" :key="material.id"
                class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-between items-start flex-wrap gap-2">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-extrabold mb-2 text-gray-900">
                            {{ material.title }}
                        </h2>

                        <p class="text-sm text-gray-600 mb-2">
                            {{ new Date(material.date).toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' }) }}
                        </p>

                        <p class="text-sm text-gray-600 mb-2">
                            <button v-if="material.file" @click="downloadFile(material)"
                                class="text-blue-600 hover:text-blue-800 flex items-center gap-1 ml-1">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                Download PDF
                            </button>
                            <span v-else class="text-gray-400 ml-1">No file</span>
                        </p>

                        <p class="text-sm text-gray-600">
                            <a v-if="material.link" :href="material.link" target="_blank" class="text-blue-600 hover:text-blue-800 flex items-center gap-1 ml-1">
                                <i class="fas fa-link"></i>
                                Open Link
                            </a>
                            <span v-else class="text-gray-400 ml-1">No link</span>
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex gap-2">
                    <button @click="openModal(material)"
                        class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>

                    <button @click="openDeleteModal(material)"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-1 mt-2 sm:mt-0">
                <button v-for="(link, index) in visiblePages" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-2 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center px-2 py-4">
            <div class="bg-white p-2 lg:p-6 shadow-lg w-full max-w-lg max-h-[95vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit Material" : "Add Material" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Title</label>
                <input v-model="form.title" placeholder="Enter Title" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Link</label>
                <input v-model="form.link" placeholder="Enter AnyFlip URL (https://)" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Date</label>
                <input v-model="form.date" type="date" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">PDF File (Optional)</label>
                <input type="file" @change="form.file = $event.target.files[0]" accept=".pdf"
                    class="border p-2 w-full my-2" />

                <div v-if="isEditMode && editingMaterial?.file" class="mb-2">
                    <p class="text-sm text-gray-600">Current file:</p>
                    <button @click="downloadFile(editingMaterial)"
                        class="text-blue-600 hover:text-blue-800 flex items-center gap-1">
                        <i class="fas fa-file-pdf text-red-500"></i>
                        Download current PDF
                    </button>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitMaterial"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isUploadModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center px-2 py-4 z-50">
            <div class="bg-white p-2 lg:p-6 rounded-lg shadow-xl w-full max-w-md">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-gray-800">
                        {{ isUploading ? 'Uploading File' : 'Upload Complete' }}
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

        <div v-if="isDeleteModalOpen"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center px-2 py-4">
            <div class="bg-white p-2 lg:p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl mb-4 text-center">Are you sure?</h2>
                <p class="text-center mb-4">
                    Do you really want to delete
                    <strong>{{ materialToDelete?.title }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteMaterial" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
