<script setup>
import { Line } from "vue-chartjs";
import {
    Chart as ChartJS,
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
    Legend,
} from "chart.js";

ChartJS.register(
    LineElement,
    PointElement,
    LinearScale,
    CategoryScale,
    Filler,
    Tooltip,
    Legend,
);

defineProps({
    chartData: { type: Object, required: true },
    title: { type: String, default: "Request Trend (6 months)" },
});

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            callbacks: {
                label: (ctx) =>
                    ` ${ctx.parsed.y} request${ctx.parsed.y !== 1 ? "s" : ""}`,
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
            grid: { color: "rgba(0,0,0,0.05)" },
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
        <div class="px-4 py-4" style="height: 220px">
            <Line :data="chartData" :options="chartOptions" />
        </div>
    </div>
</template>
