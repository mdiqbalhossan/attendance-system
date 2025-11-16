import { router } from '@inertiajs/vue3';

interface Student {
    id: number;
    name: string;
    student_id: string;
    class: string;
    section: string;
    photo: string | null;
    photo_url: string | null;
    created_at: string;
    updated_at: string;
}

/**
 * Composable for student-related actions
 * Handles deletion and other student operations
 */
export function useStudentActions() {
    /**
     * Delete a student with confirmation
     * @param student - The student to delete
     */
    const deleteStudent = (student: Student) => {
        if (confirm(`Are you sure you want to delete ${student.name}?`)) {
            router.delete(`/students/${student.id}`, {
                preserveScroll: true,
                onSuccess: () => {
                    // Success message is handled by the backend flash message
                },
                onError: (errors) => {
                    console.error('Failed to delete student:', errors);
                },
            });
        }
    };

    return {
        deleteStudent,
    };
}

