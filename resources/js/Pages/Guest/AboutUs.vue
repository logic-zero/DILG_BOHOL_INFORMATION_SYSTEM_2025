<script setup>
import GuestLayout from "../../Layouts/GuestLayout.vue";
import { onMounted, onUnmounted } from 'vue';

defineOptions({ layout: GuestLayout });
const props = defineProps({
    audio: Object
});

let audio = null;

onMounted(() => {
    if (props.audio) {
        audio = new Audio('/audios/' + props.audio.file);
        audio.volume = 1;
        audio.loop = true;
        audio.play().catch(e => console.log('Autoplay prevented:', e));
    }
});

onUnmounted(() => {
    if (audio) {
        audio.pause();
        audio.currentTime = 0;
        audio = null;
    }
});
</script>

<template>
    <div class="p-2 lg:p-6 w-full">
        <div class="text-center mb-6">
            <img src="/img/Background.png" class="max-w-full h-auto mx-auto" alt="Background Image">
        </div>
        <h1 class="text-xl bg-blue-800 text-white p-2 font-bold text-center mb-6 uppercase">
            About Us
        </h1>
        <div class="container mx-auto px-4 mt-3">
            <ul class="list-disc list-inside space-y-3">
                <li class="flex items-start">
                    <span class="fas fa-arrow-right mr-3 text-[#002c76]"></span>
                    The Department of the Interior and Local Government is a dynamic government agency mandated by law to assist the President in the exercise of general supervision over local governments (Local Government Code of 1991).
                </li>
                <li class="flex items-start">
                    <span class="fas fa-arrow-right mr-3 text-[#002c76]"></span>
                    The Department was created under Republic Act No. 6975, otherwise known as the DILG Act of 1990, in order to promote peace and order, ensure public safety and further strengthen local government capability aimed towards the effective delivery of basic services to the citizenry. The operating offices and bureaus responsible for the peace and order and public safety functions of the Department are the National Police Commission (NAPOLCOM), Philippine National Police (PNP), Bureau of Fire Protection (BFP), Bureau of Jail Management and Penology (BJMP) and the Philippine Public Safety College (PPSC). The Department is headed by the Secretary who is also the ex-officio chairman of the NAPOLCOM. DILG has established regional offices throughout the country to implement the policies and programs of the Department. It continues to work closely with local government units (LGUs), national government agencies (NGAs), and civil society, including non-government organizations (NGOs) and people's organizations (POs).
                </li>
            </ul>
        </div>
        <div class="container mx-auto px-4 mt-8">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="flex justify-center items-center">
                    <div class="flex flex-col items-center text-center">
                        <img src="/img/goal.png" class="h-24 mb-4" alt="Mission Icon">
                        <h2 class="text-xl font-semibold">Our Mission</h2>
                        <p class="text-justify">The Department shall ensure peace and order, public safety and security, uphold excellence in local governance and enable resilient and inclusive communities.</p>
                    </div>
                </div>
                <div class="flex justify-center items-center">
                    <div class="flex flex-col items-center text-center">
                        <img src="/img/lightbulb.png" class="h-24 mb-4" alt="Vision Icon">
                        <h2 class="text-xl font-semibold">Our Vision</h2>
                        <p class="text-justify">A highly trusted Department and Partner in nurturing local governments and sustaining peaceful, safe, progressive, resilient, and inclusive communities towards a comfortable and secure life for Filipinos by 2040.</p>
                    </div>
                </div>
            </div>
        </div>
        <div class="container mx-auto px-4 mt-8">
            <div class="flex justify-center">
                <div class="text-center">
                    <img src="/img/star.png" class="h-24 mb-4 mx-auto" alt="Shared Values Icon">
                    <h2 class="text-xl font-semibold">Our Shared Values</h2>
                    <p>Ang DILG ay Matino, Mahusay, at Maasahan</p>
                </div>
            </div>
        </div>
    </div>
</template>
