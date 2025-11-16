import { router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { debounce } from 'lodash-es';

interface AttendanceFilters {
    date?: string;
    start_date?: string;
    end_date?: string;
    status?: string;
    class?: string;
    section?: string;
    student_id?: string;
}

/**
 * Composable for handling attendance filters
 */
export function useAttendanceFilters(initialFilters: AttendanceFilters = {}) {
    const date = ref(initialFilters.date || '');
    const startDate = ref(initialFilters.start_date || '');
    const endDate = ref(initialFilters.end_date || '');
    const status = ref(initialFilters.status || '');
    const selectedClass = ref(initialFilters.class || '');
    const selectedSection = ref(initialFilters.section || '');
    const studentId = ref(initialFilters.student_id || '');

    /**
     * Debounced filter update
     */
    const updateFilters = debounce(() => {
        router.get(
            window.location.pathname,
            {
                date: date.value || undefined,
                start_date: startDate.value || undefined,
                end_date: endDate.value || undefined,
                status: status.value || undefined,
                class: selectedClass.value || undefined,
                section: selectedSection.value || undefined,
                student_id: studentId.value || undefined,
            },
            {
                preserveState: true,
                preserveScroll: true,
                replace: true,
            }
        );
    }, 300);

    /**
     * Watch filters and update on change
     */
    watch([date, startDate, endDate, status, selectedClass, selectedSection, studentId], () => {
        updateFilters();
    });

    /**
     * Clear all filters
     */
    const clearFilters = () => {
        date.value = '';
        startDate.value = '';
        endDate.value = '';
        status.value = '';
        selectedClass.value = '';
        selectedSection.value = '';
        studentId.value = '';
    };

    return {
        date,
        startDate,
        endDate,
        status,
        selectedClass,
        selectedSection,
        studentId,
        clearFilters,
    };
}

