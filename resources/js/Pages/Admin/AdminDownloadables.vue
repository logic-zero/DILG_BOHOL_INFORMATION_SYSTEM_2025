<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

const props = defineProps({
  downloadables: Object,
  filters: Object,
  outcomeAreas: Array,
  programs: Array,
});

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.downloadables);
const downloadablesList = ref((pageProps.downloadables.data ?? []).map(item => ({
    ...item,
    showFullTitle: false
})));
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingItem = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const itemToDelete = ref(null);
const isFileModalOpen = ref(false);
const selectedFile = ref("");

const openFileModal = (fileUrl) => {
  selectedFile.value = fileUrl;
  isFileModalOpen.value = true;
};

const closeFileModal = () => {
  isFileModalOpen.value = false;
  selectedFile.value = "";
};

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
        fetchDownloadables();
    }, 500)
);

const fetchDownloadables = (url = "/admin/downloadables") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["downloadables", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.downloadables;
            downloadablesList.value = props.downloadables.data.map(item => ({
                ...item,
                showFullTitle: false
            }));
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const goToPage = (url) => url && fetchDownloadables(url);

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
    outcome_area: "",
    program: "",
    file: null,
});


const selectProgram = (selected) => {
    form.program = selected;
};

const selectOutcomeArea = (selected) => {
    form.outcome_area = selected;
};

const openModal = (item = null) => {
    isEditMode.value = !!item;
    editingItem.value = item;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (item) {
        form.id = item.id;
        form.file = null;
        form.title = item.title;
        form.link = item.link;
        form.outcome_area = item.outcome_area;
        form.program = item.program;
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

const submitDownloadable = () => {
    errorMessage.value = "";

    if (!form.title) return (errorMessage.value = "Title is required.");

    const url = isEditMode.value ? `/admin/downloadables/${form.id}` : "/admin/downloadables";

    const formData = new FormData();
    formData.append("title", form.title);
    formData.append("link", form.link);
    formData.append("outcome_area", form.outcome_area);
    formData.append("program", form.program);
    if (form.file) {
        formData.append("file", form.file);
    }

    router.post(url, formData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.downloadables;
            downloadablesList.value = [...props.downloadables.data];
            showSuccessMessage(
                isEditMode.value
                    ? "Downloadable updated successfully!"
                    : "Downloadable added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (item) => {
    itemToDelete.value = item;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    itemToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteDownloadable = async () => {
    if (!itemToDelete.value) return;

    try {
        await form.delete(`/admin/downloadables/${itemToDelete.value.id}`);
        downloadablesList.value = downloadablesList.value.filter((d) => d.id !== itemToDelete.value.id);
        showSuccessMessage("Downloadable deleted successfully!");
        closeDeleteModal();

        if (downloadablesList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/downloadables?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete downloadable.";
    }
};

const toggleTitle = (item) => {
    item.showFullTitle = !item.showFullTitle;
};

const getFileIcon = (fileName) => {
    if (!fileName) return 'fa-file';
    const extension = fileName.split('.').pop().toLowerCase();
    switch(extension) {
        case 'pdf': return 'fa-file-pdf';
        case 'doc':
        case 'docx': return 'fa-file-word';
        case 'xls':
        case 'xlsx': return 'fa-file-excel';
        case 'ppt':
        case 'pptx': return 'fa-file-powerpoint';
        case 'txt': return 'fa-file-alt';
        default: return 'fa-file';
    }
};
</script>

<template>
    <div class="p-4">
        <h1
            class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            DOWNLOADABLE RESOURCES
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
                Add Downloadable
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="fetchDownloadables" type="text"
                    placeholder="Search downloadables..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <button @click="fetchDownloadables"
                    class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded hover:bg-gray-100">
                    <i class="fas fa-search"></i>
                </button>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[20%]">Outcome Area</th>
                        <th class="p-3 text-left w-[20%]">Program</th>
                        <th class="p-3 text-left w-[20%]">Title</th>
                        <th class="p-3 text-left w-[20%]">File</th>
                        <th class="p-3 text-left w-[20%]">Posted on</th>
                        <th class="p-3 text-left w-[20%]">Link</th>
                        <th class="p-3 text-center w-[15%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="item in downloadablesList" :key="item.id"
                        class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words">
                            {{ item.outcome_area || 'N/A' }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ item.program || 'N/A' }}
                        </td>
                        <td class="p-3 text-gray-600 text-justify break-words">
                            {{ item.showFullTitle ? item.title : item.title.substring(0, 100) + (item.title.length > 100 ? '...' : '') }}
                            <button
                                v-if="item.title.length > 100"
                                @click="toggleTitle(item)"
                                class="text-blue-500 hover:underline ml-2"
                            >
                                {{ item.showFullTitle ? 'Show Less' : 'Read More' }}
                            </button>
                        </td>
                        <td class="p-3 break-words">
                            <div v-if="item.file" class="flex items-center gap-2">
                                <i :class="`fas ${getFileIcon(item.file)} text-2xl text-blue-600`"></i>
                                <button @click="openFileModal(`/downloadable_files/${item.file}`)"
                                    class="text-blue-500 hover:underline">
                                    View File
                                </button>
                            </div>
                            <span v-else class="text-gray-400">No file</span>
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ formatDate(item.created_at) }}
                        </td>
                        <td class="p-3 text-gray-600">
                            <div class="w-[200px]">
                                <a
                                    v-if="item.link"
                                    :href="item.link"
                                    target="_blank"
                                    class="text-blue-500 hover:underline block truncate"
                                    :title="item.link"
                                >
                                    {{ item.link }}
                                </a>
                                <span v-else class="text-gray-400">No link</span>
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1 whitespace-nowrap">
                                <button @click="openModal(item)"
                                    class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition w-24">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(item)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition w-20">
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
                    {{ isEditMode ? "Edit Downloadable" : "Add Downloadable" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Outcome Area</label>
                <input v-model="form.outcome_area" placeholder="Enter New Outcome Area" class="border p-2 w-full my-2" />
                <select @change="selectOutcomeArea($event.target.value)" class="border p-2 w-full my-2">
                    <option value="">Select Existing Outcome Area</option>
                    <option v-for="area in outcomeAreas" :key="area" :value="area">{{ area }}</option>
                </select>

                <label class="font-bold block text-gray-700">Program</label>
                <input v-model="form.program" placeholder="Enter New Program" class="border p-2 w-full my-2" />
                <select @change="selectProgram($event.target.value)" class="border p-2 w-full my-2">
                    <option value="">Select Existing Program</option>
                    <option v-for="program in programs" :key="program" :value="program">{{ program }}</option>
                </select>

                <label class="font-bold block text-gray-700">Title</label>
                <input v-model="form.title" placeholder="Enter Title" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Document Link</label>
                <input v-model="form.link" placeholder="Enter External Link" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">You may also upload <span class="text-red-600">PDF</span> File <span class="text-red-600">(Max: 10MB)</span></label>
                <input type="file" @change="form.file = $event.target.files[0]" class="border p-2 w-full my-2" accept=".pdf,.doc,.docx,.xls,.xlsx,.ppt,.pptx,.txt" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitDownloadable"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
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
                    <strong>{{ itemToDelete?.title }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteDownloadable" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
                        Yes, Delete
                    </button>
                    <button @click="closeDeleteModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isFileModalOpen" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 p-4"
             @click.self="closeFileModal">
          <div class="relative max-w-4xl max-h-screen bg-white p-4 rounded-lg">
            <button @click="closeFileModal"
                    class="absolute -top-10 right-0 text-white hover:text-gray-300 focus:outline-none">
              <i class="fas fa-times text-2xl"></i>
            </button>
            <iframe
                :src="selectedFile"
                class="w-full h-[80vh] border-0"
                v-if="selectedFile.endsWith('.pdf')">
            </iframe>
            <div v-else class="p-4 text-center">
                <i :class="`fas ${getFileIcon(selectedFile)} text-6xl text-blue-500 mb-4`"></i>
                <p class="mb-4">This file type cannot be previewed</p>
                <a :href="selectedFile" download class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
                    <i class="fas fa-download mr-2"></i>Download File
                </a>
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

.img-zoom-enter-active,
.img-zoom-leave-active {
  transition: transform 0.3s ease, opacity 0.3s ease;
}

.img-zoom-enter,
.img-zoom-leave-to {
  transform: scale(0.95);
  opacity: 0;
}
</style>
