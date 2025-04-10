<script setup>
import { ref, computed } from "vue";
import { router } from "@inertiajs/vue3";
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({
    layout: GuestLayout,
});

const props = defineProps({
    charters: Object,
    pdf: Object,
});

const paginationInfo = computed(() => {
    const { from, to, total } = props.charters;
    return from && to
        ? `Showing ${from} to ${to} of ${total} entries`
        : "No results found";
});

const goToPage = (url) => {
    if (url) {
        router.visit(url, {
            preserveState: true,
            preserveScroll: true,
        });
    }
};
</script>

<template>
    <div class="p-2 lg:p-6 w-full">
        <div class="flex justify-center items-center mb-6">
            <img src="/img/dilg-main.png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="bohol-seal">
        </div>
        <h1 class="text-3xl text-black p-2 font-bold text-center mb-6 uppercase">
            CITIZENS CHARTER
        </h1>

        <div v-if="pdf?.file" class="max-w-5xl mx-auto mb-2 flex justify-end">
            <a
                :href="`/citizens-charter/download-pdf`"
                class="inline-flex items-center justify-center gap-2 px-4 py-1 bg-red-700 text-white rounded hover:bg-red-800 transition"
            >
                <i class="fas fa-file-pdf"></i>
                <span>Download PDF</span>
            </a>
        </div>

        <div class="space-y-4">
            <div v-for="charter in charters.data" :key="charter.id">
                <h2 class="text-xl font-extrabold text-blue-900 border pb-4 bg-gray-200 border-gray-500 max-w-5xl mx-auto p-1 mb-2 text-center">
                    {{ charter.title }}
                </h2>

                <div class="max-w-5xl mx-auto overflow-hidden border border-gray-500">
                    <video
                        controls
                        class="w-full aspect-[3/2]"
                        :poster="charter.thumbnail ? `/citizens_charters/thumbnails/${charter.thumbnail}` : '/img/default-video-thumbnail.jpg'"
                        preload="none"
                        loading="lazy"
                    >
                        <source :src="`/citizens_charters/${charter.file}`" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            </div>
        </div>

        <div class="flex flex-col sm:flex-row justify-between items-center mt-6 text-gray-700">
            <span>{{ paginationInfo }}</span>
            <div class="flex flex-wrap space-x-2 mt-2 sm:mt-0">
                <button
                    v-for="(link, index) in charters.links"
                    :key="index"
                    @click="goToPage(link.url)"
                    v-html="link.label"
                    :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }"
                    class="px-4 py-1 border border-gray-300 hover:bg-gray-200 transition"
                    :disabled="!link.url"
                ></button>
            </div>
        </div>
    </div>
</template>
