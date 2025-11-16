<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Head, useForm } from '@inertiajs/vue3';
import { type BreadcrumbItem } from '@/types';

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
    status: 'Present' | 'Absent' | 'Late';
    note: string | null;
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
        title: 'Edit',
        href: `/attendance/${props.attendance.id}/edit`,
    },
];

const form = useForm({
    student_id: props.attendance.student_id,
    date: props.attendance.date,
    status: props.attendance.status,
    note: props.attendance.note || '',
});

const submit = () => {
    form.put(`/attendance/${props.attendance.id}`, {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Edit Attendance" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Edit Attendance</h1>
                    <p class="text-sm text-muted-foreground">
                        Update attendance record for {{ attendance.student?.name || 'Student' }}
                    </p>
                </div>
            </div>

            <Card>
                <CardHeader>
                    <CardTitle>Attendance Details</CardTitle>
                    <CardDescription>
                        Edit the attendance information below
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Student Info (Read-only) -->
                        <div class="space-y-2">
                            <Label>Student</Label>
                            <div class="rounded-md border bg-muted/50 px-3 py-2">
                                <div class="font-medium">{{ attendance.student?.name || 'N/A' }}</div>
                                <div class="text-sm text-muted-foreground">
                                    ID: {{ attendance.student?.student_id || 'N/A' }} | 
                                    Class: {{ attendance.student?.class || 'N/A' }} | 
                                    Section: {{ attendance.student?.section || 'N/A' }}
                                </div>
                            </div>
                        </div>

                        <!-- Date -->
                        <div class="space-y-2">
                            <Label for="date">Date</Label>
                            <Input
                                id="date"
                                v-model="form.date"
                                type="date"
                                :class="{ 'border-red-500': form.errors.date }"
                                required
                            />
                            <p v-if="form.errors.date" class="text-sm text-red-500">
                                {{ form.errors.date }}
                            </p>
                        </div>

                        <!-- Status -->
                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model="form.status" required>
                                <SelectTrigger id="status" :class="{ 'border-red-500': form.errors.status }">
                                    <SelectValue placeholder="Select status" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="Present">Present</SelectItem>
                                    <SelectItem value="Absent">Absent</SelectItem>
                                    <SelectItem value="Late">Late</SelectItem>
                                </SelectContent>
                            </Select>
                            <p v-if="form.errors.status" class="text-sm text-red-500">
                                {{ form.errors.status }}
                            </p>
                        </div>

                        <!-- Note -->
                        <div class="space-y-2">
                            <Label for="note">Note (Optional)</Label>
                            <Textarea
                                id="note"
                                v-model="form.note"
                                placeholder="Add any additional notes..."
                                rows="3"
                                :class="{ 'border-red-500': form.errors.note }"
                            />
                            <p v-if="form.errors.note" class="text-sm text-red-500">
                                {{ form.errors.note }}
                            </p>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3">
                            <Button
                                type="submit"
                                :disabled="form.processing"
                            >
                                {{ form.processing ? 'Updating...' : 'Update Attendance' }}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit('/attendance')"
                            >
                                Cancel
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>

