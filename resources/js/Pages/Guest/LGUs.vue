<script setup>
import { defineProps } from 'vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';

defineOptions({ layout: GuestLayout });

const props = defineProps({
    lgus: Array,
    municipalities: Array,
});

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
                <div>
                    <p class="font-medium text-lg">EXECUTIVE BRIEF</p>
                    <p class="mt-1">Location: 803 km. south of Manila 79 km southeast of Cebu</p>
                    <p>Capital: Tagbilaran City</p>
                    <p>Cities: 1</p>
                    <p>Municipalities: 47</p>
                    <p>Barangays: 1,113</p>
                    <p>Land Area: 4,117.26 square kilometers</p>
                    <p>Population: 1,313,560</p>
                    <p>Province: 1,230,110 (2007 Census)</p>
                    <p>City: 92,297 (2007 Census)</p>
                    <p>Language/Dialects: Boholano, English, Tagalog, Chinese</p>
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

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-4 md:px-12">
                <div v-for="lgu in lgus" :key="lgu.id"
                    class="bg-white shadow-md rounded p-4 border flex flex-col sm:flex-row">
                    <div class="flex-1 pr-4">
                        <p class="text-center text-red-600 font-semibold text-xl mb-4">{{ lgu.municipality.municipality
                        }}</p>
                        <div class="space-y-2">
                            <p class="text-sm text-gray-600">
                                <span class="font-bold block">Mayor:</span> {{ lgu.mayor }}
                            </p>
                            <p class="text-sm text-gray-600">
                                <span class="font-bold block">Vice Mayor:</span> {{ lgu.vice_mayor }}
                            </p>
                        </div>
                        <div class="grid grid-cols-2 gap-2 mt-4">
                            <p v-for="(sbMember, index) in getSbMembers(lgu)" :key="index"
                                class="text-sm text-gray-600">
                                <span class="font-bold">SB Member:</span> {{ sbMember }}
                            </p>
                        </div>
                        <div class="space-y-2 mt-4">
                            <p v-if="lgu.lb_pres" class="text-sm text-gray-600">
                                <span class="font-bold block">Liga ng mga Brgy. Pres:</span> {{ lgu.lb_pres }}
                            </p>
                            <p v-if="lgu.psk_pres" class="text-sm text-gray-600">
                                <span class="font-bold block">PSK President:</span> {{ lgu.psk_pres }}
                            </p>
                        </div>
                    </div>
                    <div class="flex-none w-full sm:w-1/2 flex flex-col">
                        <iframe :src="lgu.municipality.gmap_url" class="w-full h-64 sm:h-72"
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
