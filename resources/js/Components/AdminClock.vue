<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const time = ref('');
const date = ref('');
const day = ref('');
const ampm = ref('');

const fetchTime = async () => {
    try {
        const response = await fetch('https://worldtimeapi.org/api/timezone/Asia/Manila');
        const data = await response.json();
        updateClock(new Date(data.datetime));
    } catch (error) {
        console.error('Failed to fetch time:', error);
    }
};

const updateClock = (dateObj) => {
    // Format Time (12-hour format without seconds)
    const hours = dateObj.getHours();
    const minutes = dateObj.getMinutes();
    ampm.value = hours >= 12 ? 'PM' : 'AM';
    const formattedHours = hours % 12 || 12; // Convert to 12-hour format
    time.value = `${formattedHours}:${minutes.toString().padStart(2, '0')}`;

    // Format Day (e.g. Monday)
    const dayOptions = { weekday: 'long' };
    day.value = dateObj.toLocaleDateString('en-US', dayOptions);

    // Format Date (Month dd, yyyy)
    const dateOptions = { year: 'numeric', month: 'long', day: 'numeric' };
    date.value = dateObj.toLocaleDateString('en-US', dateOptions);
};

let interval = null;

onMounted(async () => {
    await fetchTime(); // Fetch initial time
    interval = setInterval(() => {
        updateClock(new Date()); // Update every second
    }, 1000);
});

onUnmounted(() => {
    clearInterval(interval);
});
</script>

<template>
    <div class="flex flex-col">
        <div class="flex items-end gap-1">
            <p class="text-xs uppercase text-gray-600">Philippines</p>
            <p class="text-sm font-bold">{{ time }}</p>
            <p class="text-xs text-gray-500">{{ ampm }}</p>
        </div>

        <div class="flex gap-1">
            <p class="text-xs font-semibold text-blue-950">{{ day }}</p>
            <p class="text-xs uppercase">{{ date }}</p>
        </div>
    </div>
</template>
