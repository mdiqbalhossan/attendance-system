<script setup lang="ts">
/**
 * Dashboard Page
 * 
 * Displays attendance statistics, charts, and recent activity
 * 
 * Features:
 * - Today's attendance summary with color-coded statistics
 * - Monthly attendance chart (Chart.js) with bar/line toggle
 * - Overall statistics cards
 * - Recent attendance activity feed
 */

import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { dashboard } from '@/routes';
import { type BreadcrumbItem } from '@/types';
import { 
    useDashboard, 
    type TodayStats, 
    type MonthlyChartData,
    type OverallStats,
    type RecentActivity 
} from '@/composables/useDashboard';

// Components
import StatCard from '@/Components/Dashboard/StatCard.vue';
import AttendanceSummaryCard from '@/Components/Dashboard/AttendanceSummaryCard.vue';
import AttendanceChart from '@/Components/Dashboard/AttendanceChart.vue';
import RecentActivityCard from '@/Components/Dashboard/RecentActivity.vue';

// Props from controller
interface Props {
    todayStats: TodayStats;
    monthlyChartData: MonthlyChartData;
    overallStats: OverallStats;
    recentActivity: RecentActivity[];
    currentMonth: string;
    currentYear: number;
}

const props = defineProps<Props>();

// Breadcrumb navigation
const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: dashboard().url,
    },
];

// Use dashboard composable for computed properties and utilities
const { todayStatsCards, overallStatsCards } = useDashboard(
    props.todayStats,
    props.overallStats
);
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto p-4 md:p-6">
            <!-- Page Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                <p class="text-muted-foreground">
                    Welcome back! Here's an overview of today's attendance.
                </p>
            </div>

            <!-- Today's Quick Stats - Top Cards -->
            <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
                <StatCard
                    v-for="card in todayStatsCards"
                    :key="card.title"
                    :title="card.title"
                    :value="card.value"
                    :icon="card.icon"
                    :color="card.color"
                    :percentage="card.percentage"
                />
            </div>

            <!-- Main Content Grid -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Left Column - 2/3 width -->
                <div class="space-y-6 lg:col-span-2">
                    <!-- Today's Attendance Summary -->
                    <AttendanceSummaryCard :today-stats="todayStats" />

                    <!-- Monthly Attendance Chart -->
                    <AttendanceChart
                        :monthly-chart-data="monthlyChartData"
                        :current-month="currentMonth"
                        :current-year="currentYear"
                    />
                </div>

                <!-- Right Column - 1/3 width -->
                <div class="space-y-6">
                    <!-- Overall Statistics -->
                    <div class="space-y-4">
                        <StatCard
                            v-for="card in overallStatsCards"
                            :key="card.title"
                            :title="card.title"
                            :value="card.value"
                            :icon="card.icon"
                            :description="card.description"
                            :color="card.color"
                        />
                    </div>

                    <!-- Recent Activity -->
                    <RecentActivityCard :activities="recentActivity" />
                </div>
            </div>
        </div>
    </AppLayout>
</template>
