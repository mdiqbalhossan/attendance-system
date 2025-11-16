<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref } from 'vue';

// ========================================
// Types & Interfaces
// ========================================

interface Student {
    id: number;
    name: string;
    student_id: string;
    class: string;
    section: string;
}

interface StudentStat {
    student: Student;
    total_days: number;
    present: number;
    absent: number;
    late: number;
    attendance_rate: number;
}

interface Stats {
    total_records: number;
    present_count: number;
    absent_count: number;
    late_count: number;
    unique_students: number;
    working_days: number;
    attendance_rate: number;
    student_stats: StudentStat[];
}

interface Props {
    report: {
        stats: Stats;
        month: number;
        year: number;
        class: string | null;
        section: string | null;
    };
    filters: {
        month: number;
        year: number;
        class?: string;
        section?: string;
    };
    classes: string[];
    sections: string[];
}

// ========================================
// Component Setup
// ========================================

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Attendance',
        href: '/attendance',
    },
    {
        title: 'Monthly Report',
        href: '/attendance/reports/monthly',
    },
];

// ========================================
// State
// ========================================

const selectedMonth = ref(props.filters.month);
const selectedYear = ref(props.filters.year);
const selectedClass = ref(props.filters.class || '');
const selectedSection = ref(props.filters.section || '');

// ========================================
// Data
// ========================================

const months = [
    { value: 1, label: 'January' },
    { value: 2, label: 'February' },
    { value: 3, label: 'March' },
    { value: 4, label: 'April' },
    { value: 5, label: 'May' },
    { value: 6, label: 'June' },
    { value: 7, label: 'July' },
    { value: 8, label: 'August' },
    { value: 9, label: 'September' },
    { value: 10, label: 'October' },
    { value: 11, label: 'November' },
    { value: 12, label: 'December' },
];

const years = Array.from({ length: 10 }, (_, i) => new Date().getFullYear() - 5 + i);

// ========================================
// Methods
// ========================================

const applyFilters = () => {
    router.get('/attendance/reports/monthly', {
        month: selectedMonth.value,
        year: selectedYear.value,
        class: selectedClass.value || undefined,
        section: selectedSection.value || undefined,
    }, {
        preserveState: false,
    });
};

const getAttendanceRateColor = (rate: number): string => {
    if (rate >= 90) return 'text-green-600';
    if (rate >= 75) return 'text-yellow-600';
    return 'text-red-600';
};

const exportReport = () => {
    // You can implement CSV/PDF export here
    alert('Export functionality can be implemented as needed');
};
</script>

<template>
    <Head title="Monthly Attendance Report" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Monthly Attendance Report</h1>
                    <p class="text-sm text-muted-foreground">
                        View detailed attendance statistics for {{ months.find(m => m.value === report.month)?.label }} {{ report.year }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" @click="exportReport">
                        Export Report
                    </Button>
                </div>
            </div>

            <!-- Filters -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-wrap gap-4">
                        <div class="min-w-[150px]">
                            <Label for="month">Month</Label>
                            <select
                                id="month"
                                v-model="selectedMonth"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option v-for="month in months" :key="month.value" :value="month.value">
                                    {{ month.label }}
                                </option>
                            </select>
                        </div>
                        <div class="min-w-[120px]">
                            <Label for="year">Year</Label>
                            <select
                                id="year"
                                v-model="selectedYear"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option v-for="year in years" :key="year" :value="year">
                                    {{ year }}
                                </option>
                            </select>
                        </div>
                        <div class="min-w-[120px]">
                            <Label for="class">Class</Label>
                            <select
                                id="class"
                                v-model="selectedClass"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option value="">All Classes</option>
                                <option v-for="cls in classes" :key="cls" :value="cls">
                                    Class {{ cls }}
                                </option>
                            </select>
                        </div>
                        <div class="min-w-[120px]">
                            <Label for="section">Section</Label>
                            <select
                                id="section"
                                v-model="selectedSection"
                                class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                            >
                                <option value="">All Sections</option>
                                <option v-for="section in sections" :key="section" :value="section">
                                    Section {{ section }}
                                </option>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <Button @click="applyFilters">
                                Generate Report
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Overall Statistics -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Total Records</CardDescription>
                        <CardTitle class="text-3xl">{{ report.stats.total_records }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Unique Students</CardDescription>
                        <CardTitle class="text-3xl">{{ report.stats.unique_students }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Working Days</CardDescription>
                        <CardTitle class="text-3xl">{{ report.stats.working_days }}</CardTitle>
                    </CardHeader>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardDescription>Overall Attendance Rate</CardDescription>
                        <CardTitle class="text-3xl" :class="getAttendanceRateColor(report.stats.attendance_rate)">
                            {{ report.stats.attendance_rate }}%
                        </CardTitle>
                    </CardHeader>
                </Card>
            </div>

            <!-- Status Distribution -->
            <Card>
                <CardHeader>
                    <CardTitle>Status Distribution</CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div class="text-center">
                            <div class="text-4xl font-bold text-green-600 mb-2">
                                {{ report.stats.present_count }}
                            </div>
                            <div class="text-sm text-muted-foreground">Present</div>
                            <div class="text-xs text-muted-foreground mt-1">
                                {{ report.stats.total_records > 0 
                                    ? ((report.stats.present_count / report.stats.total_records) * 100).toFixed(1) 
                                    : 0 }}%
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-red-600 mb-2">
                                {{ report.stats.absent_count }}
                            </div>
                            <div class="text-sm text-muted-foreground">Absent</div>
                            <div class="text-xs text-muted-foreground mt-1">
                                {{ report.stats.total_records > 0 
                                    ? ((report.stats.absent_count / report.stats.total_records) * 100).toFixed(1) 
                                    : 0 }}%
                            </div>
                        </div>
                        <div class="text-center">
                            <div class="text-4xl font-bold text-yellow-600 mb-2">
                                {{ report.stats.late_count }}
                            </div>
                            <div class="text-sm text-muted-foreground">Late</div>
                            <div class="text-xs text-muted-foreground mt-1">
                                {{ report.stats.total_records > 0 
                                    ? ((report.stats.late_count / report.stats.total_records) * 100).toFixed(1) 
                                    : 0 }}%
                            </div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Student-wise Statistics -->
            <Card>
                <CardHeader>
                    <CardTitle>Student-wise Attendance</CardTitle>
                    <CardDescription>Detailed attendance breakdown for each student</CardDescription>
                </CardHeader>
                <CardContent>
                    <div v-if="report.stats.student_stats.length > 0" class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="border-b">
                                <tr>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Student
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Student ID
                                    </th>
                                    <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                        Class
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Total Days
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Present
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Absent
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Late
                                    </th>
                                    <th class="px-4 py-3 text-center text-sm font-medium text-muted-foreground">
                                        Attendance Rate
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr
                                    v-for="stat in report.stats.student_stats"
                                    :key="stat.student.id"
                                    class="border-b last:border-b-0 hover:bg-muted/50 transition-colors"
                                >
                                    <td class="px-4 py-3 font-medium">
                                        {{ stat.student.name }}
                                    </td>
                                    <td class="px-4 py-3 text-sm text-muted-foreground">
                                        {{ stat.student.student_id }}
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        Class {{ stat.student.class }} - Section {{ stat.student.section }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm">
                                        {{ stat.total_days }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-green-600">
                                        {{ stat.present }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-red-600">
                                        {{ stat.absent }}
                                    </td>
                                    <td class="px-4 py-3 text-center text-sm text-yellow-600">
                                        {{ stat.late }}
                                    </td>
                                    <td class="px-4 py-3 text-center">
                                        <span class="text-sm font-semibold" :class="getAttendanceRateColor(stat.attendance_rate)">
                                            {{ stat.attendance_rate }}%
                                        </span>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div v-else class="text-center py-8 text-muted-foreground">
                        No attendance records found for the selected criteria.
                    </div>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

