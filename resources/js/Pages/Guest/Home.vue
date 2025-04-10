<template>
    <div>
        <div class="md:p-6 pt-0 md:pt-3 w-full">
            <div class="w-full h-[40vh] sm:h-[50vh] md:h-[70vh] lg:h-[700px] overflow-hidden">
                <swiper
                    :modules="[Autoplay]"
                    :allowTouchMove="false"
                    :autoplay="{ delay: 20000, disableOnInteraction: false }"
                    class="w-full h-full"
                >
                    <swiper-slide v-for="(image, index) in images" :key="index">
                        <div class="w-full h-full flex justify-center items-center bg-transparent">
                            <img
                                :src="`/home_images/${image}`"
                                alt="Cover Photo"
                                class="w-full h-full object-contain"
                            />
                        </div>
                    </swiper-slide>
                </swiper>
            </div>

            <div class="container mx-auto px-2 sm:px-4">
                <div class="flex flex-col md:flex-row flex-wrap">
                    <div class="w-full md:w-1/2 p-2 md:p-4 order-2 md:order-1">
                        <div class="flex flex-col items-center md:items-start justify-center h-full space-y-3">
                            <Link href="/provincialDirector" class="btn rounded bg-blue-800 hover:bg-blue-900 text-white btn-md w-full sm:w-96 px-3 py-2 sm:px-4 sm:py-3 flex justify-between items-center">
                                <span class="text-sm sm:text-base">THE PROVINCIAL DIRECTOR</span>
                                <span class="fas fa-arrow-right text-xs sm:text-sm"></span>
                            </Link>

                            <Link href="/aboutUs" class="btn rounded bg-blue-800 hover:bg-blue-900 text-white btn-md w-full sm:w-96 px-3 py-2 sm:px-4 sm:py-3 flex justify-between items-center">
                                <span class="text-sm sm:text-base">VISION & MISSION</span>
                                <span class="fas fa-arrow-right text-xs sm:text-sm"></span>
                            </Link>

                            <Link href="/latestIssuances" class="btn rounded bg-blue-800 hover:bg-blue-900 text-white btn-md w-full sm:w-96 px-3 py-2 sm:px-4 sm:py-3 flex justify-between items-center">
                                <span class="text-sm sm:text-base">LATEST ISSUANCES</span>
                                <span class="fas fa-arrow-right text-xs sm:text-sm"></span>
                            </Link>
                        </div>
                    </div>

                    <div class="w-full md:w-1/2 p-2 md:p-4 order-1 md:order-2">
                        <div class="flex flex-col items-center justify-center my-3 sm:my-5 text-center">
                            <img src="/img/dilg-main.png" alt="DILG Logo" class="mb-2 sm:mb-3 h-20 sm:h-24 md:h-32 w-20 sm:w-24 md:w-32">
                            <h1 class="text-base sm:text-lg font-bold">
                                Department of the Interior and Local Government
                            </h1>
                            <p class="mt-2 sm:mt-3 max-w-2xl mx-auto text-xs sm:text-sm md:text-base">
                                The DILG is the executive department of the Philippine government responsible for promoting peace and
                                order, ensuring public safety, and strengthening local government capability aimed towards the effective
                                delivery of basic services to the citizenry.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="p-2 lg:p-6 w-full">
            <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
                Latest News & Updates
            </h1>

            <div v-if="newsList.length > 0" class="space-y-6 md:px-12">
                <div v-for="news in newsList" :key="news.id"
                    class="border border-gray-300 bg-white p-6 shadow-lg shadow-black/50 flex flex-col md:flex-row gap-6 rounded cursor-pointer"
                    @click="openModal(news)">
                    <div class="w-full md:w-3/5">
                        <p class="text-xs uppercase font-black text-blue-900 mb-8">
                            {{ formatDate(news.created_at) }}
                        </p>
                        <h2 class="text-lg font-bold text-gray-700 mt-2 line-clamp-2">
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
                                    <img :src="`/news_images/${image}`" alt="News Image" loading="lazy"
                                        class="max-h-full max-w-full p-1 border border-gray-300 object-contain transition-transform duration-300 hover:scale-90"
                                        style="box-shadow: 1px 0px 10px rgba(0, 0, 0, 0.75);" />
                                </div>
                            </swiper-slide>
                        </swiper>
                    </div>
                </div>

                <div class="flex justify-center">
                    <Link href="/News"
                        class="text-white text-sm px-2 py-1 rounded bg-blue-800 hover:bg-blue-900 inline-flex items-center">
                    Discover More News...
                    </Link>
                </div>
            </div>

            <p v-else class="text-center text-gray-500 mt-4">No news found.</p>

            <NewsModal
                :newsItem="selectedNews"
                :isOpen="isModalOpen"
                @close="closeModal"
            />
        </div>

        <div class="p-2 lg:p-6 w-full">
            <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
                Latest Issuances
            </h1>

            <div v-if="issuances.length > 0" class="space-y-4 md:px-12">
                <div v-for="issuance in issuances" :key="issuance.id"
                    class="border border-gray-200 p-4 cursor-pointer transition-all duration-200 bg-white hover:shadow-md"
                    @click="toggleIssuance(issuance.id)">

                    <div class="flex flex-col md:flex-row md:justify-between md:items-start gap-2">
                        <div class="flex-1 space-y-1">
                            <p class="text-xs text-gray-500 font-medium">
                                {{ issuance.date }}
                            </p>
                            <h2 class="text-base font-semibold text-gray-800">{{ issuance.title }}</h2>
                            <p class="text-xs text-gray-600">{{ issuance.outcome_area }}</p>
                        </div>
                        <div class="md:ml-5">
                            <p class="text-xs font-semibold text-gray-700 md:text-right">
                                <span class="text-red-500">Ref:</span> {{ issuance.reference_num }}
                            </p>
                            <p class="text-xs text-gray-600 font-bold text-right">{{ issuance.category }}</p>
                        </div>
                    </div>

                    <div class="mt-3 flex items-center justify-between border-t pt-3">
                        <a :href="'/issuance_files/' + issuance.file" download
                            class="text-xs font-medium text-red-600 hover:text-red-700 flex items-center"
                            @click.stop>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            Download PDF
                        </a>

                        <div class="flex items-center space-x-2">
                            <span class="text-xs text-gray-500">Click to view</span>
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-gray-500 transition-transform duration-200"
                                :class="{ 'transform rotate-180': selectedIssuanceId === issuance.id }"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </div>
                    </div>

                    <div :style="{ maxHeight: selectedIssuanceId === issuance.id ? '600px' : '0' }"
                        class="overflow-hidden transition-max-height duration-300 ease-out">
                        <div class="mt-4 border-t pt-4">
                            <div class="relative">
                                <div class="absolute top-2 right-5 flex gap-2">
                                    <div class="hidden md:block bg-white text-xs px-3 py-1 rounded shadow-md">
                                        <i class="fas fa-search-plus mr-1"></i>
                                        Hold <span class="font-bold">Ctrl</span> + <span class="font-bold">Scroll</span> to zoom
                                    </div>
                                    <button @click.stop="openFullScreen(issuance.file)" class="bg-white text-xs px-3 py-1 rounded shadow-md hover:bg-gray-100">
                                        <i class="fas fa-expand mr-1"></i> Fullscreen
                                    </button>
                                </div>

                                <iframe :src="'/issuance_files/' + issuance.file + '#toolbar=0'" width="100%"
                                    height="500px" loading="lazy"></iframe>
                            </div>
                            <div class="border border-gray-500 bg-gray-100 text-gray-700 p-2 rounded text-center w-full mt-2">
                                <div class="flex items-center justify-center">
                                    <i class="fas fa-exclamation-triangle text-lg mr-2"></i>
                                    <p class="text-xs font-semibold">
                                        If PDF preview doesn't work, please download the file above.
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="flex justify-center">
                    <Link href="/latestIssuances"
                        class="text-white text-sm px-2 py-1 rounded bg-blue-800 hover:bg-blue-900 inline-flex items-center">
                    Discover More Issuances...
                    </Link>
                </div>
            </div>

            <p v-else class="text-center text-gray-500 mt-4">No issuances found.</p>
        </div>

        <div class="container mx-auto px-4 py-6 max-w-[1400px]">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="flex-1 flex flex-col gap-4 md:w-auto w-full">
                    <div class="w-full aspect-video">
                        <iframe class="w-full h-full border-0 rounded"
                            src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3861.1757461435755!2d123.8735479!3d9.6530238!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x33aa4d0045215a9d%3A0x9061d57f8098ab66!2sDepartment%20of%20the%20Interior%20and%20Local%20Government%20-%20Bohol%20Province%20Region-7%20Office!5e0!3m2!1sen!2sph!4v1709012345678!5m2!1sen!2sph"
                            allowfullscreen loading="lazy"></iframe>
                    </div>

                    <div class="w-full aspect-video">
                        <iframe class="w-full h-full border-0 rounded" src="https://www.youtube.com/embed/cwAGIorJD6o"
                            title="Baya't LGOO" frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                            referrerpolicy="strict-origin-when-cross-origin" loading="lazy" allowfullscreen></iframe>
                    </div>
                </div>

                <div class="flex flex-col gap-4 md:w-[500px] w-full">
                    <div class="h-[300px] md:h-[70%] min-h-[400px]">
                        <iframe class="w-full h-full border-0 rounded overflow-auto"
                            src="https://www.facebook.com/plugins/page.php?href=https%3A%2F%2Fwww.facebook.com%2Fdilgboholprovince&tabs=timeline&width=500&height=800&small_header=false&adapt_container_width=false&hide_cover=false&show_facepile=true"
                            allowfullscreen loading="lazy"></iframe>
                    </div>

                    <div>
                        <Calendar/>
                    </div>
                </div>
            </div>

            <div class="mt-4">
                <div class="rounded" id="ww_17a0c713a0539" v='1.3' loc='id'
                    a='{"t":"responsive","lang":"en","sl_lpl":1,"ids":["wl5320"],"font":"Arial","sl_ics":"one_a","sl_sot":"celsius","cl_bkg":"#0097A7","cl_font":"#FFFFFF","cl_cloud":"#FFFFFF","cl_persp":"#FFFFFF","cl_sun":"#FFC107","cl_moon":"#FFC107","cl_thund":"#FF5722","cl_odd":"#0000000a"}'>
                    <a href="https://weatherwidget.org/" id="ww_17a0c713a0539_u" target="_blank">Html weather widget</a>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { onMounted, ref, computed } from 'vue';
import { usePage, Link } from "@inertiajs/vue3";
import { Swiper, SwiperSlide } from "swiper/vue";
import "swiper/css";
import "swiper/css/pagination";
import "swiper/css/autoplay";
import { Pagination, Autoplay } from "swiper/modules";
import NewsModal from "@/Components/NewsModal.vue";
import GuestLayout from "../../Layouts/GuestLayout.vue"
import Calendar from '@/Components/Calendar.vue';

defineOptions({ layout: GuestLayout });

const pageProps = usePage().props;
const images = ref(pageProps.images ?? []);
const issuances = ref(pageProps.b_issuances ?? []);
const selectedIssuanceId = ref(null);

onMounted(() => {
    const script = document.createElement("script");
    script.src = "https://app3.weatherwidget.org/js/?id=ww_17a0c713a0539";
    script.async = true;
    document.body.appendChild(script);
});

const toggleIssuance = (issuanceId) => {
    selectedIssuanceId.value = selectedIssuanceId.value === issuanceId ? null : issuanceId;
};

const openFullScreen = (fileUrl) => {
    window.open('/issuance_files/' + fileUrl, '_blank');
};

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
    return new Intl.DateTimeFormat("en-US", {
        year: "numeric",
        month: "long",
        day: "2-digit",
        weekday: "long",
        hour: "2-digit",
        minute: "2-digit",
        hour12: true,
    }).format(new Date(dateString));
};

const newsList = ref(usePage().props.news ?? []);
</script>

<style scoped>
.transition-max-height {
    transition-property: max-height;
    transition-timing-function: ease-out;
}
</style>
