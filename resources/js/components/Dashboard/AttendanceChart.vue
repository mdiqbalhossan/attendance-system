<!--
  AttendanceChart Component
  
  Displays monthly attendance data using Chart.js
  Shows trends for Present, Absent, Late, and Excused statuses
-->

<script setup lang="ts">
import { ref, onMounted, watch, computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@iconify/vue';
import type { MonthlyChartData } from '@/composables/useDashboard';
import {
    Chart,
    CategoryScale,
    LinearScale,
    BarElement,
    BarController,
    LineElement,
    LineController,
    PointElement,
    Title,
    Tooltip,
    Legend,
    type ChartConfiguration,
} from 'chart.js';

// Register Chart.js components and controllers
Chart.register(
    CategoryScale,
    LinearScale,
    BarElement,
    BarController,
    LineElement,
    LineController,
    PointElement,
    Title,
    Tooltip,
    Legend
);

interface Props {
    monthlyChartData: MonthlyChartData;
    currentMonth: string;
    currentYear: number;
    chartType?: 'bar' | 'line';
}

const props = withDefaults(defineProps<Props>(), {
    chartType: 'bar',
});

const chartCanvas = ref<HTMLCanvasElement | null>(null);
const chartInstance = ref<Chart | null>(null);
const selectedChartType = ref<'bar' | 'line'>(props.chartType);

// Compute monthly totals
const monthlyTotals = computed(() => props.monthlyChartData.monthlyTotals);
const totalAttendance = computed(() => 
    Object.values(monthlyTotals.value).reduce((sum, val) => sum + val, 0)
);

// Statistics cards for monthly totals
const monthlyStatsCards = computed(() => [
    {
        label: 'Present',
        value: monthlyTotals.value.present,
        percentage: totalAttendance.value > 0 
            ? ((monthlyTotals.value.present / totalAttendance.value) * 100).toFixed(1)
            : '0.0',
        icon: 'lucide:check-circle',
        colorClass: 'text-green-600 dark:text-green-400',
        bgClass: 'bg-green-50 dark:bg-green-950/30',
    },
    {
        label: 'Absent',
        value: monthlyTotals.value.absent,
        percentage: totalAttendance.value > 0 
            ? ((monthlyTotals.value.absent / totalAttendance.value) * 100).toFixed(1)
            : '0.0',
        icon: 'lucide:x-circle',
        colorClass: 'text-red-600 dark:text-red-400',
        bgClass: 'bg-red-50 dark:bg-red-950/30',
    },
    {
        label: 'Late',
        value: monthlyTotals.value.late,
        percentage: totalAttendance.value > 0 
            ? ((monthlyTotals.value.late / totalAttendance.value) * 100).toFixed(1)
            : '0.0',
        icon: 'lucide:clock',
        colorClass: 'text-amber-600 dark:text-amber-400',
        bgClass: 'bg-amber-50 dark:bg-amber-950/30',
    },
    {
        label: 'Excused',
        value: monthlyTotals.value.excused,
        percentage: totalAttendance.value > 0 
            ? ((monthlyTotals.value.excused / totalAttendance.value) * 100).toFixed(1)
            : '0.0',
        icon: 'lucide:info',
        colorClass: 'text-blue-600 dark:text-blue-400',
        bgClass: 'bg-blue-50 dark:bg-blue-950/30',
    },
]);

/**
 * Initialize the chart
 */
const initChart = () => {
    if (!chartCanvas.value) return;

    // Destroy existing chart if it exists
    if (chartInstance.value) {
        chartInstance.value.destroy();
    }

    const ctx = chartCanvas.value.getContext('2d');
    if (!ctx) return;

    // Check for dark mode
    const isDark = document.documentElement.classList.contains('dark');
    const textColor = isDark ? '#e5e7eb' : '#374151';
    const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';

    const config: ChartConfiguration = {
        type: selectedChartType.value,
        data: {
            labels: props.monthlyChartData.labels,
            datasets: props.monthlyChartData.datasets,
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: {
                mode: 'index',
                intersect: false,
            },
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: textColor,
                        usePointStyle: true,
                        padding: 15,
                    },
                },
                title: {
                    display: false,
                },
                tooltip: {
                    backgroundColor: isDark ? 'rgba(0, 0, 0, 0.8)' : 'rgba(255, 255, 255, 0.9)',
                    titleColor: textColor,
                    bodyColor: textColor,
                    borderColor: gridColor,
                    borderWidth: 1,
                    padding: 12,
                    displayColors: true,
                    callbacks: {
                        footer: (tooltipItems) => {
                            const total = tooltipItems.reduce((sum, item) => sum + (item.parsed.y || 0), 0);
                            return `Total: ${total}`;
                        },
                    },
                },
            },
            scales: {
                x: {
                    stacked: false,
                    grid: {
                        color: gridColor,
                        display: false,
                    },
                    ticks: {
                        color: textColor,
                    },
                },
                y: {
                    stacked: false,
                    beginAtZero: true,
                    grid: {
                        color: gridColor,
                    },
                    ticks: {
                        color: textColor,
                        precision: 0,
                    },
                },
            },
        },
    };

    chartInstance.value = new Chart(ctx, config);
};

/**
 * Toggle chart type
 */
const toggleChartType = (type: 'bar' | 'line') => {
    selectedChartType.value = type;
    initChart();
};

// Initialize chart on mount
onMounted(() => {
    initChart();
    
    // Watch for theme changes
    const observer = new MutationObserver(() => {
        initChart();
    });
    
    observer.observe(document.documentElement, {
        attributes: true,
        attributeFilter: ['class'],
    });
});

// Re-initialize chart when data changes
watch(() => props.monthlyChartData, () => {
    initChart();
}, { deep: true });
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle>Monthly Attendance Trend</CardTitle>
                    <CardDescription>
                        {{ currentMonth }} {{ currentYear }}
                    </CardDescription>
                </div>
                
                <!-- Chart Type Toggles -->
                <div class="flex gap-1 rounded-lg border border-border p-1">
                    <button
                        type="button"
                        :class="[
                            'rounded px-3 py-1.5 text-xs font-medium transition-colors',
                            selectedChartType === 'bar'
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground'
                        ]"
                        @click="toggleChartType('bar')"
                    >
                        <Icon icon="lucide:bar-chart-3" class="h-4 w-4" />
                    </button>
                    <button
                        type="button"
                        :class="[
                            'rounded px-3 py-1.5 text-xs font-medium transition-colors',
                            selectedChartType === 'line'
                                ? 'bg-primary text-primary-foreground'
                                : 'text-muted-foreground hover:text-foreground'
                        ]"
                        @click="toggleChartType('line')"
                    >
                        <Icon icon="lucide:line-chart" class="h-4 w-4" />
                    </button>
                </div>
            </div>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Chart Canvas -->
            <div class="relative h-[300px] w-full">
                <canvas ref="chartCanvas"></canvas>
            </div>

            <!-- Monthly Statistics Summary -->
            <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
                <div
                    v-for="stat in monthlyStatsCards"
                    :key="stat.label"
                    :class="stat.bgClass"
                    class="rounded-lg p-3"
                >
                    <div class="flex items-center gap-2">
                        <Icon
                            :icon="stat.icon"
                            :class="stat.colorClass"
                            class="h-4 w-4"
                        />
                        <span class="text-xs font-medium text-muted-foreground">
                            {{ stat.label }}
                        </span>
                    </div>
                    <div class="mt-1 flex items-baseline gap-2">
                        <span class="text-xl font-bold" :class="stat.colorClass">
                            {{ stat.value }}
                        </span>
                        <span class="text-xs text-muted-foreground">
                            {{ stat.percentage }}%
                        </span>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

