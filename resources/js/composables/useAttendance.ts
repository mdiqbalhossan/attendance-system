import { router } from '@inertiajs/vue3';
import { ref } from 'vue';

/**
 * Composable for handling attendance operations
 */
export function useAttendance() {
    const isSubmitting = ref(false);
    const error = ref<string | null>(null);

    /**
     * Record bulk attendance
     */
    const recordBulkAttendance = async (
        date: string,
        attendances: Array<{
            student_id: number;
            status: 'Present' | 'Absent' | 'Late';
            note?: string;
        }>
    ) => {
        isSubmitting.value = true;
        error.value = null;

        try {
            // Send both date and attendances array
            // Backend's prepareForValidation() will merge date into each record
            router.post(
                '/attendance/bulk',
                {
                    date: date,
                    attendances: attendances,
                },
                {
                    preserveScroll: true,
                    preserveState: false,
                    onSuccess: () => {
                        isSubmitting.value = false;
                    },
                    onError: (errors) => {
                        isSubmitting.value = false;
                        error.value = Object.values(errors).join(', ');
                        console.error('Attendance submission errors:', errors);
                    },
                    onFinish: () => {
                        isSubmitting.value = false;
                    },
                }
            );
        } catch (e) {
            isSubmitting.value = false;
            error.value = e instanceof Error ? e.message : 'An error occurred';
            console.error('Attendance submission exception:', e);
        }
    };

    /**
     * Update attendance status
     */
    const updateAttendance = (
        attendanceId: number,
        data: {
            status: 'Present' | 'Absent' | 'Late';
            note?: string;
        }
    ) => {
        router.put(`/attendance/${attendanceId}`, data, {
            preserveScroll: true,
            preserveState: true,
        });
    };

    /**
     * Delete attendance record
     */
    const deleteAttendance = (attendanceId: number) => {
        if (confirm('Are you sure you want to delete this attendance record?')) {
            router.delete(`/attendance/${attendanceId}`, {
                preserveScroll: true,
            });
        }
    };

    /**
     * Mark all students as present
     */
    const markAllPresent = (
        students: Array<{ id: number }>,
        date: string
    ) => {
        const attendances = students.map((student) => ({
            student_id: student.id,
            status: 'Present' as const,
        }));

        return recordBulkAttendance(date, attendances);
    };

    /**
     * Get status badge color
     */
    const getStatusColor = (status: string): string => {
        const colors: Record<string, string> = {
            Present: 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300',
            Absent: 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300',
            Late: 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300',
        };

        return colors[status] || 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300';
    };

    return {
        isSubmitting,
        error,
        recordBulkAttendance,
        updateAttendance,
        deleteAttendance,
        markAllPresent,
        getStatusColor,
    };
}

