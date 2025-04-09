<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/autoplay";
import { Pagination, Autoplay } from "swiper/modules";
import { debounce } from "lodash";
import NewsModal from "@/Components/NewsModal.vue";

defineOptions({ layout: GuestLayout });

const selectedNews = ref(null);
const isModalOpen = ref(false);

const openModal = (news) => {
    selectedNews.value = news;
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
};

const formatDate = (dateString) => {
  return new Intl.DateTimeFormat('en-US', {
    year: 'numeric',
    month: 'long',
    day: '2-digit',
    weekday: 'long',
    hour: '2-digit',
    minute: '2-digit',
    hour12: true,
  }).format(new Date(dateString));
};

const pageProps = usePage().props;
const pagination = ref(pageProps.news);
const newsList = ref(pageProps.news.data ?? []);

const filters = ref({
    search: pageProps.filters?.search ?? "",
    from_date: pageProps.filters?.from_date ?? "",
    to_date: pageProps.filters?.to_date ?? "",
});

const paginationInfo = computed(() => {
    const { from, to, total } = pagination.value;
    return from && to ? `Showing ${from} to ${to} of ${total} entries` : "No results found";
});

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

watch(() => filters.value.search, debouncedSearch);

watch([() => filters.value.from_date, () => filters.value.to_date], () => {
    applyFilters();
});

const applyFilters = () => {
    router.get("/News", filters.value, {
        preserveState: true,
        preserveScroll: true,
        only: ["news", "filters"],
        onSuccess: ({ props }) => {
            pagination.value = props.news;
            newsList.value = props.news.data;
        },
    });
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, { ...filters.value }, {
        preserveState: true,
        preserveScroll: true,
        only: ["news", "filters"],
        onSuccess: ({ props }) => {
            Object.assign(pagination.value, props.news);
            newsList.value = props.news.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};

const clearFilters = () => {
    filters.value = {
        search: '',
        from_date: '',
        to_date: ''
    };
    applyFilters();
};
</script>

<template>
    <div class="p-2 lg:p-6 w-full">
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            News & Updates
        </h1>

        <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
            <div class="flex flex-col md:flex-row items-start gap-3">
                <div class="relative w-full md:flex-1">
                    <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                    <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search news..."
                        class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                </div>

                <div class="flex flex-col md:flex-row w-full sm:w-auto gap-2">
                    <div class="flex flex-col md:flex-row w-full sm:w-auto">
                        <label class="text-gray-600 text-sm font-semibold mb-1 md:mb-0 md:mr-2">From:</label>
                        <input v-model="filters.from_date" type="date"
                            class="border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-gray-400 outline-none w-full sm:w-auto" />
                    </div>
                    <div class="flex flex-col md:flex-row w-full sm:w-auto">
                        <label class="text-gray-600 text-sm font-semibold mb-1 md:mb-0 md:mr-2">To:</label>
                        <input v-model="filters.to_date" type="date"
                            class="border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-gray-400 outline-none w-full sm:w-auto" />
                    </div>
                </div>

                <button
                    @click="clearFilters"
                    class="bg-gray-400 text-gray-700 px-2 py-1 flex items-center justify-center md:justify-start gap-2 hover:bg-gray-400 w-full md:w-auto transition-colors"
                >
                    <i class="fas fa-sync-alt"></i>
                    <span class="inline">Clear</span>
                </button>
            </div>
        </div>

        <div v-if="newsList.length > 0" class="space-y-6 md:px-12">
            <div v-for="news in newsList" :key="news.id"
                class="border border-gray-300 bg-white p-6 shadow-lg shadow-black/50 flex flex-col md:flex-row gap-6 rounded cursor-pointer"
                @click="openModal(news)">
                <div class="w-full md:w-3/5">
                    <p class="text-sm uppercase font-black text-gray-700 mb-8">
                        {{ formatDate(news.created_at) }}
                    </p>
                    <h2 class="text-lg md:text-xl font-bold text-blue-900 mt-2 line-clamp-2">
                        {{ news.title }}
                    </h2>
                    <p class="text-md text-gray-500 mt-3 line-clamp-5">
                        {{ news.caption }}
                    </p>
                </div>

                <div v-if="news.images.length" class="w-full md:w-2/5 flex justify-center">
                    <swiper :modules="[Pagination, Autoplay]" :pagination="{ clickable: false }"
                        :allowTouchMove="false" :autoplay="{ delay: 3000, disableOnInteraction: false }"
                        class="w-full max-w-[400px]">
                        <swiper-slide v-for="(image, index) in news.images" :key="index"
                            class="">
                            <div class="flex justify-center items-center h-[250px] w-full overflow-hidden">
                                <img :src="`/news_images/${image}`" alt="News Image"
                                    class="max-h-full max-w-full p-1 border border-gray-300 object-contain transition-transform duration-300 hover:scale-90"
                                    style="box-shadow: 1px 0px 10px rgba(0, 0, 0, 0.75);" />
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No news found.</p>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-4 py-1 mt-1 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>
        <NewsModal
            :newsItem="selectedNews"
            :isOpen="isModalOpen"
            @close="closeModal"
        />
    </div>
</template>
