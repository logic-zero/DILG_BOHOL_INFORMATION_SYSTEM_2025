<script setup>
import { ref, watch, computed } from "vue";
import { useForm, usePage, router } from "@inertiajs/vue3";
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { debounce } from "lodash";

defineOptions({
    layout: AuthenticatedLayout,
});

const pageProps = usePage().props;
const pagination = ref(pageProps.faqs);
const faqsList = ref(pageProps.faqs.data ?? []);
const programs = ref(pageProps.programs ?? []);
const outcomeAreas = ref(pageProps.outcomeAreas ?? []);

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingFaq = ref(null);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const isDeleteModalOpen = ref(false);
const faqToDelete = ref(null);

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
        fetchFaqs();
    }, 500)
);

const fetchFaqs = (url = "/admin/faqs") => {
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["faqs", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.faqs;
            faqsList.value = props.faqs.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const goToPage = (url) => url && fetchFaqs(url);

const form = useForm({
    id: null,
    outcome_area: "",
    program: "",
    questions: "",
    answers: "",
});

const openModal = (faq = null) => {
    isEditMode.value = !!faq;
    editingFaq.value = faq;
    isModalOpen.value = true;
    errorMessage.value = "";

    if (faq) {
        form.id = faq.id;
        form.outcome_area = faq.outcome_area;
        form.program = faq.program;
        form.questions = faq.questions;
        form.answers = faq.answers;
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

const submitFaq = () => {
    errorMessage.value = "";

    if (!form.questions) return (errorMessage.value = "Questions are required.");
    if (!form.answers) return (errorMessage.value = "Answers are required.");
    if (!form.program) return (errorMessage.value = "Program is required.");
    if (!form.outcome_area) return (errorMessage.value = "Outcome Area is required.");

    const url = isEditMode.value ? `/admin/faqs/${form.id}` : "/admin/faqs";

    router.post(url, form, {
        preserveScroll: true,
        preserveState: true,
        onSuccess: ({ props }) => {
            pagination.value = props.faqs;
            faqsList.value = [...props.faqs.data];
            showSuccessMessage(
                isEditMode.value
                    ? "FAQ updated successfully!"
                    : "FAQ added successfully!"
            );
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value =
                Object.values(errors).flat().join(", ") || "An error occurred.";
        },
    });
};

const openDeleteModal = (faq) => {
    faqToDelete.value = faq;
    isDeleteModalOpen.value = true;
};

const closeDeleteModal = () => {
    faqToDelete.value = null;
    isDeleteModalOpen.value = false;
};

const deleteFaq = async () => {
    if (!faqToDelete.value) return;

    try {
        await form.delete(`/admin/faqs/${faqToDelete.value.id}`);
        faqsList.value = faqsList.value.filter((f) => f.id !== faqToDelete.value.id);
        showSuccessMessage("FAQ deleted successfully!");
        closeDeleteModal();

        if (faqsList.value.length === 0 && pagination.value.current_page > 1) {
            goToPage(`/admin/faqs?page=${pagination.value.current_page - 1}`);
        }
    } catch (error) {
        errorMessage.value = "Failed to delete FAQ.";
    }
};

const selectProgram = (program) => {
    form.program = program;
    resetSelect('programSelect');
};

const selectOutcomeArea = (outcomeArea) => {
    form.outcome_area = outcomeArea;
    resetSelect('outcomeAreaSelect');
};

const resetSelect = (selectId) => {
    const selectElement = document.getElementById(selectId);
    if (selectElement) {
        selectElement.value = "";
    }
};
</script>

<template>
    <div class="p-4">
        <h1 class="text-3xl md:text-4xl mb-5 font-bold text-gray-800 dark:text-white border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
            FAQs Management
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
                Add FAQ
            </button>

            <div class="relative flex-1 w-full md:w-auto">
                <input v-model="filters.search" @keyup.enter="fetchFaqs" type="text" placeholder="Search FAQs..." class="border p-2 pl-4 pr-12 rounded w-full" />
                <div class="absolute right-1 top-1/2 transform -translate-y-1/2 text-gray-700 px-3 py-1 rounded">
                    <i class="fas fa-search"></i>
                </div>
            </div>
        </div>

        <div class="overflow-x-auto hidden xl:block">
            <table class="w-full border-collapse">
                <thead>
                    <tr class="bg-gray-200 text-gray-700 text-sm uppercase tracking-wider">
                        <th class="p-3 text-left w-[20%]">Outcome Area</th>
                        <th class="p-3 text-left w-[20%]">Program</th>
                        <th class="p-3 text-left w-[20%]">Questions</th>
                        <th class="p-3 text-left w-[20%]">Answers</th>
                        <th class="p-3 text-center w-[20%]">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr v-for="faq in faqsList" :key="faq.id" class="border-b hover:bg-gray-50 transition">
                        <td class="p-3 text-gray-600 break-words">
                            {{ faq.outcome_area }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ faq.program }}
                        </td>
                        <td class="p-3 text-gray-900 font-extrabold break-words">
                            {{ faq.questions }}
                        </td>
                        <td class="p-3 text-gray-600 break-words">
                            {{ faq.answers }}
                        </td>
                        <td class="p-3 text-center">
                            <div class="flex justify-center gap-1">
                                <button @click="openModal(faq)" class="bg-blue-800 hover:bg-blue-900 text-white px-3 py-1 rounded text-sm transition">
                                    View | Edit
                                </button>
                                <button @click="openDeleteModal(faq)" class="bg-red-600 hover:bg-red-700 text-white px-3 py-1 rounded text-sm transition">
                                    Delete
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="block xl:hidden space-y-4">
            <div v-for="faq in faqsList" :key="faq.id" class="border rounded-lg shadow-md bg-gray-100 p-4">
                <div class="flex justify-between items-start flex-wrap gap-2">
                    <div class="flex-1 min-w-0">
                        <h2 class="text-lg font-extrabold mb-4 text-gray-900">
                            {{ faq.questions }}
                        </h2>

                        <p class="text-sm text-gray-600 mb-4">
                            <span class="font-bold">Answers:</span> {{ faq.answers }}
                        </p>

                        <p class="text-sm text-gray-600">
                            <span class="font-bold">Program:</span> {{ faq.program }}
                        </p>

                        <p class="text-sm text-gray-600">
                            <span class="font-bold">Outcome Area:</span> {{ faq.outcome_area }}
                        </p>
                    </div>
                </div>

                <div class="mt-3 flex gap-2">
                    <button @click="openModal(faq)" class="flex-1 bg-blue-800 hover:bg-blue-900 text-white text-sm px-3 py-2 rounded transition">
                        <i class="fas fa-edit"></i> View | Edit
                    </button>

                    <button @click="openDeleteModal(faq)" class="flex-1 bg-red-600 hover:bg-red-700 text-white text-sm px-3 py-2 rounded transition">
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

        <div v-if="isModalOpen" class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4">
            <div class="bg-white p-6 shadow-lg w-full max-w-lg mx-4 max-h-[95vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    {{ isEditMode ? "Edit FAQ" : "Add FAQ" }}
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Questions</label>
                <textarea v-model="form.questions" placeholder="Enter Questions" class="border p-2 w-full my-2 h-24"></textarea>

                <label class="font-bold block text-gray-700">Answers</label>
                <textarea v-model="form.answers" placeholder="Enter Answers" class="border p-2 w-full my-2 h-64"></textarea>

                <label class="font-bold block text-gray-700">Program</label>
                <input v-model="form.program" placeholder="Enter New Program" class="border p-2 w-full my-2" />
                <select id="programSelect" @change="selectProgram($event.target.value)" class="border p-2 w-full my-2">
                    <option value="">Select Existing Program</option>
                    <option v-for="program in programs" :key="program" :value="program">
                        {{ program }}
                    </option>
                </select>

                <label class="font-bold block text-gray-700">Outcome Area</label>
                <input v-model="form.outcome_area" placeholder="Enter New Outcome Area" class="border p-2 w-full my-2" />
                <select id="outcomeAreaSelect" @change="selectOutcomeArea($event.target.value)" class="border p-2 w-full my-2">
                    <option value="">Select Existing Outcome Area</option>
                    <option v-for="outcomeArea in outcomeAreas" :key="outcomeArea" :value="outcomeArea">
                        {{ outcomeArea }}
                    </option>
                </select>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitFaq" class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1">
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
                    <strong>{{ faqToDelete?.outcome_area }}</strong>?
                </p>

                <div class="flex justify-center gap-2">
                    <button @click="deleteFaq" class="bg-red-500 text-white px-2 py-1 rounded hover:bg-red-600">
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
