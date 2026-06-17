<script setup lang="ts">
import { computed } from 'vue'
import { Chart as ChartJS, ArcElement, Tooltip, Legend } from 'chart.js'
import { Doughnut } from 'vue-chartjs'

ChartJS.register(ArcElement, Tooltip, Legend)

const props = defineProps<{
    labels: string[]
    values: number[]
    colors?: string[]
}>()

const DEFAULT_COLORS = ['#e11d48', '#10b981', '#f59e0b', '#3b82f6', '#8b5cf6']

const chartData = computed(() => ({
    labels: props.labels,
    datasets: [
        {
            data: props.values,
            backgroundColor: props.colors ?? DEFAULT_COLORS.slice(0, props.values.length),
            borderWidth: 0,
            hoverOffset: 4,
        },
    ],
}))

const options = {
    responsive: true,
    maintainAspectRatio: false,
    cutout: '70%',
    plugins: {
        legend: {
            position: 'bottom' as const,
            labels: {
                color: '#6b7280',
                font: { size: 12 },
                padding: 16,
                usePointStyle: true,
                pointStyleWidth: 8,
            },
        },
        tooltip: {
            backgroundColor: '#0f0f0f',
            titleColor: '#fff',
            bodyColor: '#d1d5db',
            padding: 10,
            cornerRadius: 8,
        },
    },
}
</script>

<template>
    <div class="relative h-full min-h-[200px]">
        <Doughnut :data="chartData" :options="options" />
    </div>
</template>
