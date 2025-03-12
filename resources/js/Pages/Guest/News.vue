<script setup>
import { ref, watch, computed } from "vue";
import { usePage, router, Link } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/autoplay";
import { Pagination, Autoplay } from "swiper/modules";
import { debounce } from "lodash";

defineOptions({ layout: GuestLayout });

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

watch([() => filters.value.from_date, () => filters.value.to_date], () => {
    if (filters.value.from_date && filters.value.to_date) {
        applyFilters();
    }
});

const debouncedSearch = debounce(() => {
    if (filters.value.search === "") {
        applyFilters();
    }
}, 500);

watch(() => filters.value.search, debouncedSearch);

const applyFilters = () => {
    router.get("/guestNews", {
        search: filters.value.search || null,
        from_date: filters.value.from_date || null,
        to_date: filters.value.to_date || null,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ["news", "filters"],
        onSuccess: (page) => {
            pagination.value = page.props.news;
            newsList.value = page.props.news.data;
        },
    });
};

const goToPage = (url) => {
    if (!url) return;
    router.get(url, filters.value, {
        preserveState: true,
        preserveScroll: false,
        only: ["news", "filters"],
        onSuccess: (page) => {
            pagination.value = page.props.news;
            newsList.value = page.props.news.data;
            window.scrollTo({ top: 0, behavior: "smooth" });
        },
    });
};
</script>

<template>
    <div class="p-6 w-full">
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

                <Link href="/guestNews"
                    class="text-gray-700 px-3 py-2 rounded flex items-center bg-gray-400 justify-center hover:bg-gray-500 w-full md:w-auto">
                <i class="fas fa-sync-alt"></i>
                </Link>
            </div>
        </div>

        <div v-if="newsList.length > 0" class="space-y-6 md:px-12">
            <div v-for="news in newsList" :key="news.id"
                class="border border-gray-300 bg-white p-6 shadow-lg flex flex-col md:flex-row gap-6 rounded">
                <div class="w-full md:w-3/5">
                    <p class="text-sm font-extrabold text-gray-500 mb-8">
                        {{ new Date(news.created_at).toLocaleDateString() }}
                    </p>
                    <h2 class="text-lg md:text-xl font-bold text-blue-900 mt-2 line-clamp-2">
                        {{ news.title }}
                    </h2>
                    <p class="text-md text-gray-500 mt-3 line-clamp-5">
                        {{ news.caption }}
                    </p>
                </div>

                <div v-if="news.images.length" class="w-full md:w-2/5 flex justify-center">
                    <swiper :modules="[Pagination, Autoplay]" :pagination="{ clickable: true }"
                        :autoplay="{ delay: 3000, disableOnInteraction: false }" class="w-full max-w-[400px]">
                        <swiper-slide v-for="(image, index) in news.images" :key="index" class="px-2">
                            <div class="relative flex justify-center items-center h-[250px] w-full overflow-hidden">
                                <div class="absolute inset-0 bg-cover bg-center blur-lg scale-110"
                                    :style="{ backgroundImage: `url(/storage/${image})` }"></div>
                                <img :src="`/storage/${image}`" alt="News Image"
                                    class="max-h-full max-w-full object-contain relative z-10" />
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>
            </div>
        </div>

        <p v-else class="text-center text-gray-500 mt-4">No news found.</p>

        <div class="flex justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex space-x-2">
                <button v-for="(link, index) in pagination.links" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed': !link.url,
                    }" class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition"
                    :disabled="!link.url"></button>
            </div>
        </div>
    </div>
</template>
