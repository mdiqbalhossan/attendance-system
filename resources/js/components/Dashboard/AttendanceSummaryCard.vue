<!--
  AttendanceSummaryCard Component
  
  Displays today's attendance summary with color-coded statistics
-->

<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@iconify/vue';
import { Badge } from '@/components/ui/badge';
import type { TodayStats } from '@/composables/useDashboard';

interface Props {
    todayStats: TodayStats;
}

const props = defineProps<Props>();

// Status configurations with colors
const statusItems = [
    {
        label: 'Present',
        value: props.todayStats.present,
        icon: 'lucide:check-circle',
        colorClass: 'text-green-600 dark:text-green-400',
        bgClass: 'bg-green-50 dark:bg-green-950/30',
        borderClass: 'border-green-200 dark:border-green-800',
    },
    {
        label: 'Absent',
        value: props.todayStats.absent,
        icon: 'lucide:x-circle',
        colorClass: 'text-red-600 dark:text-red-400',
        bgClass: 'bg-red-50 dark:bg-red-950/30',
        borderClass: 'border-red-200 dark:border-red-800',
    },
    {
        label: 'Late',
        value: props.todayStats.late,
        icon: 'lucide:clock',
        colorClass: 'text-amber-600 dark:text-amber-400',
        bgClass: 'bg-amber-50 dark:bg-amber-950/30',
        borderClass: 'border-amber-200 dark:border-amber-800',
    },
    {
        label: 'Excused',
        value: props.todayStats.excused,
        icon: 'lucide:info',
        colorClass: 'text-blue-600 dark:text-blue-400',
        bgClass: 'bg-blue-50 dark:bg-blue-950/30',
        borderClass: 'border-blue-200 dark:border-blue-800',
    },
];

// Get attendance rate status
const attendanceRateStatus = computed(() => {
    const rate = props.todayStats.attendanceRate;
    if (rate >= 90) return { label: 'Excellent', color: 'bg-green-500' };
    if (rate >= 75) return { label: 'Good', color: 'bg-amber-500' };
    return { label: 'Needs Attention', color: 'bg-red-500' };
});
</script>

<template>
    <Card>
        <CardHeader>
            <div class="flex items-center justify-between">
                <div>
                    <CardTitle>Today's Attendance</CardTitle>
                    <CardDescription>
                        {{ todayStats.dateFormatted }}
                    </CardDescription>
                </div>
                <Badge :class="attendanceRateStatus.color" class="text-white">
                    {{ todayStats.attendanceRate }}% {{ attendanceRateStatus.label }}
                </Badge>
            </div>
        </CardHeader>
        <CardContent class="space-y-4">
            <!-- Overall Progress -->
            <div class="space-y-2">
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Total Students</span>
                    <span class="font-semibold">{{ todayStats.totalStudents }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Recorded</span>
                    <span class="font-semibold">{{ todayStats.totalRecorded }}</span>
                </div>
                <div class="flex items-center justify-between text-sm">
                    <span class="text-muted-foreground">Not Recorded</span>
                    <span class="font-semibold">{{ todayStats.notRecorded }}</span>
                </div>
                
                <!-- Progress Bar -->
                <div class="mt-2 h-2 w-full overflow-hidden rounded-full bg-muted">
                    <div
                        class="h-full bg-primary transition-all"
                        :style="{ width: `${(todayStats.totalRecorded / todayStats.totalStudents) * 100}%` }"
                    ></div>
                </div>
            </div>

            <!-- Status Breakdown -->
            <div class="grid grid-cols-2 gap-3">
                <div
                    v-for="item in statusItems"
                    :key="item.label"
                    :class="[item.bgClass, item.borderClass]"
                    class="rounded-lg border p-3 transition-all hover:shadow-sm"
                >
                    <div class="flex items-center gap-2">
                        <Icon
                            :icon="item.icon"
                            :class="item.colorClass"
                            class="h-5 w-5"
                        />
                        <span class="text-sm font-medium text-foreground">
                            {{ item.label }}
                        </span>
                    </div>
                    <div class="mt-1 text-2xl font-bold" :class="item.colorClass">
                        {{ item.value }}
                    </div>
                    <div class="text-xs text-muted-foreground">
                        {{ ((item.value / todayStats.totalStudents) * 100).toFixed(1) }}% of total
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

