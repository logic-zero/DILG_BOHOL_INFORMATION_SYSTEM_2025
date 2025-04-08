<script setup>
import { computed, ref } from 'vue'
import { usePage, Link } from '@inertiajs/vue3'
import GuestLayout from '@/Layouts/GuestLayout.vue'
import { Swiper, SwiperSlide } from 'swiper/vue'
import { Pagination, Navigation, Thumbs } from 'swiper/modules'
import 'swiper/css'
import 'swiper/css/pagination'
import 'swiper/css/navigation'

defineOptions({ layout: GuestLayout })

const pageProps = usePage().props
const newsItem = computed(() => pageProps.news)
const thumbsSwiper = ref(null)

const shareOnFacebook = () => {
    const url = encodeURIComponent(window.location.href)
    window.open(`https://www.facebook.com/sharer/sharer.php?u=${url}`, '_blank', 'width=600,height=400')
}

const formatDate = (dateString) => {
    return new Intl.DateTimeFormat('en-US', {
        year: 'numeric',
        month: 'long',
        day: '2-digit',
        weekday: 'long',
        hour: '2-digit',
        minute: '2-digit',
        hour12: true,
    }).format(new Date(dateString))
}

const setThumbsSwiper = (swiper) => {
    thumbsSwiper.value = swiper
}
</script>

<template>
    <div class="min-h-screen p-4 md:p-8">
        <div class="mx-auto mb-4 flex justify-between items-center">
            <div>
                <h1 class="text-2xl md:text-3xl font-bold text-blue-900 mb-2">News Details</h1>
                <Link href="/News" class="flex items-center text-blue-600 hover:text-blue-800 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd"
                            d="M9.707 16.707a1 1 0 01-1.414 0l-6-6a1 1 0 010-1.414l6-6a1 1 0 011.414 1.414L5.414 9H17a1 1 0 110 2H5.414l4.293 4.293a1 1 0 010 1.414z"
                            clip-rule="evenodd" />
                    </svg>
                    Back to News Page
                </Link>
            </div>

            <button @click="shareOnFacebook" class="flex items-center bg-blue-600 hover:bg-blue-700 text-white px-3 py-1 rounded transition">
                <i class="fab fa-facebook-f mr-2"></i>
                Share
            </button>
        </div>

        <div class="mx-auto bg-white rounded shadow-lg overflow-hidden">
            <div class="xl:flex">
                <div class="xl:w-1/2 p-4 bg-gray-200 relative">
                    <swiper :modules="[Pagination, Navigation, Thumbs]" :pagination="{ clickable: true }" :navigation="{
                        nextEl: '.swiper-button-next',
                        prevEl: '.swiper-button-prev',
                    }" :thumbs="{ swiper: thumbsSwiper }" class="relative overflow-hidden mb-4 shadow-lg main-swiper">
                        <swiper-slide v-for="(image, index) in newsItem.images" :key="index">
                            <div class="aspect-w-4 aspect-h-3 bg-gray-200">
                                <img :src="`/news_images/${image}`" :alt="`News image ${index + 1}`"
                                    class="object-contain w-full h-full p-1 bg-white select-none" draggable="false" />
                            </div>
                        </swiper-slide>

                        <div
                            class="swiper-button-prev absolute left-3 top-1/2 z-10 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/40 rounded-full backdrop-blur-sm hover:bg-white/60 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700 hover:text-blue-600"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                        </div>
                        <div
                            class="swiper-button-next absolute right-3 top-1/2 z-10 -translate-y-1/2 w-8 h-8 flex items-center justify-center bg-white/40 rounded-full backdrop-blur-sm hover:bg-white/60 transition shadow-sm">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-gray-700 hover:text-blue-600"
                                fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                    </swiper>

                    <swiper @swiper="setThumbsSwiper" :spaceBetween="8" :slidesPerView="4" :freeMode="true"
                        :watchSlidesProgress="true" class="thumbnail-swiper mt-2">
                        <swiper-slide v-for="(image, index) in newsItem.images" :key="index"
                            class="cursor-pointer opacity-50 hover:opacity-100 transition thumbnail-slide">
                            <div
                                class="aspect-w-1 aspect-h-1 bg-gray-200 overflow-hidden border-2 border-transparent hover:border-blue-500">
                                <img :src="`/news_images/${image}`" :alt="`Thumbnail ${index + 1}`"
                                    class="object-cover w-full h-full p-1 bg-white select-none" draggable="false" />
                            </div>
                        </swiper-slide>
                    </swiper>
                </div>

                <div class="xl:w-1/2 p-6 md:p-8">
                    <div class="text-content">
                        <p class="text-sm uppercase font-semibold text-gray-500 mb-4">
                            {{ formatDate(newsItem.created_at) }}
                        </p>

                        <h2 class="text-2xl font-bold text-blue-900 mb-6">
                            {{ newsItem.title }}
                        </h2>

                        <div class="prose prose-blue max-w-none text-gray-700 mb-6">
                            <p class="whitespace-pre-line">{{ newsItem.caption }}</p>
                        </div>

                        <div v-if="newsItem.content" class="prose prose-blue max-w-none text-gray-700">
                            <p class="whitespace-pre-line">{{ newsItem.content }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.main-swiper {
    --swiper-pagination-color: #2563eb;
    --swiper-pagination-bullet-inactive-color: #9ca3af;
    --swiper-pagination-bullet-inactive-opacity: 1;
}

.thumbnail-swiper :deep(.swiper-slide-thumb-active) {
    opacity: 1;
    border-color: #3b82f6;
}

.prose :deep(img) {
    max-width: 100%;
    height: auto;
    margin: 1rem 0;
}

.prose :deep(iframe) {
    max-width: 100%;
    margin: 1rem 0;
}

.swiper-button-prev,
.swiper-button-next {
    user-select: none;
    -webkit-user-select: none;
    -moz-user-select: none;
    -ms-user-select: none;
    outline: none;
    width: 32px;
    height: 32px;
    background-color: rgba(255, 255, 255, 0.4);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.2s ease;
    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.1);
}

.swiper-button-prev:hover,
.swiper-button-next:hover {
    background-color: rgba(255, 255, 255, 0.6);
    transform: translateY(-50%) scale(1.05);
}

.swiper-button-prev:active,
.swiper-button-next:active {
    transform: translateY(-50%) scale(0.95);
}

.swiper-button-prev::after,
.swiper-button-next::after {
    display: none;
}

.swiper-button-prev {
    left: 12px;
}

.swiper-button-next {
    right: 12px;
}
</style>
