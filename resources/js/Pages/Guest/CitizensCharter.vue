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

const visiblePages = computed(() => {
    const current = props.charters.current_page;
    const last = props.charters.last_page;
    const pages = [];

    if (last <= 1) return pages;

    if (current !== 1) {
        pages.push({ label: '« First', url: props.charters.first_page_url });
    }

    if (current > 1) {
        pages.push({ label: '‹ Prev', url: props.charters.prev_page_url });
    }

    pages.push({
        label: current.toString(),
        url: props.charters.path + '?page=' + current,
        active: true
    });

    if (current < last) {
        pages.push({ label: 'Next ›', url: props.charters.next_page_url });
    }

    if (current !== last) {
        pages.push({ label: 'Last »', url: props.charters.last_page_url });
    }

    return pages;
});

const pageOptions = computed(() => {
    const pages = [];
    for (let i = 1; i <= props.charters.last_page; i++) {
        pages.push({
            value: i,
            label: i.toString(),
            url: props.charters.path + '?page=' + i
        });
    }
    return pages;
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
            <div class="flex flex-wrap space-x-1 mt-2 sm:mt-0">
                <button v-for="(link, index) in visiblePages" :key="index" @click="goToPage(link.url)"
                    v-html="link.label" :class="{
                        'font-bold bg-blue-300 text-gray-900': link.active,
                        'text-gray-400 cursor-not-allowed pointer-events-none': !link.url,
                    }" class="px-2 border border-gray-300 hover:bg-gray-200 transition" :disabled="!link.url">
                </button>
            </div>
        </div>

        <div v-if="charters.last_page > 1" class="flex justify-center mt-2">
            <select
                v-model="charters.current_page"
                @change="goToPage(charters.path + '?page=' + charters.current_page)"
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
    </div>
</template>
