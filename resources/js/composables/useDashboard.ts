/**
 * Dashboard composable
 * 
 * Handles dashboard data, statistics, and formatting
 */

import { computed, type ComputedRef } from 'vue';

// Types
export interface TodayStats {
    date: string;
    dateFormatted: string;
    totalStudents: number;
    present: number;
    absent: number;
    late: number;
    excused: number;
    notRecorded: number;
    totalRecorded: number;
    attendanceRate: number;
}

export interface MonthlyChartData {
    labels: string[];
    datasets: ChartDataset[];
    monthlyTotals: {
        present: number;
        absent: number;
        late: number;
        excused: number;
    };
}

export interface ChartDataset {
    label: string;
    data: number[];
    backgroundColor: string;
    borderColor: string;
    borderWidth: number;
}

export interface OverallStats {
    totalStudents: number;
    totalRecords: number;
    weeklyAttendanceRate: number;
    monthlyAttendanceRate: number;
    weeklyStats: {
        present: number;
        absent: number;
        late: number;
        excused: number;
    };
}

export interface RecentActivity {
    id: number;
    student_name: string;
    student_id: string;
    status: string;
    date: string;
    recorded_by: string;
    recorded_at: string;
}

/**
 * Composable for dashboard functionality
 */
export function useDashboard(
    todayStats: TodayStats,
    overallStats: OverallStats
) {
    /**
     * Get status configuration (color, icon, label)
     */
    const getStatusConfig = (status: string) => {
        const configs: Record<string, { color: string; icon: string; label: string }> = {
            present: {
                color: 'text-green-600 dark:text-green-400',
                icon: 'lucide:check-circle',
                label: 'Present',
            },
            absent: {
                color: 'text-red-600 dark:text-red-400',
                icon: 'lucide:x-circle',
                label: 'Absent',
            },
            late: {
                color: 'text-amber-600 dark:text-amber-400',
                icon: 'lucide:clock',
                label: 'Late',
            },
            excused: {
                color: 'text-blue-600 dark:text-blue-400',
                icon: 'lucide:info',
                label: 'Excused',
            },
        };

        return configs[status] || configs.present;
    };

    /**
     * Get percentage of total
     */
    const getPercentage = (value: number, total: number): number => {
        if (total === 0) return 0;
        return Math.round((value / total) * 100);
    };

    /**
     * Format number with commas
     */
    const formatNumber = (num: number): string => {
        return num.toLocaleString();
    };

    /**
     * Get trend indicator (up/down/neutral)
     */
    const getTrendIndicator = (current: number, previous: number): 'up' | 'down' | 'neutral' => {
        if (current > previous) return 'up';
        if (current < previous) return 'down';
        return 'neutral';
    };

    /**
     * Get attendance rate status (good/warning/bad)
     */
    const getAttendanceRateStatus = (rate: number): 'good' | 'warning' | 'bad' => {
        if (rate >= 90) return 'good';
        if (rate >= 75) return 'warning';
        return 'bad';
    };

    /**
     * Computed properties for today's stats
     */
    const todayStatsCards: ComputedRef<Array<{
        title: string;
        value: number;
        icon: string;
        color: string;
        percentage?: number;
    }>> = computed(() => [
        {
            title: 'Present',
            value: todayStats.present,
            icon: 'lucide:check-circle',
            color: 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/30',
            percentage: getPercentage(todayStats.present, todayStats.totalStudents),
        },
        {
            title: 'Absent',
            value: todayStats.absent,
            icon: 'lucide:x-circle',
            color: 'text-red-600 dark:text-red-400 bg-red-50 dark:bg-red-950/30',
            percentage: getPercentage(todayStats.absent, todayStats.totalStudents),
        },
        {
            title: 'Late',
            value: todayStats.late,
            icon: 'lucide:clock',
            color: 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30',
            percentage: getPercentage(todayStats.late, todayStats.totalStudents),
        },
        {
            title: 'Excused',
            value: todayStats.excused,
            icon: 'lucide:info',
            color: 'text-blue-600 dark:text-blue-400 bg-blue-50 dark:bg-blue-950/30',
            percentage: getPercentage(todayStats.excused, todayStats.totalStudents),
        },
    ]);

    /**
     * Computed properties for overall stats cards
     */
    const overallStatsCards: ComputedRef<Array<{
        title: string;
        value: number | string;
        icon: string;
        description: string;
        color: string;
    }>> = computed(() => [
        {
            title: 'Total Students',
            value: formatNumber(overallStats.totalStudents),
            icon: 'lucide:users',
            description: 'Enrolled students',
            color: 'text-purple-600 dark:text-purple-400 bg-purple-50 dark:bg-purple-950/30',
        },
        {
            title: 'Weekly Attendance',
            value: `${overallStats.weeklyAttendanceRate}%`,
            icon: 'lucide:calendar-days',
            description: 'This week',
            color: getAttendanceRateStatus(overallStats.weeklyAttendanceRate) === 'good'
                ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/30'
                : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30',
        },
        {
            title: 'Monthly Attendance',
            value: `${overallStats.monthlyAttendanceRate}%`,
            icon: 'lucide:calendar',
            description: 'This month',
            color: getAttendanceRateStatus(overallStats.monthlyAttendanceRate) === 'good'
                ? 'text-green-600 dark:text-green-400 bg-green-50 dark:bg-green-950/30'
                : 'text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-950/30',
        },
    ]);

    return {
        // Methods
        getStatusConfig,
        getPercentage,
        formatNumber,
        getTrendIndicator,
        getAttendanceRateStatus,
        
        // Computed
        todayStatsCards,
        overallStatsCards,
    };
}

