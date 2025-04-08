<template>
    <div>
        <div class="mx-auto p-6">
            <div class="flex justify-center items-center mb-6">
                <img src="/img/dilg-main.png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="DILG Logo">
            </div>
            <h1 class="text-3xl font-bold text-center mb-12 uppercase">ORGANIZATIONAL STRUCTURE</h1>

            <div v-if="provincialDirector" class="flex justify-center mb-8">
                <div class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-72 h-72 border border-black overflow-hidden rounded-full">
                            <img v-if="provincialDirector.profile_img" :src="`/organizational_structure/${provincialDirector.profile_img}`"
                                :alt="provincialDirector.fname + ' ' + provincialDirector.lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-semibold text-gray-800">
                                {{ provincialDirector.fname }} {{ provincialDirector.mid_initial }}. {{ provincialDirector.lname }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ provincialDirector.position }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="groupedData['Cluster Head, D\'One'] || groupedData['Cluster Head, M&M']" class="grid grid-cols-1 sm:grid-cols-2 justify-items-center gap-8 mb-8 px-4">
                <div v-for="head in [...(groupedData['Cluster Head, D\'One'] || []), ...(groupedData['Cluster Head, M&M'] || [])]" :key="head.id" class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-64 h-64 border border-black overflow-hidden rounded-full">
                            <img v-if="head.profile_img" :src="`/organizational_structure/${head.profile_img}`"
                                :alt="head.fname + ' ' + head.lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-semibold text-gray-800">
                                {{ head.fname }} {{ head.mid_initial }}. {{ head.lname }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ head.position }} / {{ head.management_and_administrative_roles }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="groupedData['Program Manager']" class="flex justify-center mb-8">
                <div class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-64 h-64 border border-black overflow-hidden rounded-full">
                            <img v-if="groupedData['Program Manager'][0].profile_img" :src="`/organizational_structure/${groupedData['Program Manager'][0].profile_img}`"
                                :alt="groupedData['Program Manager'][0].fname + ' ' + groupedData['Program Manager'][0].lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-lg font-semibold text-gray-800">
                                {{ groupedData['Program Manager'][0].fname }} {{ groupedData['Program Manager'][0].mid_initial }}. {{ groupedData['Program Manager'][0].lname }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ groupedData['Program Manager'][0].position }} / {{ groupedData['Program Manager'][0].management_and_administrative_roles }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="groupedData['Program Coordinators']" class="mb-8">
                <h2 class="text-2xl font-bold text-center mb-4">PROGRAM COORDINATORS</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-4">
                    <div v-for="coordinator in groupedData['Program Coordinators']" :key="coordinator.id" class="group">
                        <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                            <div class="w-40 h-40 border border-black overflow-hidden rounded-full">
                                <img v-if="coordinator.profile_img" :src="`/organizational_structure/${coordinator.profile_img}`"
                                    :alt="coordinator.fname + ' ' + coordinator.lname"
                                    class="w-full h-full object-cover rounded-full" />
                                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                    No Image
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ coordinator.fname }} {{ coordinator.mid_initial }}. {{ coordinator.lname }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ coordinator.position }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="groupedData['Admin Services']" class="mb-8">
                <h2 class="text-2xl font-bold text-center mb-4">ADMIN SERVICES</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="admin in groupedData['Admin Services']" :key="admin.id" class="group">
                        <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                            <div class="w-40 h-40 border border-black overflow-hidden rounded-full">
                                <img v-if="admin.profile_img" :src="`/organizational_structure/${admin.profile_img}`"
                                    :alt="admin.fname + ' ' + admin.lname"
                                    class="w-full h-full object-cover rounded-full" />
                                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                    No Image
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ admin.fname }} {{ admin.mid_initial }}. {{ admin.lname }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ admin.position }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="nonGroupData.length > 0" class="mb-8">
                <h2 class="text-2xl font-bold text-center mb-4">OTHERS</h2>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
                    <div v-for="member in nonGroupData" :key="member.id" class="group">
                        <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                            <div class="w-40 h-40 border border-black overflow-hidden rounded-full">
                                <img v-if="member.profile_img" :src="`/organizational_structure/${member.profile_img}`"
                                    :alt="member.fname + ' ' + member.lname"
                                    class="w-full h-full object-cover rounded-full" />
                                <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                    No Image
                                </div>
                            </div>
                            <div class="text-center">
                                <p class="text-sm font-semibold text-gray-800">
                                    {{ member.fname }} {{ member.mid_initial }}. {{ member.lname }}
                                </p>
                                <p class="text-xs text-gray-600">
                                    {{ member.position }} / {{ member.management_and_administrative_roles }}
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <hr class="my-12">
        <div class="mx-auto p-6">
            <div class="flex justify-center items-center mb-6">
                <img src="/img/dilg-main.png" class="rounded-full h-[200px] w-[200px] shadow-lg" alt="DILG Logo">
            </div>
            <h1 class="text-3xl font-bold text-center mb-12 uppercase">PROJECT DEVELOPMENT AND MANAGEMENT UNIT (PDMU)</h1>

            <div v-if="pdmuProvincialDirector" class="flex justify-center mb-8">
                <div class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-72 h-72 border border-black overflow-hidden rounded-full">
                            <img v-if="pdmuProvincialDirector.profile_img" :src="`/storage/${pdmuProvincialDirector.profile_img}`"
                                :alt="pdmuProvincialDirector.fname + ' ' + pdmuProvincialDirector.lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-xl font-semibold text-gray-800">
                                {{ pdmuProvincialDirector.fname }} {{ pdmuProvincialDirector.mid_initial }}. {{ pdmuProvincialDirector.lname }}
                            </p>
                            <p class="text-sm text-gray-600">
                                {{ pdmuProvincialDirector.position }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <div v-if="pdmus.length > 0" class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-8 mb-8">
                <div v-for="member in pdmus" :key="member.id" class="group">
                    <div class="flex flex-col items-center space-y-2 transform transition-transform duration-300 hover:scale-105">
                        <div class="w-40 h-40 border border-black overflow-hidden rounded-full">
                            <img v-if="member.profile_img" :src="`/storage/${member.profile_img}`"
                                :alt="member.fname + ' ' + member.lname"
                                class="w-full h-full object-cover rounded-full" />
                            <div v-else class="w-full h-full bg-gray-300 flex items-center justify-center text-gray-600 rounded-full">
                                No Image
                            </div>
                        </div>
                        <div class="text-center">
                            <p class="text-sm font-semibold text-gray-800">
                                {{ member.fname }} {{ member.mid_initial }}. {{ member.lname }}
                            </p>
                            <p class="text-xs text-gray-600">
                                {{ member.position }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import GuestLayout from "@/Layouts/GuestLayout.vue";

defineOptions({ layout: GuestLayout });

const props = defineProps({
    provincialDirector: Object,
    groupedData: Object,
    pdmuProvincialDirector: Object,
    pdmus: Array,
    nonGroupData: Array,
});
</script>

<style scoped>
.group:hover .transform {
    transform: scale(1.15);
}
</style>
