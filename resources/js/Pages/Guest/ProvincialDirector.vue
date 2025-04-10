<template>
    <div class="p-4">
        <div class="mx-auto">
            <div class="flex flex-col md:flex-row gap-4">
                <div class="w-full md:w-1/2 flex flex-col items-center">
                    <h1 class="text-3xl font-medium mb-4">The Provincial Director</h1>
                    <div class="transform hover:-rotate-3 transition duration-500 mb-4">
                        <img
                            v-if="pdinfo.profile_img"
                            :src="`/organizational_structure/${pdinfo.profile_img}`"
                            alt="Provincial Director"
                            class="shadow-lg border h-96 w-96 object-cover"
                        >
                        <div v-else class="shadow-lg border rounded-lg h-64 w-64 bg-gray-200 flex items-center justify-center">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-20 w-20 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                            </svg>
                        </div>
                    </div>
                    <p class="text-2xl font-medium">{{ pdinfo.fname }} {{ pdinfo.mid_initial }} {{ pdinfo.lname }}</p>
                    <p class="text-xl mb-2">{{ pdinfo.position }}</p>
                    <p class="text-center">
                        <img src="/img/dilg-main.png" class="h-10 w-10 inline-block" alt="DILG Logo">
                        Department of the Interior and Local Government - Bohol Province
                    </p>
                </div>

                <div class="w-full md:w-1/2 flex flex-col">
                    <h2 class="text-2xl text-center font-medium mb-4">MESSAGE OF THE PROVINCIAL DIRECTOR</h2>
                    <div class="text-justify indent-10 whitespace-normal break-words" v-if="pd">
                        <p v-html="pd.message.replace(/\n/g, '<br />')"></p>
                    </div>
                    <div v-else class="text-center text-gray-500 bg-white p-6 rounded-lg shadow border">
                        No message available
                    </div>
                </div>
            </div>
        </div>

        <div class="firework"></div>
        <div class="firework"></div>
        <div class="firework"></div>
    </div>
</template>

<script setup>
import GuestLayout from "../../Layouts/GuestLayout.vue"

defineOptions({ layout: GuestLayout });

const props = defineProps({
    pdinfo: Object,
    pd: Object
});
</script>

<style>
.firework,
.firework::before,
.firework::after {
    --initialSize: 0.5vmin;
    --finalSize: 45vmin;
    --particleSize: 0.2vmin;
    --color1: red;
    --color2: blue;
    --color3: black;
    --color4: violet;
    --color5: green;
    --color6: mediumseagreen;
    --y: -30vmin;
    --x: -50%;
    --initialY: 60vmin;
    content: "";
    animation: firework 2s infinite;
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, var(--y));
    width: var(--initialSize);
    aspect-ratio: 1;
    background:
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 50% 0%,
        radial-gradient(circle, var(--color2) var(--particleSize), #0000 0) 100% 50%,
        radial-gradient(circle, var(--color3) var(--particleSize), #0000 0) 50% 100%,
        radial-gradient(circle, var(--color4) var(--particleSize), #0000 0) 0% 50%,
        radial-gradient(circle, var(--color5) var(--particleSize), #0000 0) 80% 90%,
        radial-gradient(circle, var(--color6) var(--particleSize), #0000 0) 95% 90%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 90% 70%,
        radial-gradient(circle, var(--color2) var(--particleSize), #0000 0) 100% 60%,
        radial-gradient(circle, var(--color3) var(--particleSize), #0000 0) 55% 80%,
        radial-gradient(circle, var(--color4) var(--particleSize), #0000 0) 70% 77%,
        radial-gradient(circle, var(--color5) var(--particleSize), #0000 0) 22% 90%,
        radial-gradient(circle, var(--color6) var(--particleSize), #0000 0) 45% 90%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 33% 70%,
        radial-gradient(circle, var(--color2) var(--particleSize), #0000 0) 10% 60%,
        radial-gradient(circle, var(--color3) var(--particleSize), #0000 0) 31% 80%,
        radial-gradient(circle, var(--color4) var(--particleSize), #0000 0) 28% 77%,
        radial-gradient(circle, var(--color5) var(--particleSize), #0000 0) 13% 72%,
        radial-gradient(circle, var(--color6) var(--particleSize), #0000 0) 80% 10%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 95% 14%,
        radial-gradient(circle, var(--color2) var(--particleSize), #0000 0) 90% 23%,
        radial-gradient(circle, var(--color3) var(--particleSize), #0000 0) 100% 43%,
        radial-gradient(circle, var(--color4) var(--particleSize), #0000 0) 85% 27%,
        radial-gradient(circle, var(--color5) var(--particleSize), #0000 0) 77% 37%,
        radial-gradient(circle, var(--color6) var(--particleSize), #0000 0) 60% 7%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 22% 14%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 45% 20%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 33% 34%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 10% 29%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 31% 37%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 28% 7%,
        radial-gradient(circle, var(--color1) var(--particleSize), #0000 0) 13% 42%;
    background-size: var(--initialSize) var(--initialSize);
    background-repeat: no-repeat;
}

.firework::before {
    --x: -50%;
    --y: -50%;
    --initialY: -50%;
    transform: translate(-50%, -50%) rotate(40deg) scale(1.3) rotateY(40deg);
}

.firework::after {
    --x: -50%;
    --y: -50%;
    --initialY: -50%;
    transform: translate(-50%, -50%) rotate(170deg) scale(1.15) rotateY(-30deg);
}

.firework:nth-child(2) {
    --x: 30vmin;
}

.firework:nth-child(2),
.firework:nth-child(2)::before,
.firework:nth-child(2)::after {
    --color1: red;
    --color2: blue;
    --color3: violet;
    --color4: orchid;
    --color5: plum;
    --color6: lavender;
    --finalSize: 40vmin;
    left: 30%;
    top: 60%;
    animation-delay: -0.25s;
}

.firework:nth-child(3) {
    --x: -30vmin;
    --y: -50vmin;
}

@keyframes firework {
    0% {
        transform: translate(var(--x), var(--initialY));
        width: var(--initialSize);
        opacity: 1;
    }
    50% {
        width: 0.5vmin;
        opacity: 1;
    }
    100% {
        width: var(--finalSize);
        opacity: 0;
    }
}
</style>
