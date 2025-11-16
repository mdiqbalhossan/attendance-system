<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent } from '@/components/ui/card';
import { Head, router, usePage } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { ref, computed, watch } from 'vue';
import { useAttendance } from '@/composables/useAttendance';
import { useToast } from '@/components/ui/toast/use-toast';

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

interface Props {
    students: Student[];
    date: string;
    existingAttendance: Record<number, 'Present' | 'Absent' | 'Late'>;
    filters: {
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
        title: 'Record Attendance',
        href: '/attendance/create',
    },
];

// ========================================
// State
// ========================================

const selectedDate = ref(props.date);
const selectedClass = ref(props.filters.class || '');
const selectedSection = ref(props.filters.section || '');

// Initialize attendance records from existing or default to Present
const attendanceRecords = ref<Record<number, { status: 'Present' | 'Absent' | 'Late'; note: string }>>(
    props.students.reduce((acc, student) => {
        acc[student.id] = {
            status: props.existingAttendance[student.id] || 'Present',
            note: '',
        };
        return acc;
    }, {} as Record<number, { status: 'Present' | 'Absent' | 'Late'; note: string }>)
);

// ========================================
// Composables
// ========================================

const { isSubmitting, recordBulkAttendance, getStatusColor } = useAttendance();
const { toast } = useToast();
const page = usePage();

// Watch for flash messages
watch(() => page.props.flash, (flash: any) => {
    if (flash?.success) {
        toast({
            title: 'Success',
            description: flash.success,
        });
    }
    if (flash?.error) {
        toast({
            title: 'Error',
            description: flash.error,
            variant: 'destructive',
        });
    }
    if (flash?.warning) {
        toast({
            title: 'Warning',
            description: flash.warning,
            variant: 'default',
        });
    }
}, { deep: true, immediate: true });

// ========================================
// Computed
// ========================================

const attendanceSummary = computed(() => {
    const records = Object.values(attendanceRecords.value);
    const total = records.length;
    const present = records.filter(r => r.status === 'Present').length;
    const absent = records.filter(r => r.status === 'Absent').length;
    const late = records.filter(r => r.status === 'Late').length;
    
    return {
        total,
        present,
        absent,
        late,
        presentPercentage: total > 0 ? ((present / total) * 100).toFixed(1) : '0.0',
        absentPercentage: total > 0 ? ((absent / total) * 100).toFixed(1) : '0.0',
        latePercentage: total > 0 ? ((late / total) * 100).toFixed(1) : '0.0',
        attendanceRate: total > 0 ? (((present + late) / total) * 100).toFixed(1) : '0.0',
    };
});

// ========================================
// Methods
// ========================================

const applyFilters = () => {
    router.get('/attendance/create', {
        date: selectedDate.value,
        class: selectedClass.value || undefined,
        section: selectedSection.value || undefined,
    }, {
        preserveState: false,
    });
};

const setAllStatus = (status: 'Present' | 'Absent' | 'Late') => {
    props.students.forEach((student) => {
        attendanceRecords.value[student.id].status = status;
    });
};

const submitAttendance = async () => {
    const attendances = props.students.map((student) => ({
        student_id: student.id,
        status: attendanceRecords.value[student.id].status,
        note: attendanceRecords.value[student.id].note || undefined,
    }));

    await recordBulkAttendance(selectedDate.value, attendances);
};
</script>

<template>
    <Head title="Record Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Record Attendance</h1>
                    <p class="text-sm text-muted-foreground">
                        Mark attendance for multiple students
                    </p>
                </div>
            </div>

            <!-- Filters and Date Selection -->
            <Card>
                <CardContent class="pt-6">
                    <div class="flex flex-wrap gap-4">
                        <div class="min-w-[150px]">
                            <Label for="date">Date</Label>
                            <Input
                                id="date"
                                v-model="selectedDate"
                                type="date"
                                class="mt-1"
                            />
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
                                Apply Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Summary Card -->
            <Card class="bg-gradient-to-br from-primary/10 to-primary/5">
                <CardContent class="pt-6">
                    <!-- Overall Attendance Rate -->
                    <div class="mb-6 text-center">
                        <div class="text-4xl font-bold text-primary mb-2">
                            {{ attendanceSummary.attendanceRate }}%
                        </div>
                        <div class="text-sm font-medium text-muted-foreground mb-3">
                            Overall Attendance Rate
                        </div>
                        <div class="w-full bg-muted rounded-full h-3 overflow-hidden">
                            <div 
                                class="h-full bg-gradient-to-r from-green-500 to-primary transition-all duration-300 ease-in-out"
                                :style="{ width: `${attendanceSummary.attendanceRate}%` }"
                            ></div>
                        </div>
                    </div>

                    <!-- Detailed Statistics -->
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div class="text-center p-3 rounded-lg bg-background/50 border-2 border-primary/20">
                            <div class="text-2xl font-bold text-primary">{{ attendanceSummary.total }}</div>
                            <div class="text-xs text-muted-foreground">Total Students</div>
                        </div>
                        <div class="text-center p-3 rounded-lg bg-green-50 dark:bg-green-950/30 border border-green-200 dark:border-green-800">
                            <div class="text-2xl font-bold text-green-600">{{ attendanceSummary.present }}</div>
                            <div class="text-xs text-muted-foreground mb-2">Present</div>
                            <div class="w-full bg-green-200 dark:bg-green-900/50 rounded-full h-1.5 mb-1">
                                <div 
                                    class="h-full bg-green-600 rounded-full transition-all duration-300"
                                    :style="{ width: `${attendanceSummary.presentPercentage}%` }"
                                ></div>
                            </div>
                            <div class="text-xs font-semibold text-green-600">{{ attendanceSummary.presentPercentage }}%</div>
                        </div>
                        <div class="text-center p-3 rounded-lg bg-red-50 dark:bg-red-950/30 border border-red-200 dark:border-red-800">
                            <div class="text-2xl font-bold text-red-600">{{ attendanceSummary.absent }}</div>
                            <div class="text-xs text-muted-foreground mb-2">Absent</div>
                            <div class="w-full bg-red-200 dark:bg-red-900/50 rounded-full h-1.5 mb-1">
                                <div 
                                    class="h-full bg-red-600 rounded-full transition-all duration-300"
                                    :style="{ width: `${attendanceSummary.absentPercentage}%` }"
                                ></div>
                            </div>
                            <div class="text-xs font-semibold text-red-600">{{ attendanceSummary.absentPercentage }}%</div>
                        </div>
                        <div class="text-center p-3 rounded-lg bg-yellow-50 dark:bg-yellow-950/30 border border-yellow-200 dark:border-yellow-800">
                            <div class="text-2xl font-bold text-yellow-600">{{ attendanceSummary.late }}</div>
                            <div class="text-xs text-muted-foreground mb-2">Late</div>
                            <div class="w-full bg-yellow-200 dark:bg-yellow-900/50 rounded-full h-1.5 mb-1">
                                <div 
                                    class="h-full bg-yellow-600 rounded-full transition-all duration-300"
                                    :style="{ width: `${attendanceSummary.latePercentage}%` }"
                                ></div>
                            </div>
                            <div class="text-xs font-semibold text-yellow-600">{{ attendanceSummary.latePercentage }}%</div>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Quick Actions -->
            <div class="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" @click="setAllStatus('Present')">
                    Mark All Present
                </Button>
                <Button variant="outline" size="sm" @click="setAllStatus('Absent')">
                    Mark All Absent
                </Button>
                <Button variant="outline" size="sm" @click="setAllStatus('Late')">
                    Mark All Late
                </Button>
            </div>

            <!-- Students List -->
            <div v-if="students.length > 0" class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
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
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Status
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Note
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="student in students"
                                :key="student.id"
                                class="border-b last:border-b-0 hover:bg-muted/50 transition-colors"
                            >
                                <td class="px-4 py-3 font-medium">
                                    {{ student.name }}
                                </td>
                                <td class="px-4 py-3 text-sm text-muted-foreground">
                                    {{ student.student_id }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    Class {{ student.class }} - Section {{ student.section }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <button
                                            v-for="status in ['Present', 'Absent', 'Late'] as const"
                                            :key="status"
                                            @click="attendanceRecords[student.id].status = status"
                                            :class="[
                                                'px-3 py-1 text-xs font-medium rounded-md transition-colors',
                                                attendanceRecords[student.id].status === status
                                                    ? getStatusColor(status)
                                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200 dark:bg-gray-800 dark:text-gray-400'
                                            ]"
                                        >
                                            {{ status }}
                                        </button>
                                    </div>
                                </td>
                                <td class="px-4 py-3">
                                    <Input
                                        v-model="attendanceRecords[student.id].note"
                                        placeholder="Optional note..."
                                        class="text-sm"
                                    />
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Submit Button -->
                <div class="border-t px-4 py-3 flex justify-end gap-2">
                    <Button variant="outline" @click="$inertia.visit('/attendance')">
                        Cancel
                    </Button>
                    <Button @click="submitAttendance" :disabled="isSubmitting">
                        {{ isSubmitting ? 'Saving...' : 'Save Attendance' }}
                    </Button>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-else
                class="flex flex-col items-center justify-center py-12 text-center rounded-lg border bg-card"
            >
                <div class="rounded-full bg-muted p-3 mb-4">
                    <svg class="h-6 w-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-foreground mb-1">No students found</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    Try adjusting your filters or add students first
                </p>
            </div>
        </div>
    </AppLayout>
</template>

