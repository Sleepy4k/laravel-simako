<script setup lang="ts">
import { computed } from 'vue'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js'
import { Bar } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, BarElement, Title, Tooltip, Legend)

const props = defineProps<{
    labels: string[]
    values: number[]
    label?: string
    color?: string
}>()

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            label: props.label ?? 'Data',
            data: props.values,
            backgroundColor: (props.color ?? '#e11d48') + 'cc',
            borderRadius: 6,
            borderSkipped: false,
        },
    ],
}))

const options = {
    responsive: true,
    maintainAspectRatio: false,
    plugins: {
        legend: { display: false },
        tooltip: {
            backgroundColor: '#0f0f0f',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            padding: 10,
            cornerRadius: 8,
        },
    },
    scales: {
        x: {
            grid: { display: false },
            ticks: { color: '#9ca3af', font: { size: 11 } },
        },
        y: {
            grid: { color: '#f3f4f6' },
            ticks: { color: '#9ca3af', font: { size: 11 } },
        },
    },
}
</script>

<template>
    <div class="relative h-full min-h-[200px]">
        <Bar :data="chartData" :options="options" />
    </div>
</template>
