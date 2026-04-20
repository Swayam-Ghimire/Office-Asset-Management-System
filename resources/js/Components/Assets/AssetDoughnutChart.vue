<script setup>
import { computed } from "vue";
import { Doughnut } from "vue-chartjs";
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from "chart.js";

ChartJS.register(ArcElement, Tooltip, Legend);

const props = defineProps({
    chartData: { type: Object, required: true },
    title: { type: String, default: "Asset Status" },
});

// Total for the centre label
const total = computed(
    () => props.chartData.datasets[0]?.data?.reduce((a, b) => a + b, 0) ?? 0,
);

const chartOptions = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: "68%",
    plugins: {
        legend: {
            display: true,
            position: "bottom",
            labels: {
                boxWidth: 10,
                boxHeight: 10,
                borderRadius: 3,
                padding: 14,
                font: { size: 11 },
            },
        },
        tooltip: {
            callbacks: {
                label: (ctx) => ` ${ctx.label}: ${ctx.parsed}`,
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
        <div class="px-4 py-4 relative" style="height: 280px">
            <Doughnut :data="chartData" :options="chartOptions" />
            <!-- Centre label -->
            <div
                class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none"
                style="padding-bottom: 32px"
            >
                <p class="text-2xl font-bold text-gray-900">{{ total }}</p>
                <p class="text-xs text-gray-400">total assets</p>
            </div>
        </div>
    </div>
</template>
