<script setup>
import { onMounted, onUnmounted, ref, watch } from 'vue';
import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

const props = defineProps({
    type: { type: String, default: 'line' },
    data: { type: Object, required: true },
    options: { type: Object, default: () => ({}) },
    height: { type: Number, default: 300 }
});

const canvasRef = ref(null);
let chartInstance = null;

const initChart = () => {
    if (chartInstance) {
        chartInstance.destroy();
    }

    if (!canvasRef.value) return;

    const ctx = canvasRef.value.getContext('2d');
    
    // Global defaults for the library's glassmorphism style
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#94a3b8' : '#475569';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.05)' : 'rgba(0, 0, 0, 0.05)';

    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: false, // Default to false, can be overridden in props
                labels: { color: textColor, font: { family: 'Outfit', weight: 'bold', size: 10 } }
            },
            tooltip: {
                backgroundColor: isDark ? '#0f172a' : '#ffffff',
                titleColor: isDark ? '#ffffff' : '#0f172a',
                bodyColor: isDark ? '#94a3b8' : '#475569',
                borderColor: isDark ? 'rgba(255,255,255,0.1)' : 'rgba(0,0,0,0.1)',
                borderWidth: 1,
                padding: 12,
                displayColors: true,
                usePointStyle: true,
            }
        },
        scales: props.type === 'doughnut' || props.type === 'pie' ? {} : {
            x: {
                grid: { display: false },
                ticks: { color: textColor, font: { family: 'Outfit', weight: 'bold', size: 10 } }
            },
            y: {
                grid: { color: gridColor },
                ticks: { color: textColor, font: { family: 'Outfit', weight: 'bold', size: 10 } }
            }
        }
    };

    chartInstance = new Chart(ctx, {
        type: props.type,
        data: props.data,
        options: { ...defaultOptions, ...props.options }
    });
};

onMounted(initChart);
onUnmounted(() => {
    if (chartInstance) chartInstance.destroy();
});

watch(() => props.data, initChart, { deep: true });
watch(() => props.options, initChart, { deep: true });

// Optional: Watch for theme changes if needed
const observer = new MutationObserver((mutations) => {
    mutations.forEach((mutation) => {
        if (mutation.attributeName === 'class') {
            initChart();
        }
    });
});

onMounted(() => {
    observer.observe(document.documentElement, { attributes: true });
});

onUnmounted(() => {
    observer.disconnect();
});
</script>

<template>
    <div :style="{ height: height + 'px' }" class="relative w-full">
        <canvas ref="canvasRef"></canvas>
    </div>
</template>
