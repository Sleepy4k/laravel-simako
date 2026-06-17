<script setup lang="ts">
import { computed } from 'vue'
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    PointElement,
    LineElement,
    Title,
    Tooltip,
    Legend,
    Filler,
} from 'chart.js'
import { Line } from 'vue-chartjs'

ChartJS.register(CategoryScale, LinearScale, PointElement, LineElement, Title, Tooltip, Legend, Filler)

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
            borderColor: props.color ?? '#e11d48',
            backgroundColor: (props.color ?? '#e11d48') + '20',
            borderWidth: 2,
            pointRadius: 3,
            pointHoverRadius: 5,
            fill: true,
            tension: 0.4,
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
        <Line :data="chartData" :options="options" />
    </div>
</template>
