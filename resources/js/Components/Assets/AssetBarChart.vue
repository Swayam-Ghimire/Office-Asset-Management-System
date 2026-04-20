<script setup>

import { Bar } from "vue-chartjs";
import {
    Chart as ChartJS,
    Title,
    Tooltip,
    Legend,
    BarElement,
    CategoryScale,
    LinearScale,
} from "chart.js";

ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
);

defineProps({
    chartData: { type: Object, required: true },
    title: { type: String, default: "Assets by Category" },
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false, // critical — lets the parent div control height
    plugins: {
        legend: { display: false }, // single dataset — no legend needed
        tooltip: {
            callbacks: {
                label: (ctx) =>
                    ` ${ctx.parsed.y} asset${ctx.parsed.y !== 1 ? "s" : ""}`,
            },
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { font: { size: 11 } },
        },
        y: {
            beginAtZero: true,
            ticks: {
                precision: 0,
                font: { size: 11 },
            },
            grid: {
                color: "rgba(0,0,0,0.05)",
            },
        },
    },
};
</script>

<template>
    <div
        class="bg-white rounded-xl border border-gray-100 shadow-sm overflow-hidden"
    >
        <div class="px-6 py-4 border-b border-gray-100">
            <h2 class="font-semibold text-gray-900 text-sm">{{ title }}</h2>
        </div>
        <!-- Fixed pixel height on this div is what Chart.js measures -->
        <div class="px-4 py-4" style="height: 280px">
            <Bar :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
