<script setup>
import { ref, watch } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.news);
const newsList = ref(pageProps.news.data ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingNews = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const newsToDelete = ref(null);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    status: pageProps.filters?.status ?? "",
});

watch(
    () => filters.value.search,
    debounce(() => {
        applyFilters();
    }, 500)
);

const fetchNews = (url = "/adminNews") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["news", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.news;
            newsList.value = props.news.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const applyFilters = () => fetchNews();
const goToPage = (url) => url && fetchNews(url);


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
    if (!isEditMode.value && form.images.length === 0)
        return (errorMessage.value = "Please upload at least one image.");
    if (form.images.length > 5)
        return (errorMessage.value = "You can upload a maximum of 5 images.");
    if (form.images.some((image) => image.size > 5 * 1024 * 1024))
        return (errorMessage.value = "Each image must not exceed 5MB.");

    const formData = new FormData();
    formData.append("title", title);
    formData.append("caption", caption);

    if (form.images.length) {
    form.images.forEach((image) => formData.append("images[]", image));
} else if (isEditMode.value && editingNews.value.images) {
    formData.append("existing_images", JSON.stringify(editingNews.value.images));
}

    const onSuccess = (page) => {
        pagination.value = page.props.news;
        newsList.value = [...page.props.news.data]; // Ensure a fresh list

        showSuccessMessage(isEditMode.value ? "News updated successfully!" : "News added successfully!");
        closeModal();
    };

    const onError = (errors) => {
        errorMessage.value =
            errors.title?.[0] ||
            errors.caption?.[0] ||
            errors.images?.[0] ||
            "An error occurred.";
    };

    router.post(isEditMode.value ? `/news/${form.id}` : "/news", formData, {
        preserveScroll: true,
        preserveState: true,
        headers: { "Content-Type": "multipart/form-data" },
        onSuccess,
        onError,
    });
};

const openDeleteModal = (news) => {
    newsToDelete.value = news;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    newsToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteNews = async () => {
    if (!newsToDelete.value) return;

    try {
        await form.delete(`/news/${newsToDelete.value.id}`);
        newsList.value = newsList.value.filter((n) => n.id !== newsToDelete.value.id);
        showSuccessMessage("News deleted successfully!");
        closeDeleteModal();

        if (newsList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/adminNews?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete news.";
    }
};


const toggleStatus = async (id) => {
    try {
        await router.patch(`/news/${id}/toggle-status`, {}, { preserveState: true, preserveScroll: true });
        const newsItem = newsList.value.find((n) => n.id === id);
        if (newsItem) newsItem.status = !newsItem.status;
        showSuccessMessage("Status updated successfully!");
    } catch (error) {
        errorMessage.value = "Failed to update status.";
    }
};

</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 dark:text-white border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            NEWS & UPDATES
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
                Add News
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="applyFilters" type="text"
                    placeholder="Search news..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <button @click="applyFilters"
                    class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded hover:bg-gray-100">
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
                        <td class="p-3 text-gray-700 font-bold truncate">
                            {{ news.user.name }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center flex-wrap gap-1 max-w-[160px]">
                                <img v-for="(image, index) in news.images.slice(
                                    0,
                                    5
                                )" :key="index" :src="`/storage/${image}`" alt="News Image"
                                    class="w-12 h-12 object-cover border border-gray-300" />
                            </div>
                        </td>
                        <td class="p-3 text-center">
                            <button @click="toggleStatus(news.id)" :class="news.status
                                ? 'bg-green-500'
                                : 'bg-orange-400'
                                " class="px-3 py-1 text-white rounded text-sm transition">
                                {{ news.status ? "Approved" : "Pending" }}
                            </button>
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(news)"
                                    class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
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
                        <p class="text-sm text-gray-600 line-clamp-4" :title="news.caption">
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
                        class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> Edit
                    </button>
                    <button @click="openDeleteModal(news)"
                        class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-trash"></i> Delete
                    </button>
                </div>
            </div>
        </div>

        <div class="flex justify-center mt-4 space-x-2">
            <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                v-html="link.label" :class="{
                    'font-bold bg-blue-300': link.active,
                    'text-gray-500': !link.url,
                }" class="px-3 py-1 border rounded cursor-pointer" :disabled="!link.url"></button>
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
                <p class="text-sm text-gray-500">
                    Max 5 images, each up to 5MB
                </p>
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
