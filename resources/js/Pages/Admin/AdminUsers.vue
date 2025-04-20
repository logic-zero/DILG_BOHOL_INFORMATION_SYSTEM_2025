<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.users);
const usersList = ref(pageProps.users.data ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingUser = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const userToDelete = ref(null);

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

const fetchUsers = (url = "/admin/users") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["users", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.users;
            usersList.value = props.users.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const applyFilters = () => fetchUsers();
const goToPage = (url) => url && fetchUsers(url);

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
    name: "",
    email: "",
    position: "",
    password: "",
    password_confirmation: "",
    role: "",
});

const openModal = (user = null) => {
    isEditMode.value = !!user;
    editingUser.value = user;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (user) {
        form.id = user.id;
        form.name = user.name;
        form.email = user.email;
        form.position = user.position;
        form.role = user.roles[0]?.id;
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

const submitUser = () => {
    errorMessage.value = "";

    if (!form.name) return (errorMessage.value = "Name is required.");
    if (!form.email) return (errorMessage.value = "Email is required.");
    if (!form.position) return (errorMessage.value = "Position is required.");
    if (!form.role) return (errorMessage.value = "Role is required.");

    const url = isEditMode.value
        ? `/admin/users/${form.id}`
        : "/admin/users";

    const formattedData = new FormData();
    formattedData.append("name", form.name);
    formattedData.append("email", form.email);
    formattedData.append("position", form.position);
    formattedData.append("role", form.role);

    if (form.password) formattedData.append("password", form.password);
    if (form.password_confirmation) formattedData.append("password_confirmation", form.password_confirmation);

    router.post(url, formattedData, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.users;
            usersList.value = props.users.data;
            showSuccessMessage(
                isEditMode.value
                    ? "User updated successfully!"
                    : "User created successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value = Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (user) => {
    userToDelete.value = user;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    userToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteUser = async () => {
    if (!userToDelete.value) return;

    try {
        const response = await form.delete(`/admin/users/${userToDelete.value.id}`, {
            preserveScroll: true,
            preserveState: true,
        });

        if (userToDelete.value.id !== pageProps.auth.user.id) {
            usersList.value = usersList.value.filter(
                (u) => u.id !== userToDelete.value.id
            );
            showSuccessMessage("User deleted successfully!");

            if (usersList.value.length === 0 && pagination.value.current_page > 1) {
                goToPage(`/admin/users?page=${pagination.value.current_page - 1}`);
            }
        } else {
            showSuccessMessage("Self-deletion is not allowed. To delete this account, another admin must log in and remove it.");
        }

        closeDeleteModal();
    } catch (error) {
        errorMessage.value = error.response?.data?.message || "Failed to delete user.";
    }
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            USERS
        </h1>
        <transition name="fade">
            <div v-if="showSuccess"
                 class="fixed top-20 right-5 text-white p-3 rounded shadow-lg z-50 flex items-center gap-2"
                 :class="{
                     'bg-green-500': !successMessage.includes('Self-deletion'),
                     'bg-orange-500': successMessage.includes('Self-deletion')
                 }">
                <i class="fas fa-check-circle text-white text-lg"></i>
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
            <button @click="openModal()" class="bg-blue-800 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-blue-900 w-full md:w-auto">
                <i class="fas fa-plus-circle"></i>
                Add User
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search users..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[15%]">Profile Image</th>
                        <th class="p-3 text-left w-[20%]">Name</th>
                        <th class="p-3 text-left w-[20%]">Email</th>
                        <th class="p-3 text-left w-[10%]">Position</th>
                        <th class="p-3 text-left w-[15%]">Role</th>
                        <th class="p-3 text-center w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="user in usersList" :key="user.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words flex justify-center">
                            <img v-if="user.profile_image" loading="lazy" :src="`/profile_images/${user.profile_image}`" class="w-20 h-20 rounded-full object-cover" alt="Profile Image" />
                            <span v-else>No Image</span>
                        </td>
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ user.name }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ user.email }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ user.position }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ user.roles[0]?.name }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(user)" class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(user)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="user in usersList" :key="user.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-center mb-4">
                    <div class="w-32 h-32 rounded-full overflow-hidden border-4 border-white shadow-lg">
                        <img v-if="user.profile_image" loading="lazy" :src="`/profile_images/${user.profile_image}`" class="w-full h-full object-cover" alt="Profile Image" />
                        <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600">
                            No Image
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <h2 class="text-xl font-extrabold text-gray-900 mb-2">
                        {{ user.name }}
                    </h2>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Email:</span> {{ user.email }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Position:</span> {{ user.position }}
                    </p>
                    <p class="text-sm text-gray-600">
                        <span class="font-bold">Role:</span> {{ user.roles[0]?.name }}
                    </p>
                </div>

                <div class="mt-4 flex gap-2">
                    <button @click="openModal(user)" class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>
                    <button @click="openDeleteModal(user)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
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
                    }" class="px-2 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url"
                ></button>
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
                    {{ isEditMode ? "Edit User" : "Add User" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Full Name</label>
                <input v-model="form.name" placeholder="Enter full name" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Email</label>
                <input v-model="form.email" type="email" placeholder="Enter email" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Position</label>
                <input v-model="form.position" placeholder="Enter position" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Role</label>
                <select v-model="form.role" class="border p-2 w-full my-2">
                    <option value="">Select Role</option>
                    <option v-for="role in pageProps.roles" :key="role.id" :value="role.id">
                        {{ role.name }}
                    </option>
                </select>

                <label class="font-bold block text-gray-700">Password</label>
                <input v-model="form.password" type="password" placeholder="Enter password" class="border p-2 w-full my-2" />

                <label class="font-bold block text-gray-700">Confirm Password</label>
                <input v-model="form.password_confirmation" type="password" placeholder="Confirm password" class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitUser" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
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
                    <strong>{{ userToDelete?.name }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteUser" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
