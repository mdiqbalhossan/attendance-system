<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Badge } from '@/components/ui/badge';
import { Head, Link, router } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';
import { Calendar, User, Clock, FileText, UserCheck } from 'lucide-vue-next';

interface Student {
    id: number;
    name: string;
    student_id: string;
    class: string;
    section: string;
}

interface Recorder {
    id: number;
    name: string;
    email: string;
}

interface Attendance {
    id: number;
    student_id: number;
    student: Student;
    date: string;
    status: 'Present' | 'Absent' | 'Late';
    note: string | null;
    recorded_by: number;
    recorder: Recorder | null;
    created_at: string;
    updated_at: string;
}

interface Props {
    attendance: Attendance;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Attendance',
        href: '/attendance',
    },
    {
        title: 'View',
        href: `/attendance/${props.attendance.id}`,
    },
];

const getStatusColor = (status: string) => {
    const colors = {
        Present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
        Absent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
        Late: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
    };
    return colors[status as keyof typeof colors] || 'bg-gray-100 text-gray-800';
};

const deleteRecord = () => {
    if (confirm('Are you sure you want to delete this attendance record?')) {
        router.delete(`/attendance/${props.attendance.id}`);
    }
};

const formatDate = (date: string) => {
    try {
        return new Date(date).toLocaleDateString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric' 
        });
    } catch {
        return date;
    }
};

const formatDateTime = (date: string) => {
    try {
        return new Date(date).toLocaleString('en-US', { 
            year: 'numeric', 
            month: 'long', 
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    } catch {
        return date;
    }
};
</script>

<template>
    <Head title="View Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Attendance Details</h1>
                    <p class="text-sm text-muted-foreground">
                        View attendance record information
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/attendance/${attendance.id}/edit`">
                        <Button variant="outline">Edit</Button>
                    </Link>
                    <Button variant="destructive" @click="deleteRecord">
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 md:grid-cols-2">
                <!-- Student Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <User class="h-5 w-5" />
                            Student Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Name</div>
                            <div class="font-medium">{{ attendance.student?.name || 'N/A' }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Student ID</div>
                            <div class="font-medium">{{ attendance.student?.student_id || 'N/A' }}</div>
                        </div>
                        <div class="flex gap-4">
                            <div>
                                <div class="text-sm text-muted-foreground">Class</div>
                                <Badge variant="secondary" class="mt-1">
                                    Class {{ attendance.student?.class || 'N/A' }}
                                </Badge>
                            </div>
                            <div>
                                <div class="text-sm text-muted-foreground">Section</div>
                                <Badge variant="secondary" class="mt-1">
                                    Section {{ attendance.student?.section || 'N/A' }}
                                </Badge>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Attendance Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Calendar class="h-5 w-5" />
                            Attendance Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Date</div>
                            <div class="font-medium">{{ formatDate(attendance.date) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground mb-1">Status</div>
                            <Badge :class="getStatusColor(attendance.status)">
                                {{ attendance.status }}
                            </Badge>
                        </div>
                        <div v-if="attendance.note">
                            <div class="text-sm text-muted-foreground mb-1 flex items-center gap-1">
                                <FileText class="h-4 w-4" />
                                Note
                            </div>
                            <div class="rounded-md bg-muted/50 p-3 text-sm">
                                {{ attendance.note }}
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Recording Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <UserCheck class="h-5 w-5" />
                            Recording Information
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div v-if="attendance.recorder">
                            <div class="text-sm text-muted-foreground">Recorded By</div>
                            <div class="font-medium">{{ attendance.recorder.name }}</div>
                            <div class="text-sm text-muted-foreground">{{ attendance.recorder.email }}</div>
                        </div>
                        <div v-else>
                            <div class="text-sm text-muted-foreground">Recorded By</div>
                            <div class="font-medium">Unknown</div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Timestamps -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Clock class="h-5 w-5" />
                            Timestamps
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div>
                            <div class="text-sm text-muted-foreground">Created At</div>
                            <div class="text-sm">{{ formatDateTime(attendance.created_at) }}</div>
                        </div>
                        <div>
                            <div class="text-sm text-muted-foreground">Last Updated</div>
                            <div class="text-sm">{{ formatDateTime(attendance.updated_at) }}</div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Back Button -->
            <div>
                <Link href="/attendance">
                    <Button variant="outline">Back to List</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>

