<script setup>
import { defineProps } from 'vue';
import { ref, watch, computed } from "vue";
import { usePage, router } from "@inertiajs/vue3";
import { debounce } from "lodash";
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    lgus: Array,
    municipalities: Array,
});

const pageProps = usePage().props;
const lgus = ref(pageProps.lgus);
const filters = ref({
    search: pageProps.filters?.search ?? "",
    municipality_id: pageProps.filters?.municipality_id ?? "",
});

const debouncedSearch = debounce(() => {
    applyFilters();
}, 500);

watch(() => filters.value.search, debouncedSearch);

const applyFilters = () => {
    router.get("/guestLGUs", {
        search: filters.value.search || null,
        municipality_id: filters.value.municipality_id || null,
    }, {
        preserveState: true,
        preserveScroll: true,
        only: ["lgus", "filters"],
        onSuccess: ({ props }) => {
            lgus.value = props.lgus;
        },
    });
};

const getSbMembers = (lgu) => {
    return [
        lgu.sb_member1, lgu.sb_member2, lgu.sb_member3, lgu.sb_member4,
        lgu.sb_member5, lgu.sb_member6, lgu.sb_member7, lgu.sb_member8,
        lgu.sb_member9, lgu.sb_member10
    ].filter(member => member);
};
</script>

<template>
    <div>
        <div class="container mx-auto p-6">
            <div class="flex justify-center items-center mt-2 mb-4">
                <img src="/img/bohol_seal.png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="bohol-seal">
            </div>
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 text-[#3B6790]">
                <div class="space-y-2">
                    <p class="font-semibold text-xl">Executive Brief</p>
                    <p><span class="font-medium">Location:</span> 803 km south of Manila, 79 km southeast of Cebu</p>
                    <p><span class="font-medium">Capital:</span> Tagbilaran City</p>
                    <p><span class="font-medium">Cities:</span> 1</p>
                    <p><span class="font-medium">Municipalities:</span> 47</p>
                    <p><span class="font-medium">Barangays:</span> 1,113</p>
                    <p><span class="font-medium">Land Area:</span> 4,117.26 km²</p>
                    <p><span class="font-medium">Population:</span> 1,313,560</p>
                    <p><span class="font-medium">Province Population:</span> 1,230,110 (2007 Census)</p>
                    <p><span class="font-medium">City Population:</span> 92,297 (2007 Census)</p>
                    <p><span class="font-medium">Languages/Dialects:</span> Boholano, English, Tagalog, Chinese</p>
                </div>

                <div class="text-[#727D73]">
                    <p class="text-justify">
                        THE Province of Bohol is an island haven tucked away in the Filipino region of Visayas.
                        This is one of the largest of more than 7,000 islands that comprise the Philippines, and it
                        consistently draws tourists with its natural beauty, hilly interior and long stretches of white,
                        sandy beaches.
                        Nested as it is in the midst of Visayas, Bohol has long been a protected center of culture and
                        politics. Locals proudly refer to the island as the ‘Republic of Bohol’.
                    </p>
                    <p class="text-justify mt-4">
                        <span class="font-medium">Vision:</span> Bohol is a prime eco-cultural tourism destination and a
                        strong, balanced agro-industrial
                        province, with a well-educated, God-loving and law-abiding citizenry, proud of their cultural
                        heritage, enjoying a state of well-being and committed to sound environment management.
                    </p>
                    <p class="text-justify mt-4">
                        <span class="font-medium">Mission:</span> To enrich Bohol’s social, economic, cultural,
                        political and environmental resources through
                        good governance and effective partnerships with stakeholders for increased global
                        competitiveness.
                    </p>
                    <p class="text-justify mt-4">
                        <span class="font-medium">Goal:</span><br>
                        1. Environmental Protection and Management;<br>
                        2. Social Equity;<br>
                        3. Delivering quality services;<br>
                        4. Local/Regional Economic Development and Strategic Wealth Generation;<br>
                        5. Responsive, Transparent and Accountable Governance.
                    </p>
                </div>
            </div>
        </div>
        <div class="p-6 w-full">
            <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
                LIST OF LOCAL ELECTIVE OFFICIALS
            </h1>

            <div class="bg-gray-500 bg-opacity-20 p-2 shadow-md mb-6">
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


            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 md:px-12">
                <div v-for="lgu in lgus" :key="lgu.id"
                    class="bg-white shadow-md rounded p-4 border flex flex-col lg:flex-row">
                    <div class="flex-1 pr-4">
                        <p class="text-center text-red-600 font-semibold text-xl mb-4 break-words">
                            {{ lgu.municipality.municipality }}
                        </p>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600 break-words">
                                <span class="font-bold block">Mayor:</span> {{ lgu.mayor }}
                            </p>
                            <p class="text-sm text-gray-600 break-words">
                                <span class="font-bold block">Vice Mayor:</span> {{ lgu.vice_mayor }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <p v-for="(sbMember, index) in getSbMembers(lgu)" :key="index"
                                class="text-sm text-gray-600 break-words">
                                <span class="font-bold">SB Member:</span> {{ sbMember }}
                            </p>
                        </div>
                        <div class="space-y-2 mt-4">
                            <p v-if="lgu.lb_pres" class="text-sm text-gray-600 break-words">
                                <span class="font-bold block">Liga ng mga Brgy. Pres:</span> {{ lgu.lb_pres }}
                            </p>
                            <p v-if="lgu.psk_pres" class="text-sm text-gray-600 break-words">
                                <span class="font-bold block">PSK President:</span> {{ lgu.psk_pres }}
                            </p>
                        </div>
                    </div>
                    <div class="flex-none w-full lg:w-1/2 flex flex-col lg:flex-1">
                        <iframe :src="lgu.municipality.gmap_url" class="w-full aspect-square"
                            style="border: 0;"></iframe>
                        <div class="mt-4">
                            <p class="text-sm text-gray-600 text-center font-medium mb-2">
                                No. of Barangays: {{ lgu.municipality.num_of_brgys }}
                            </p>
                            <select class="w-full p-2 border text-sm">
                                <option selected>List of Barangays ⥂</option>
                                <option v-for="brgy in JSON.parse(lgu.municipality.barangays)" :key="brgy">{{ brgy }}
                                </option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
