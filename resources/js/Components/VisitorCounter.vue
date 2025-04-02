<script setup>
import { ref, onMounted } from 'vue';
import axios from 'axios';

const totalVisits = ref(0);
const todaysVisits = ref(0);
const yesterdaysVisits = ref(0);

const fetchVisitCount = async () => {
    try {
        const response = await axios.get('/visit-count');
        totalVisits.value = response.data.total_visits;
        todaysVisits.value = response.data.todays_visits;
        yesterdaysVisits.value = response.data.yesterdays_visits;
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
    <div class="border border-gray-500 bg-white bg-opacity-50 text-black p-2 rounded text-center font-light">
        <p class="text-xs font-bold tracking-wide uppercase">Page Visit Counter</p>
        <table class="mt-2 w-full">
            <tr>
                <td class="text-xs uppercase pr-2 text-left">Today:</td>
                <td class="text-xs text-right">{{ todaysVisits }}</td>
            </tr>
            <tr>
                <td class="text-xs uppercase pr-2 text-left">Yesterday:</td>
                <td class="text-xs text-right">{{ yesterdaysVisits }}</td>
            </tr>
            <tr>
                <td class="text-xs uppercase pr-2 text-left">All:</td>
                <td class="text-xs text-right">{{ totalVisits }}</td>
            </tr>
        </table>
    </div>
</template>
