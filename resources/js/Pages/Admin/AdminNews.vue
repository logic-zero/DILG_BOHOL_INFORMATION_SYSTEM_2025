<script setup>
import { ref } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";

defineOptions({
    layout: AuthenticatedLayout,
});

const newsList = ref(usePage().props.news ?? []);
const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingNews = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const newsToDelete = ref(null);

const filters = ref({
    search: usePage().props.filters?.search ?? "",
    status: usePage().props.filters?.status ?? "",
});

const applyFilters = () => {
    router.get('/adminNews', filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ['news', 'filters'],
        onSuccess: (page) => {
            newsList.value = page.props.news;
        }
    });
};

const resetSearch = () => {
    if (filters.value.search === "") {
        applyFilters();
    }
};


const form = useForm({
    id: null,
    title: "",
    caption: "",
    images: [],
});

const openModal = (news = null) => {
    isEditMode.value = !!news;
    editingNews.value = news;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (news) {
        form.id = news.id;
        form.title = news.title;
        form.caption = news.caption;
        form.images = [];
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

const submitNews = () => {
    errorMessage.value = "";

    const title = form.title.trim();
    const caption = form.caption.trim();

    if (!title) return (errorMessage.value = "Title is required.");
    if (!caption) return (errorMessage.value = "Caption is required.");
    if (!isEditMode.value && form.images.length === 0) {
        return (errorMessage.value = "Please upload at least one image.");
    }
    if (form.images.length > 5) {
        return (errorMessage.value = "You can upload a maximum of 5 images.");
    }
    if (form.images.some(image => image.size > 5 * 1024 * 1024)) {
        return (errorMessage.value = "Each image must not exceed 5MB.");
    }

    const formData = new FormData();
    formData.append("title", title);
    formData.append("caption", caption);

    if (isEditMode.value) {
        if (form.images.length === 0 && editingNews.value.images) {
            formData.append("existing_images", JSON.stringify(editingNews.value.images));
        } else {
            form.images.forEach(image => formData.append("images[]", image));
        }
    } else {
        form.images.forEach(image => formData.append("images[]", image));
    }

    const onSuccess = (response) => {
        if (isEditMode.value) {
            const index = newsList.value.findIndex(n => n.id === form.id);
            if (index !== -1) {
                newsList.value[index] = response.props.news.find(n => n.id === form.id);
            }
        } else {
            newsList.value.unshift(response.props.news[0]);
        }

        closeModal();
        showSuccessMessage(isEditMode.value ? "News updated successfully!" : "News added successfully!");
    };

    const onError = (errors) => {
        errorMessage.value = errors.title?.[0] || errors.caption?.[0] || errors.images?.[0] || "An error occurred.";
    };

    if (isEditMode.value) {
        router.post(`/news/${form.id}`, formData, {
            preserveScroll: true,
            preserveState: true,
            headers: { "Content-Type": "multipart/form-data" },
            onSuccess,
            onError,
        });
    } else {
        router.post("/news", formData, {
            preserveScroll: true,
            preserveState: true,
            headers: { "Content-Type": "multipart/form-data" },
            onSuccess,
            onError,
        });
    }
};

const openDeleteModal = (news) => {
    newsToDelete.value = news;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    newsToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteNews = () => {
    if (!newsToDelete.value) return;

    form.delete(`/news/${newsToDelete.value.id}`, {
        onSuccess: () => {
            newsList.value = usePage().props.news;
            showSuccessMessage("News deleted successfully!");
            closeDeleteModal();
        },
    });
};

const toggleStatus = (id) => {
    form.patch(`/news/${id}/toggle-status`, {
        onSuccess: () => {
            newsList.value = usePage().props.news;
            showSuccessMessage("Status updated successfully!");
        },
    });
};
</script>

<template>
    <div class="p-4">
        <transition name="fade">
            <div v-if="showSuccess"
                class="fixed top-20 right-5 bg-green-500 text-white p-3 rounded shadow-lg z-50 flex items-center gap-2">
                <i class="fas fa-check-circle text-white text-lg"></i>
                <span>{{ successMessage }}</span>
            </div>
        </transition>

        <div class="flex flex-col md:flex-row md:items-center gap-2 mb-4">
            <button @click="openModal()"
                class="bg-teal-500 text-white px-4 py-2 rounded flex items-center gap-2 hover:bg-teal-600 w-full md:w-auto">
                <i class="fas fa-plus-circle"></i>
                Add News
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" @input="resetSearch" type="text"
                    placeholder="Search news..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <button @click="applyFilters"
                    class="absolute right-1 top-1/2 transform -translate-y-1/2 bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600">
                    <i class="fas fa-search"></i>
                </button>
            </div>

            <select v-model="filters.status" @change="applyFilters" class="border p-2 rounded w-full md:w-52">
                <option value="">All</option>
                <option value="approved">Approved</option>
                <option value="pending">Pending</option>
            </select>
        </div>


        <div class="overflow-x-auto hidden md:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[25%]">Title</th>
                        <th class="p-3 text-left w-[35%]">Caption</th>
                        <th class="p-3 text-left w-[10%]">Author</th>
                        <th class="p-3 text-center w-[15%]">Images</th>
                        <th class="p-3 text-center w-[10%]">Status</th>
                        <th class="p-3 text-center w-[5%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="news in newsList" :key="news.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-900 font-medium break-words">
                            <div class="line-clamp-3">{{ news.title }}</div>
                        </td>
                        <td class="p-3 text-gray-600 break-words" :title="news.caption">
                            <div class="line-clamp-3">{{ news.caption }}</div>
                        </td>
                        <td class="p-3 text-gray-700 font-bold truncate">{{ news.user.name }}</td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center flex-wrap gap-1 max-w-[160px]">
                                <img v-for="(image, index) in news.images.slice(0, 5)" :key="index"
                                    :src="`/storage/${image}`" alt="News Image"
                                    class="w-12 h-12 object-cover border border-gray-300" />
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            <button @click="toggleStatus(news.id)"
                                :class="news.status ? 'bg-green-500' : 'bg-orange-400'"
                                class="px-3 py-1 text-white rounded text-sm transition">
                                {{ news.status ? "Approved" : "Pending" }}
                            </button>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(news)"
                                    class="bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded text-sm transition">
                                    Edit
                                </button>
                                <button @click="openDeleteModal(news)"
                                    class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>


        <div class="block md:hidden space-y-4">
            <div v-for="news in newsList" :key="news.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-between items-start flex-wrap gap-2">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-semibold text-gray-900">
                            {{ news.title }}
                        </h2>
                        <p class="text-sm text-gray-600 truncate" :title="news.caption">
                            {{ news.caption }}
                        </p>
                        <p class="text-xs text-gray-500 mt-1">
                            By: {{ news.user.name }}
                        </p>
                    </div>
                    <button @click="toggleStatus(news.id)" :class="news.status
                        ? 'bg-green-500 text-white'
                        : 'bg-orange-400 text-white'
                        " class="px-3 py-1 rounded text-xs whitespace-nowrap">
                        <i :class="news.status
                            ? 'fas fa-check-circle'
                            : 'fas fa-hourglass'
                            "></i>
                        {{ news.status ? "Approved" : "Pending" }}
                    </button>
                </div>

                <div v-if="news.images.length" class="flex flex-wrap gap-2 mt-3">
                    <img v-for="(image, index) in news.images" :key="index" :src="`/storage/${image}`" alt="News Image"
                        class="w-16 h-16 object-cover border border-gray-300" />
                </div>

                <div class="mt-3 flex gap-2">
                    <button @click="openModal(news)"
                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button @click="openDeleteModal(news)"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4">
                <h2 class="text-xl mb-4">
                    {{ isEditMode ? "Edit News" : "Add News" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="block text-gray-700">Title</label>
                <input v-model="form.title" placeholder="Enter title" class="border p-2 w-full my-2" />

                <label class="block text-gray-700">Caption</label>
                <textarea v-model="form.caption" placeholder="Enter caption" class="border p-2 w-full my-2"></textarea>

                <label class="block text-gray-700">Upload Images</label>
                <p class="text-sm text-gray-500">Max 5 images, each up to 5MB</p>
                <input type="file" multiple @change="form.images = [...$event.target.files]"
                    class="border p-2 w-full my-2" />

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitNews"
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
                    <strong>{{ newsToDelete?.title }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteNews" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
