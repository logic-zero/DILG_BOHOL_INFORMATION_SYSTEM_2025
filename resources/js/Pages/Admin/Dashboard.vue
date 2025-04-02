<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm, router } from "@inertiajs/vue3";
import TotalCountCard from "@/Components/TotalCountCard.vue";
import PageVisitCard from "@/Components/PageVisitCard.vue";
import PageVisitsChart from "@/Components/PageVisitsChart.vue";
import { ref, computed } from "vue";

defineOptions({ layout: AuthenticatedLayout });

const props = defineProps({
    newsStats: Object,
    organizational_structure: Number,
    pdmu: Number,
    field_officers: Number,
    lgu: Number,
    faq: Number,
    issuances: Number,
    prov_officials: Number,
    citizens_charter: Number,
    users: Number,
    pageVisits: Object,
    knowledge_materials: Number,
    jobs: Number,
});

const isModalOpen = ref(false);
const errorMessage = ref("");
const showSuccess = ref(false);
const successMessage = ref("");
const showImagePreview = ref(false);

const form = useForm({
    images: [],
});

const previewImages = computed(() => {
    return form.images.length > 0
        ? Array.from(form.images).map(image => URL.createObjectURL(image))
        : [];
});

const openModal = () => {
    isModalOpen.value = true;
    errorMessage.value = "";
    showImagePreview.value = false;
    form.reset();
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const showSuccessMessage = (message) => {
    successMessage.value = message;
    showSuccess.value = true;
    setTimeout(() => {
        showSuccess.value = false;
    }, 3000);
};

const submitHomeImages = () => {
    errorMessage.value = "";

    if (form.images.length === 0) {
        return (errorMessage.value = "Please upload at least one image.");
    }
    if (form.images.length > 3) {
        return (errorMessage.value = "You can upload a maximum of 3 images.");
    }
    if (form.images.some((image) => image.size > 5 * 1024 * 1024)) {
        return (errorMessage.value = "Each image must not exceed 5MB.");
    }

    const formData = new FormData();
    form.images.forEach((image) => formData.append("images[]", image));

    router.post('/home-images', formData, {
        preserveScroll: true,
        preserveState: true,
        headers: { "Content-Type": "multipart/form-data" },
        onSuccess: () => {
            showSuccessMessage("Home images updated successfully!");
            closeModal();
        },
        onError: (errors) => {
            errorMessage.value = errors.images?.[0] || "An error occurred.";
        }
    });
};
</script>

<template>

    <Head title="Dashboard" />

    <transition name="fade">
        <div v-if="showSuccess"
            class="fixed top-20 right-5 bg-green-500 text-white p-3 rounded shadow-lg z-50 flex items-center gap-2">
            <i class="fas fa-check-circle text-white text-lg"></i>
            <span>{{ successMessage }}</span>
        </div>
    </transition>

    <h1
        class="text-3xl md:text-4xl mt-4 mb-2 font-bold text-gray-800 border-b-2 border-gray-500 pb-2 text-center mx-auto w-fit">
        DASHBOARD
    </h1>

    <div class="px-4">
        <button @click="openModal"
            class="bg-blue-800 text-white px-2 py-1 text-sm rounded flex items-center gap-2 hover:bg-blue-900">
            <i class="fas fa-image"></i>
            Update Home Images
        </button>
    </div>

    <div class="mx-auto px-4 py-4 flex flex-col xl:flex-row gap-4">
        <!-- page visit section -->
        <div class="xl:max-w-[500px] xl:min-w-[400px] flex-1 min-w-0">
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                <PageVisitCard title="Today's Visits" :count="pageVisits.today" icon="fas fa-calendar-day"
                    bg-color="bg-white" border-color="border-blue-900 border" text-color="text-blue-950" />

                <PageVisitCard title="This Week" :count="pageVisits.thisWeek" icon="fas fa-calendar-week"
                    bg-color="bg-white" border-color="border-blue-900 border" text-color="text-blue-950" />

                <PageVisitCard title="This Month" :count="pageVisits.thisMonth" icon="fas fa-calendar-alt"
                    bg-color="bg-white" border-color="border-blue-900 border" text-color="text-blue-950" />

                <PageVisitCard title="Total Visits" :count="pageVisits.total" icon="fas fa-chart-line"
                    bg-color="bg-white" border-color="border-blue-900 border" text-color="text-blue-950" />
            </div>

            <div class="bg-white flex flex-col p-4 rounded-lg border shadow-sm border-blue-900 xl:min-h-[330px]">
                <h3 class="text-sm text-blue-950 uppercase font-semibold mb-2">Page Visits (Last 10 Days)</h3>
                <PageVisitsChart :labels="pageVisits.graph.labels" :data="pageVisits.graph.data" class="mt-auto"/>
            </div>
        </div>

        <!-- other cards section-->
        <div class="flex-1">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4 justify-items-center">
                <!-- Row 1 -->
                <TotalCountCard title="Total News" :count="newsStats.total" icon="fas fa-newspaper" color="indigo" />
                <TotalCountCard title="Approved News" :count="newsStats.approved" icon="fas fa-check-circle"
                    color="green" />
                <TotalCountCard title="Pending News" :count="newsStats.pending" icon="fas fa-clock" color="yellow" />

                <!-- Row 2 -->
                <TotalCountCard title="Organizational Structure" :count="organizational_structure" icon="fas fa-sitemap"
                    color="fuchsia" />
                <TotalCountCard title="PDMUs" :count="pdmu" icon="fas fa-user-friends" color="pink" />
                <TotalCountCard title="Field Officers" :count="field_officers" icon="fas fa-users" color="violet" />

                <!-- Row 3 -->
                <TotalCountCard title="LGUs" :count="lgu" icon="fas fa-city" color="red" />
                <TotalCountCard title="FAQs" :count="faq" icon="fas fa-question-circle" color="orange" />
                <TotalCountCard title="Issuances" :count="issuances" icon="fas fa-file-alt" color="amber" />

                <!-- Row 4 -->
                <TotalCountCard title="Provincial Officials" :count="prov_officials" icon="fas fa-user-tie"
                    color="lime" />
                <TotalCountCard title="Citizens Charter" :count="citizens_charter" icon="fas fa-file-signature"
                    color="emerald" />
                <TotalCountCard title="Users" :count="users" icon="fas fa-user-cog" color="teal" />

                <!-- Row 5 -->
                <TotalCountCard title="Downloadables" :count="prov_officials" icon="fas fa-download" color="cyan" />
                <TotalCountCard title="Knowledge Materials" :count="knowledge_materials" icon="fas fa-book"
                    color="violet" />
                <TotalCountCard title="Job Vacancies" :count="jobs" icon="fas fa-briefcase" color="purple" />
            </div>
        </div>

        <div v-if="isModalOpen"
            class="fixed inset-0 bg-gray-900 bg-opacity-50 flex justify-center items-center p-4 z-50">
            <div class="bg-white p-6 rounded shadow-lg w-full max-w-lg mx-4 max-h-[90vh] overflow-y-auto">
                <h2 class="text-xl mb-4 font-extrabold">
                    Update Home Images
                </h2>

                <div v-if="errorMessage" class="bg-red-500 text-white p-2 rounded mb-2 flex items-center">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ errorMessage }}
                </div>

                <label class="font-bold block text-gray-700">Upload Images</label>
                <p class="text-sm text-gray-500">Max 3 images, each up to 5MB</p>
                <input type="file" multiple @change="form.images = [...$event.target.files]"
                    class="border p-2 w-full my-2" accept="image/*" />

                <button @click="showImagePreview = !showImagePreview"
                    class="mt-2 text-blue-600 hover:text-blue-800 text-sm font-medium flex items-center"
                    :disabled="form.images.length === 0">
                    <i :class="showImagePreview ? 'fas fa-eye-slash' : 'fas fa-eye'" class="mr-1"></i>
                    {{ showImagePreview ? 'Hide Preview' : 'Show Preview' }}
                </button>

                <div v-if="showImagePreview && previewImages.length" class="mt-4 border-t pt-4">
                    <div class="space-y-4">
                        <div v-for="(image, index) in previewImages" :key="index" class="relative">
                            <img :src="image" alt="Preview"
                                class="w-full object-cover h-auto p-2 shadow-xl border border-gray-300 rounded" />
                            <span
                                class="absolute top-2 left-2 text-white py-1 px-2 font-bold text-xs flex items-center gap-1 bg-green-500">
                                <i class="fas fa-upload"></i>
                                Selected
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 mt-4">
                    <button @click="submitHomeImages"
                        class="bg-blue-500 text-white px-2 py-1 rounded hover:bg-blue-600 flex items-center gap-1"
                        :disabled="form.images.length === 0">
                        <i class="fas fa-save"></i> Save
                    </button>
                    <button @click="closeModal" class="px-2 py-1 bg-gray-400 rounded hover:bg-gray-500">
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
