<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.fieldOfficers);
const fieldOfficersList = ref(pageProps.fieldOfficers.data ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingFieldOfficer = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const fieldOfficerToDelete = ref(null);

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No results found";
});

const filters = ref({
    search: pageProps.filters?.search ?? "",
    municipality_id: pageProps.filters?.municipality_id ?? "",
});

watch(
    () => filters.value.search,
    debounce(() => {
        applyFilters();
    }, 500)
);

const fetchFieldOfficers = (url = "/admin/field-officers") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["fieldOfficers", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.fieldOfficers;
            fieldOfficersList.value = props.fieldOfficers.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const applyFilters = () => fetchFieldOfficers();
const goToPage = (url) => url && fetchFieldOfficers(url);

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

const pageOptions = computed(() => {
    const pages = [];
    for (let i = 1; i <= pagination.value.last_page; i++) {
        pages.push({
            value: i,
            label: i.toString(),
            url: pagination.value.path + '?page=' + i
        });
    }
    return pages;
});

const form = useForm({
    id: null,
    municipality_id: "",
    profile_img: null,
    fname: "",
    mid_initial: "",
    lname: "",
    position: "",
    cluster: "",
});

const openModal = (fieldOfficer = null) => {
    isEditMode.value = !!fieldOfficer;
    editingFieldOfficer.value = fieldOfficer;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (fieldOfficer) {
        form.id = fieldOfficer.id;
        form.municipality_id = fieldOfficer.municipality_id;
        form.profile_img = null;
        form.fname = fieldOfficer.fname;
        form.mid_initial = fieldOfficer.mid_initial;
        form.lname = fieldOfficer.lname;
        form.position = fieldOfficer.position;
        form.cluster = fieldOfficer.cluster;
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

const submitFieldOfficer = () => {
    errorMessage.value = "";

    if (!form.fname || !form.lname || !form.position || !form.municipality_id) {
        errorMessage.value = "Please fill in all required fields.";
        return;
    }

    const formData = new FormData();
    formData.append("municipality_id", form.municipality_id);
    formData.append("fname", form.fname);
    formData.append("mid_initial", form.mid_initial);
    formData.append("lname", form.lname);
    formData.append("position", form.position);
    formData.append("cluster", form.cluster);
    if (form.profile_img) formData.append("profile_img", form.profile_img);

    const onSuccess = (page) => {
        pagination.value = page.props.fieldOfficers;
        fieldOfficersList.value = [...page.props.fieldOfficers.data];
        showSuccessMessage(
            isEditMode.value
                ? "Field officer updated successfully!"
                : "Field officer added successfully!"
        );
        closeModal();
    };

    const onError = (errors) => {
        errorMessage.value = errors[Object.keys(errors)[0]] || "An error occurred.";
    };

    router.post(
        isEditMode.value ? `/admin/field-officers/${form.id}` : "/admin/field-officers",
        formData,
        {
            preserveScroll: true,
            preserveState: true,
            headers: { "Content-Type": "multipart/form-data" },
            onSuccess,
            onError,
        }
    );
};

const openDeleteModal = (fieldOfficer) => {
    fieldOfficerToDelete.value = fieldOfficer;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    fieldOfficerToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteFieldOfficer = async () => {
    if (!fieldOfficerToDelete.value) return;

    try {
        await form.delete(`/admin/field-officers/${fieldOfficerToDelete.value.id}`);
        fieldOfficersList.value = fieldOfficersList.value.filter(
            (o) => o.id !== fieldOfficerToDelete.value.id
        );
        showSuccessMessage("Field officer deleted successfully!");
        closeDeleteModal();

        if (fieldOfficersList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/field-officers?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete field officer.";
    }
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            FIELD OFFICERS
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
                Add Field Officer
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search field officers..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>

            <select v-model="filters.municipality_id" @change="applyFilters" class="border p-2 rounded w-full md:w-52">
                <option value="">All Municipalities</option>
                <option v-for="municipality in pageProps.municipalities" :key="municipality.id" :value="municipality.id">
                    {{ municipality.municipality }}
                </option>
            </select>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[15%]">Profile Image</th>
                        <th class="p-3 text-left w-[25%]">Name</th>
                        <th class="p-3 text-left w-[20%]">Municipality</th>
                        <th class="p-3 text-left w-[10%]">Position</th>
                        <th class="p-3 text-left w-[10%]">Cluster</th>
                        <th class="p-3 text-center w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="fieldOfficer in fieldOfficersList" :key="fieldOfficer.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words flex justify-center">
                            <img v-if="fieldOfficer.profile_img" loading="lazy" :src="`/field_officers/${fieldOfficer.profile_img}`" class="w-20 h-20 rounded-full object-cover" alt="Profile Image" />
                            <span v-else>No Image</span>
                        </td>
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ fieldOfficer.fname }} {{ fieldOfficer.mid_initial }} {{ fieldOfficer.lname }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ fieldOfficer.municipality.municipality }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ fieldOfficer.position }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ fieldOfficer.cluster }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(fieldOfficer)" class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(fieldOfficer)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="fieldOfficer in fieldOfficersList" :key="fieldOfficer.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-center mb-4">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <img v-if="fieldOfficer.profile_img" loading="lazy" :src="`/field_officers/${fieldOfficer.profile_img}`" class="w-full h-full object-cover" alt="Profile Image" />
                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600">
                            No Image
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h2 class="text-xl font-extrabold text-gray-900 mb-2">
                        {{ fieldOfficer.fname }} {{ fieldOfficer.mid_initial }} {{ fieldOfficer.lname }}
                    </h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Municipality:</span> {{ fieldOfficer.municipality.municipality }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Position:</span> {{ fieldOfficer.position }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Cluster:</span> {{ fieldOfficer.cluster }}
                    </p>
                </div>

                <div class="mt-4 flex gap-2">
                    <button @click="openModal(fieldOfficer)" class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>
                    <button @click="openDeleteModal(fieldOfficer)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
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

        <div v-if="pagination.last_page > 1" class="flex justify-center mt-2">
            <select
                v-model="pagination.current_page"
                @change="goToPage(pagination.path + '?page=' + pagination.current_page)"
                class="px-2 py-1 text-sm border border-gray-300 bg-white focus:outline-none focus:border-gray-400 w-auto pr-7"
            >
                <option
                    v-for="page in pageOptions"
                    :key="page.value"
                    :value="page.value"
                >
                    Page {{ page.label }}
                </option>
            </select>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center px-2 py-4">
            <div class="bg-white p-2 lg:p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit Field Officer" : "Add Field Officer" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Municipality</label>
                <select v-model="form.municipality_id" class="border p-2 w-full my-2">
                    <option value="">Select Municipality</option>
                    <option v-for="municipality in pageProps.municipalities" :key="municipality.id" :value="municipality.id">
                        {{ municipality.municipality }}
                    </option>
                </select>

                <label class="font-bold block text-gray-700">First Name</label>
                <input v-model="form.fname" placeholder="Enter first name" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Middle Initial</label>
                <input v-model="form.mid_initial" placeholder="Enter middle initial" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Last Name</label>
                <input v-model="form.lname" placeholder="Enter last name" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Position</label>
                <input v-model="form.position" placeholder="Enter position" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Cluster</label>
                <input v-model="form.cluster" placeholder="Enter cluster" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Profile Image</label>
                <input type="file" @change="form.profile_img = $event.target.files[0]" class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitFieldOfficer" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
                        Cancel
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isDeleteModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center px-2 py-4">
            <div class="bg-white p-2 lg:p-6 rounded shadow-lg w-full max-w-lg">
                <h2 class="text-xl mb-4 text-center">Are you sure?</h2>
                <p class="text-center mb-4">
                    Do you really want to delete
                    <strong>{{ fieldOfficerToDelete?.fname }} {{ fieldOfficerToDelete?.lname }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteFieldOfficer" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
