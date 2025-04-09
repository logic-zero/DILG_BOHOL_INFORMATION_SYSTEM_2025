<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pdmusList = ref(pageProps.pdmus ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingPDMU = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const pdmuToDelete = ref(null);

const filters = ref({
    search: pageProps.filters?.search ?? "",
});

watch(
    () => filters.value,
    debounce(() => {
        fetchPDMUs();
    }, 500),
    { deep: true }
);

const fetchPDMUs = () => {
    router.get("/admin/pdmu", filters.value, {
        preserveState: true,
        preserveScroll: true,
        onSuccess: ({ props }) => {
            pdmusList.value = props.pdmus;
        },
    });
};

const form = useForm({
    id: null,
    profile_img: null,
    fname: "",
    mid_initial: "",
    lname: "",
    position: "",
});

const openModal = (pdmu = null) => {
    isEditMode.value = !!pdmu;
    editingPDMU.value = pdmu;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (pdmu) {
        form.id = pdmu.id;
        form.fname = pdmu.fname;
        form.mid_initial = pdmu.mid_initial;
        form.lname = pdmu.lname;
        form.position = pdmu.position;
        form.profile_img = null;
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

const submitPDMU = () => {
    errorMessage.value = "";

    if (!form.fname) return (errorMessage.value = "First Name is required.");
    if (!form.lname) return (errorMessage.value = "Last Name is required.");
    if (!form.position) return (errorMessage.value = "Position is required.");

    const url = isEditMode.value
        ? `/admin/pdmu/${form.id}`
        : "/admin/pdmu";

    const formattedData = new FormData();
    formattedData.append("fname", form.fname);
    formattedData.append("mid_initial", form.mid_initial);
    formattedData.append("lname", form.lname);
    formattedData.append("position", form.position);
    if (form.profile_img) formattedData.append("profile_img", form.profile_img);

    router.post(url, formattedData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pdmusList.value = props.pdmus;
            showSuccessMessage(
                isEditMode.value
                    ? "PDMU updated successfully!"
                    : "PDMU added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (pdmu) => {
    pdmuToDelete.value = pdmu;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    pdmuToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deletePDMU = async () => {
    if (!pdmuToDelete.value) return;

    try {
        await form.delete(`/admin/pdmu/${pdmuToDelete.value.id}`);
        pdmusList.value = pdmusList.value.filter(
            (o) => o.id !== pdmuToDelete.value.id
        );
        showSuccessMessage("PDMU deleted successfully!");
        closeDeleteModal();
    } catch (error) {
        errorMessage.value = "Failed to delete PDMU.";
    }
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            PDMU
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
                Add PDMU
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="fetchPDMUs" type="text" placeholder="Search PDMUs..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[20%]">Profile Image</th>
                        <th class="p-3 text-left w-[25%]">Name</th>
                        <th class="p-3 text-left w-[25%]">Position</th>
                        <th class="p-3 text-center w-[30%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="pdmu in pdmusList" :key="pdmu.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words flex justify-center">
                            <img v-if="pdmu.profile_img" :src="`/pdmus/${pdmu.profile_img}`" class="w-20 h-20 rounded-full object-cover" alt="Profile Image" />
                            <span v-else>No Image</span>
                        </td>
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ pdmu.fname }} {{ pdmu.mid_initial }} {{ pdmu.lname }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ pdmu.position }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(pdmu)" class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(pdmu)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="pdmu in pdmusList" :key="pdmu.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-center mb-4">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <img v-if="pdmu.profile_img" :src="`/pdmus/${pdmu.profile_img}`" class="w-full h-full object-cover" alt="Profile Image" />
                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600">
                            No Image
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h2 class="text-xl font-extrabold text-gray-900 mb-2">
                        {{ pdmu.fname }} {{ pdmu.mid_initial }} {{ pdmu.lname }}
                    </h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Position:</span> {{ pdmu.position }}
                    </p>
                </div>

                <div class="mt-4 flex gap-2">
                    <button @click="openModal(pdmu)" class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>
                    <button @click="openDeleteModal(pdmu)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 shadow-lg w-full max-w-lg mx-4 max-h-[95vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit PDMU" : "Add PDMU" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">First Name</label>
                <input v-model="form.fname" placeholder="Enter First Name" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Middle Initial</label>
                <input v-model="form.mid_initial" placeholder="Enter Middle Initial" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Last Name</label>
                <input v-model="form.lname" placeholder="Enter Last Name" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Position</label>
                <input v-model="form.position" placeholder="Enter Position" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Profile Image</label>
                <input type="file" @change="form.profile_img = $event.target.files[0]" accept="image/*" class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitPDMU" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4 text-center">Are you sure?</h2>
                <p class="text-center mb-4">
                    Do you really want to delete
                    <strong>{{ pdmuToDelete?.fname }} {{ pdmuToDelete?.lname }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deletePDMU" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
