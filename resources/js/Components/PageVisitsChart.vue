<script setup>
import { onMounted } from 'vue';
import Chart from 'chart.js/auto';

const props = defineProps({
    labels: Array,
    data: Array,
});

onMounted(() => {
    const ctx = document.getElementById('pageVisitsChart');
    new Chart(ctx, {
        type: "line",
        data: {
            labels: props.labels,
            datasets: [{
                fill: false,
                lineTension: 0,
                backgroundColor: "rgba(79, 70, 229, 1.0)",
                borderColor: "rgba(79, 70, 229, 0.5)",
                data: props.data
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    display: false
                }
            },
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
});
</script>

<template>
    <canvas id="pageVisitsChart"></canvas>
</template>

<style scoped>
@import "https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.9.4/Chart.css";
</style>
