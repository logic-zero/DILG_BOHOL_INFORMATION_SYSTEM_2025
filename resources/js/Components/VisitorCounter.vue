<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const totalVisits = ref(0);

const fetchVisitCount = async () => {
    try {
        const response = await axios.get('/visit-count');
        totalVisits.value = response.data.total_visits;
    } catch (error) {
        console.error('Error fetching visit count', error);
    }
};

const trackVisit = async () => {
    try {
        await axios.post('/track-visit'); // Log visit
    } catch (error) {
        console.error('Error tracking visit', error);
    }
};

onMounted(() => {
    trackVisit();  // Log the visit
    fetchVisitCount();  // Fetch updated count
});
</script>

<template>
    <div class="border border-black bg-white bg-opacity-50 text-black p-4 rounded text-center font-light">
    <p class="text-xs font-bold tracking-wide uppercase">Total Visits: {{ totalVisits }}</p>
</div>
</template>
