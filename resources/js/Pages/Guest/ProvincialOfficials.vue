<template>
    <div>
        <div class="flex justify-center items-center mt-8 mb-20">
            <img src="/img/bohol_seal.png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="bohol-seal">
        </div>

        <h1 class="text-2xl font-bold text-center mb-10 mt-10">PROVINCIAL OFFICIALS</h1>

        <div v-if="governor.length > 0" class="mb-8">
            <div class="flex justify-center">
                <div v-for="official in governor" :key="official.id" class="text-center mx-4">
                    <img :src="`/provincial_officials/${official.profile_image}`" class="w-80 h-80 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                    <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                    <p class="text-sm text-gray-600">{{ official.position }}</p>
                </div>
            </div>
        </div>

        <div v-if="viceGovernor.length > 0" class="mb-8">
            <div class="flex justify-center">
                <div v-for="official in viceGovernor" :key="official.id" class="text-center mx-4">
                    <img :src="`/provincial_officials/${official.profile_image}`" class="w-64 h-64 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                    <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                    <p class="text-sm text-gray-600">{{ official.position }}</p>
                </div>
            </div>
        </div>

        <div v-if="firstDistrict.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-4">1st District Board Member</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div v-for="official in firstDistrict" :key="official.id" class="text-center">
                    <img :src="`/provincial_officials/${official.profile_image}`" class="w-48 h-48 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                    <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                    <p class="text-sm text-gray-600">{{ official.position }}</p>
                </div>
            </div>
        </div>

        <div v-if="secondDistrict.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-4">2nd District Board Member</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div v-for="official in secondDistrict" :key="official.id" class="text-center">
                    <img :src="`/provincial_officials/${official.profile_image}`" class="w-48 h-48 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                    <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                    <p class="text-sm text-gray-600">{{ official.position }}</p>
                </div>
            </div>
        </div>

        <div v-if="thirdDistrict.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-4">3rd District Board Member</h2>
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-4">
                <div v-for="official in thirdDistrict" :key="official.id" class="text-center">
                    <img :src="`/provincial_officials/${official.profile_image}`" class="w-48 h-48 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                    <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                    <p class="text-sm text-gray-600">{{ official.position }}</p>
                </div>
            </div>
        </div>

        <div v-if="exOfficio.length > 0" class="mb-8">
            <h2 class="text-2xl font-bold text-center mb-4">Ex-Officio</h2>
            <div class="flex justify-center">
                <div class="text-center">
                    <div v-for="official in exOfficio" :key="official.id" class="mb-4">
                        <img :src="`/provincial_officials/${official.profile_image}`" class="w-40 h-40 rounded-full mx-auto object-cover shadow-lg border border-black" alt="Profile Image" />
                        <p class="mt-2 text-lg font-bold">{{ official.name }}</p>
                        <p class="text-sm text-gray-600">{{ official.position }}</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import GuestLayout from "../../Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    governor: Array,
    viceGovernor: Array,
    firstDistrict: Array,
    secondDistrict: Array,
    thirdDistrict: Array,
    exOfficio: Array,
});
</script>
