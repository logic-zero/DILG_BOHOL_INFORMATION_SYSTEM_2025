<script setup>
import { defineProps } from 'vue';
import { ref, watch } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    fieldOfficers: Array,
    municipalities: Array,
});

const pageProps = usePage().props;
const fieldOfficers = ref(pageProps.fieldOfficers);
const filters = ref({
    search: pageProps.filters?.search ?? "",
    municipality_id: pageProps.filters?.municipality_id ?? "",
});

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

watch(() => filters.value.search, debouncedSearch);

const applyFilters = () => {
    router.get("/fieldOfficers", {
        search: filters.value.search || null,
        municipality_id: filters.value.municipality_id || null,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ["fieldOfficers", "filters"],
        onSuccess: ({ props }) => {
            fieldOfficers.value = props.fieldOfficers;
        },
    });
};
</script>

<template>
    <div>
        <div class="p-6 w-full">
            <div class="flex justify-center items-center mb-6">
                <img src="/img/dilg-main    .png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="bohol-seal">
            </div>
            <h1 class="text-3xl p-2 font-bold text-center mb-6 uppercase">
                FIELD OFFICERS
            </h1>

            <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-10">
                <div class="flex flex-col md:flex-row items-start gap-3">
                    <div class="relative w-full md:flex-1">
                        <i class="absolute left-3 top-2 text-gray-500 fas fa-search"></i>
                        <input v-model="filters.search" @keyup.enter="applyFilters" type="text" placeholder="Search..."
                            class="border border-gray-300 pl-10 pr-3 py-1 w-full focus:ring-2 focus:ring-gray-400 outline-none" />
                    </div>

                    <div class="w-full sm:w-64 md:w-80">
                        <select v-model="filters.municipality_id" @change="applyFilters"
                            class="border border-gray-300 px-2 py-1 focus:ring-2 focus:ring-gray-400 outline-none w-full">
                            <option value="">All Municipalities</option>
                            <option v-for="municipality in municipalities" :key="municipality.id"
                                :value="municipality.id">
                                {{ municipality.municipality }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 xl:grid-cols-6 gap-10">
                <div v-for="officer in fieldOfficers" :key="officer.id" class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-40 h-40 border border-black overflow-hidden rounded-full">
                            <img v-if="officer.profile_img" :src="`/storage/${officer.profile_img}`" :alt="officer.fname + ' ' + officer.lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ officer.fname }} {{ officer.lname }}
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ officer.position }}
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ officer.cluster }}
                            </p>
                            <p class="text-xs font-bold text-gray-800">
                                {{ officer.municipality.municipality }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.group:hover .transform {
    transform: scale(1.15);
}
</style>
