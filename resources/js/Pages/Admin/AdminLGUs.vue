<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.lgus);
const lguList = ref(pageProps.lgus.data ?? []);
const municipalities = ref(pageProps.municipalities ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingLgu = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const lguToDelete = ref(null);

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
        fetchLgus();
    }, 500)
);

const fetchLgus = (url = "/admin/lgus") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["lgus", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.lgus;
            lguList.value = props.lgus.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const goToPage = (url) => url && fetchLgus(url);

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
    municipality_id: "",
    mayor: "",
    vice_mayor: "",
    sb_members: Array(10).fill(""),
    lb_pres: "",
    psk_pres: "",
});

const openModal = (lgu = null) => {
    isEditMode.value = !!lgu;
    editingLgu.value = lgu;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (lgu) {
        form.id = lgu.id;
        form.municipality_id = lgu.municipality_id;
        form.mayor = lgu.mayor;
        form.vice_mayor = lgu.vice_mayor;
        form.sb_members = [
            lgu.sb_member1,
            lgu.sb_member2,
            lgu.sb_member3,
            lgu.sb_member4,
            lgu.sb_member5,
            lgu.sb_member6,
            lgu.sb_member7,
            lgu.sb_member8,
            lgu.sb_member9,
            lgu.sb_member10,
        ];
        form.lb_pres = lgu.lb_pres;
        form.psk_pres = lgu.psk_pres;
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

const submitLgu = () => {
    errorMessage.value = "";

    if (!form.municipality_id)
        return (errorMessage.value = "Municipality is required.");
    if (!form.mayor) return (errorMessage.value = "Mayor is required.");
    if (!form.vice_mayor) return (errorMessage.value = "Vice Mayor is required.");

    const url = isEditMode.value ? `/admin/lgus/${form.id}` : "/admin/lgus";

    const formattedData = {
        municipality_id: form.municipality_id,
        mayor: form.mayor,
        vice_mayor: form.vice_mayor,
        sb_member1: form.sb_members[0] || "",
        sb_member2: form.sb_members[1] || "",
        sb_member3: form.sb_members[2] || "",
        sb_member4: form.sb_members[3] || "",
        sb_member5: form.sb_members[4] || "",
        sb_member6: form.sb_members[5] || "",
        sb_member7: form.sb_members[6] || "",
        sb_member8: form.sb_members[7] || "",
        sb_member9: form.sb_members[8] || "",
        sb_member10: form.sb_members[9] || "",
        lb_pres: form.lb_pres || "",
        psk_pres: form.psk_pres || "",
    };

    router.post(url, formattedData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.lgus;
            lguList.value = [...props.lgus.data];
            showSuccessMessage(
                isEditMode.value
                    ? "LGU updated successfully!"
                    : "LGU added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (lgu) => {
    lguToDelete.value = lgu;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    lguToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteLgu = async () => {
    if (!lguToDelete.value) return;

    try {
        await form.delete(`/admin/lgus/${lguToDelete.value.id}`);
        lguList.value = lguList.value.filter((l) => l.id !== lguToDelete.value.id);
        showSuccessMessage("LGU deleted successfully!");
        closeDeleteModal();

        if (lguList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/lgus?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete LGU.";
    }
};
</script>

<template>
    <div class="p-4">
        <h1
            class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            LGUs
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
                Add LGU
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search LGU..."
                    class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[25%]">Municipality</th>
                        <th class="p-3 text-left w-[25%]">Mayor</th>
                        <th class="p-3 text-left w-[25%]">Vice Mayor</th>
                        <th class="p-3 text-center w-[25%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="lgu in lguList" :key="lgu.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ lgu.municipality?.municipality || "Unknown Municipality" }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ lgu.mayor }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ lgu.vice_mayor }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(lgu)"
                                    class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(lgu)"
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
            <div v-for="lgu in lguList" :key="lgu.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-between items-start flex-wrap gap-2">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-extrabold mb-2 text-gray-900">
                            {{ lgu.municipality?.municipality || "Unknown Municipality" }}
                        </h2>
                        <p class="text-sm text-gray-600">
                            <span class="font-bold">Mayor:</span> {{ lgu.mayor }}
                        </p>
                        <p class="text-sm text-gray-600">
                            <span class="font-bold">Vice Mayor:</span> {{ lgu.vice_mayor }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex gap-2">
                    <button @click="openModal(lgu)"
                        class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>
                    <button @click="openDeleteModal(lgu)"
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
                    {{ isEditMode ? "Edit LGU" : "Add LGU" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Municipality</label>
                <select v-model="form.municipality_id" class="border p-2 w-full my-2">
                    <option value="" disabled>Select Municipality</option>
                    <option v-for="municipality in municipalities" :key="municipality.id" :value="municipality.id">
                        {{ municipality.municipality || "Unknown Municipality" }}
                    </option>
                </select>

                <label class="font-bold block text-gray-700">Mayor</label>
                <input v-model="form.mayor" placeholder="Enter Mayor" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Vice Mayor</label>
                <input v-model="form.vice_mayor" placeholder="Enter Vice Mayor" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">SB Members</label>
                <div class="grid grid-cols-2 gap-2">
                    <input v-for="(member, index) in form.sb_members" :key="index" v-model="form.sb_members[index]"
                        placeholder="SB Member" class="border p-2 w-full my-1" />
                </div>

                <label class="font-bold block text-gray-700">LB President</label>
                <input v-model="form.lb_pres" placeholder="Enter LB President" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">PSK President</label>
                <input v-model="form.psk_pres" placeholder="Enter PSK President" class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitLgu"
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
                    <strong>{{ lguToDelete?.municipality?.municipality }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteLgu" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
