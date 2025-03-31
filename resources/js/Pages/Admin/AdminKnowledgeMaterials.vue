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

const form = useForm({
    id: null,
    title: "",
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
    if (!isEditMode.value && !form.file) return (errorMessage.value = "File is required.");

    const url = isEditMode.value
        ? `/admin/knowledge-materials/${form.id}`
        : "/admin/knowledge-materials";

    const formattedData = new FormData();
    formattedData.append("title", form.title);
    formattedData.append("date", form.date);
    if (form.file) formattedData.append("file", form.file);

    router.post(url, formattedData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.materials;
            materialsList.value = [...props.materials.data];
            showSuccessMessage(
                isEditMode.value
                    ? "Material updated successfully!"
                    : "Material added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
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
                        <th class="p-3 text-left w-[40%]">Title</th>
                        <th class="p-3 text-left w-[15%]">Date</th>
                        <th class="p-3 text-left w-[25%]">File</th>
                        <th class="p-3 text-center w-[20%]">Actions</th>
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

                        <p class="text-sm text-gray-600">
                            <button v-if="material.file" @click="downloadFile(material)"
                                class="text-blue-600 hover:text-blue-800 flex items-center gap-1 ml-1">
                                <i class="fas fa-file-pdf text-red-500"></i>
                                Download PDF
                            </button>
                            <span v-else class="text-gray-400 ml-1">No file</span>
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
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition"
                    :disabled="!link.url"></button>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 shadow-lg w-full max-w-lg mx-4 max-h-[95vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit Material" : "Add Material" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Title</label>
                <input v-model="form.title" placeholder="Enter Title" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Date</label>
                <input v-model="form.date" type="date" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">File (PDF only)</label>
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

        <div v-if="isDeleteModalOpen"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
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
