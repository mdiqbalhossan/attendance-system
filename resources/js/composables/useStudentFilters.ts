import { ref, watch } from 'vue';
import { router } from '@inertiajs/vue3';

/**
 * Composable for managing student filters
 * Handles search, class, and section filtering with debouncing
 */
export function useStudentFilters(initialFilters: { search?: string; class?: string; section?: string }) {
    // Reactive filter state
    const search = ref(initialFilters.search || '');
    const selectedClass = ref(initialFilters.class || '');
    const selectedSection = ref(initialFilters.section || '');

    /**
     * Debounce utility function
     * Delays execution of the function until after wait time has elapsed
     */
    const debounce = (func: () => void, wait: number) => {
        let timeout: number;
        return () => {
            clearTimeout(timeout);
            timeout = window.setTimeout(() => func(), wait);
        };
    };

    /**
     * Apply filters by updating the URL with query parameters
     * Uses Inertia's router to maintain SPA behavior
     */
    const applyFilters = () => {
        router.get(
            '/students',
            {
                search: search.value || undefined,
                class: selectedClass.value || undefined,
                section: selectedSection.value || undefined,
            },
            {
                preserveState: true,
                replace: true,
            }
        );
    };

    /**
     * Debounced version of applyFilters
     * Waits 300ms after the last change before applying filters
     */
    const debouncedApplyFilters = debounce(applyFilters, 300);

    /**
     * Clear all filters and reset to default state
     */
    const clearFilters = () => {
        search.value = '';
        selectedClass.value = '';
        selectedSection.value = '';
    };

    /**
     * Watch for changes in any filter and apply them
     * Uses debouncing to avoid excessive API calls
     */
    watch([search, selectedClass, selectedSection], debouncedApplyFilters);

    return {
        search,
        selectedClass,
        selectedSection,
        clearFilters,
    };
}

