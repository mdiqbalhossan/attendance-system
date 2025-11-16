<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Badge } from '@/components/ui/badge';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Head, Link } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { useAttendanceFilters } from '@/composables/useAttendanceFilters';
import { useAttendance } from '@/composables/useAttendance';

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

interface Attendance {
    id: number;
    student_id: number;
    student: Student;
    date: string;
    date_formatted: string;
    status: 'Present' | 'Absent' | 'Late';
    note: string | null;
    recorder: {
        id: number;
        name: string;
    };
}

interface PaginationLink {
    url: string | null;
    label: string;
    active: boolean;
}

interface PaginationMeta {
    current_page: number;
    from: number;
    last_page: number;
    links: PaginationLink[];
    path: string;
    per_page: number;
    to: number;
    total: number;
}

interface DailyStats {
    date: string;
    total: number;
    present: number;
    absent: number;
    late: number;
    attendance_rate: number;
}

interface Props {
    attendances: {
        data: Attendance[];
        meta: PaginationMeta;
    };
    filters: {
        date?: string;
        start_date?: string;
        end_date?: string;
        status?: string;
        class?: string;
        section?: string;
        student_id?: string;
    };
    classes: string[];
    sections: string[];
    dailyStats: DailyStats | null;
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
];

// ========================================
// Composables
// ========================================

const { date, startDate, endDate, status, selectedClass, selectedSection, clearFilters } =
    useAttendanceFilters(props.filters);

const { getStatusColor, deleteAttendance } = useAttendance();
</script>

<template>
    <Head title="Attendance Records" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Attendance Records</h1>
                    <p class="text-sm text-muted-foreground">
                        View and manage student attendance records
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link href="/attendance/reports/monthly">
                        <Button variant="outline">Monthly Report</Button>
                    </Link>
                    <Link href="/attendance/create">
                        <Button>Record Attendance</Button>
                    </Link>
                </div>
            </div>

            <!-- Daily Stats Card -->
            <Card v-if="dailyStats" class="bg-gradient-to-br from-primary/10 to-primary/5">
                <CardHeader>
                    <CardTitle>Daily Statistics</CardTitle>
                    <CardDescription>{{ dailyStats.date }}</CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <div class="text-center">
                            <div class="text-2xl font-bold text-primary">{{ dailyStats.total }}</div>
                            <div class="text-xs text-muted-foreground">Total</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-green-600">{{ dailyStats.present }}</div>
                            <div class="text-xs text-muted-foreground">Present</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-red-600">{{ dailyStats.absent }}</div>
                            <div class="text-xs text-muted-foreground">Absent</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-yellow-600">{{ dailyStats.late }}</div>
                            <div class="text-xs text-muted-foreground">Late</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl font-bold text-blue-600">{{ dailyStats.attendance_rate }}%</div>
                            <div class="text-xs text-muted-foreground">Attendance Rate</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 rounded-lg border bg-card p-4">
                <div class="min-w-[150px]">
                    <Label for="date">Date</Label>
                    <Input
                        id="date"
                        v-model="date"
                        type="date"
                        class="mt-1"
                    />
                </div>
                <div class="min-w-[150px]">
                    <Label for="start_date">Start Date</Label>
                    <Input
                        id="start_date"
                        v-model="startDate"
                        type="date"
                        class="mt-1"
                    />
                </div>
                <div class="min-w-[150px]">
                    <Label for="end_date">End Date</Label>
                    <Input
                        id="end_date"
                        v-model="endDate"
                        type="date"
                        class="mt-1"
                    />
                </div>
                <div class="min-w-[120px]">
                    <Label for="status">Status</Label>
                    <select
                        id="status"
                        v-model="status"
                        class="mt-1 block w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background focus:border-ring focus:outline-none focus:ring-2 focus:ring-ring focus:ring-offset-2"
                    >
                        <option value="">All Status</option>
                        <option value="Present">Present</option>
                        <option value="Absent">Absent</option>
                        <option value="Late">Late</option>
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
                    <Button variant="outline" @click="clearFilters">
                        Clear Filters
                    </Button>
                </div>
            </div>

            <!-- Attendance Table -->
            <div v-if="attendances.data && attendances.data.length > 0" class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Date
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Student
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Student ID
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Class
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Recorded By
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="attendance in attendances.data"
                                :key="attendance.id"
                                class="border-b last:border-b-0 hover:bg-muted/50 transition-colors"
                            >
                                <td class="px-4 py-3 text-sm">
                                    {{ attendance.date_formatted }}
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ attendance.student.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ attendance.student.student_id }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    Class {{ attendance.student.class }} - Section {{ attendance.student.section }}
                                </td>
                                <td class="px-4 py-3">
                                    <Badge :class="getStatusColor(attendance.status)">
                                        {{ attendance.status }}
                                    </Badge>
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ attendance.recorder.name }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="`/attendance/${attendance.id}/edit`">
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="deleteAttendance(attendance.id)"
                                        >
                                            Delete
                                        </Button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                <div v-if="attendances.meta && attendances.meta.last_page > 1" class="border-t px-4 py-3">
                    <div class="flex items-center justify-between flex-wrap gap-4">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ attendances.meta.from ?? 0 }} to {{ attendances.meta.to ?? 0 }} of {{ attendances.meta.total ?? 0 }} results
                        </div>
                        <div class="flex gap-1 flex-wrap">
                            <Link
                                v-for="(link, index) in attendances.meta.links"
                                :key="`pagination-${index}`"
                                :href="link.url ?? '#'"
                                :class="[
                                    'px-3 py-1 text-sm rounded border inline-flex items-center justify-center min-w-[40px]',
                                    link.active
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'bg-background hover:bg-muted border-input',
                                    !link.url && 'opacity-50 cursor-not-allowed pointer-events-none'
                                ]"
                                :preserve-scroll="true"
                                :preserve-state="true"
                            >
                                <span v-html="link.label"></span>
                            </Link>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-12 text-center rounded-lg border bg-card"
            >
                <div class="rounded-full bg-muted p-3 mb-4">
                    <svg class="h-6 w-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-foreground mb-1">No attendance records found</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    {{ date || selectedClass || selectedSection ? 'Try adjusting your filters' : 'Get started by recording attendance' }}
                </p>
                <Link href="/attendance/create">
                    <Button>Record Attendance</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

