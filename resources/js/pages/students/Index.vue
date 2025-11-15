<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { type BreadcrumbItem } from '@/types';

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

interface Props {
    students: {
        data: Student[];
        links: any[];
        meta: any;
    };
    filters: {
        search?: string;
        class?: string;
        section?: string;
    };
    classes: string[];
    sections: string[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: '/students',
    },
];

// Reactive filters
const search = ref(props.filters.search || '');
const selectedClass = ref(props.filters.class || '');
const selectedSection = ref(props.filters.section || '');

// Debounce function
const debounce = (func: () => void, wait: number) => {
    let timeout: number;
    return () => {
        clearTimeout(timeout);
        timeout = window.setTimeout(() => func(), wait);
    };
};

// Watch for filter changes and update URL
const debouncedSearch = debounce(() => {
    router.get('/students', {
        search: search.value || undefined,
        class: selectedClass.value || undefined,
        section: selectedSection.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
}, 300);

watch([search, selectedClass, selectedSection], debouncedSearch);

const clearFilters = () => {
    search.value = '';
    selectedClass.value = '';
    selectedSection.value = '';
};

const deleteStudent = (student: Student) => {
    if (confirm(`Are you sure you want to delete ${student.name}?`)) {
        router.delete(`/students/${student.id}`);
    }
};
</script>

<template>
    <Head title="Students" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Students</h1>
                    <p class="text-sm text-muted-foreground">
                        Manage student records and information
                    </p>
                </div>
                <Link href="/students/create">
                    <Button>Add Student</Button>
                </Link>
            </div>

            <!-- Filters -->
            <div class="flex flex-wrap gap-4 rounded-lg border bg-card p-4">
                <div class="flex-1 min-w-[200px]">
                    <Label for="search">Search</Label>
                    <Input
                        id="search"
                        v-model="search"
                        placeholder="Search by name or student ID..."
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
                    <Button variant="outline" @click="clearFilters">
                        Clear Filters
                    </Button>
                </div>
            </div>

            <!-- Students Table -->
            <div class="rounded-lg border bg-card">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="border-b bg-muted/50">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Photo
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Name
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Student ID
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Class
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Section
                                </th>
                                <th class="px-4 py-3 text-left text-sm font-medium text-muted-foreground">
                                    Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                v-for="student in students.data"
                                :key="student.id"
                                class="border-b hover:bg-muted/50"
                            >
                                <td class="px-4 py-3">
                                    <div class="h-10 w-10 rounded-full bg-muted flex items-center justify-center overflow-hidden">
                                        <img
                                            v-if="student.photo_url"
                                            :src="student.photo_url"
                                            :alt="student.name"
                                            class="h-full w-full object-cover"
                                        />
                                        <span v-else class="text-sm font-medium text-muted-foreground">
                                            {{ student.name.charAt(0).toUpperCase() }}
                                        </span>
                                    </div>
                                </td>
                                <td class="px-4 py-3 font-medium">
                                    {{ student.name }}
                                </td>
                                <td class="px-4 py-3 text-muted-foreground">
                                    {{ student.student_id }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ student.class }}
                                </td>
                                <td class="px-4 py-3">
                                    {{ student.section }}
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex gap-2">
                                        <Link :href="`/students/${student.id}`">
                                            <Button variant="outline" size="sm">
                                                View
                                            </Button>
                                        </Link>
                                        <Link :href="`/students/${student.id}/edit`">
                                            <Button variant="outline" size="sm">
                                                Edit
                                            </Button>
                                        </Link>
                                        <Button
                                            variant="destructive"
                                            size="sm"
                                            @click="deleteStudent(student)"
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
                <div v-if="students.links.length > 3" class="border-t px-4 py-3">
                    <div class="flex items-center justify-between">
                        <div class="text-sm text-muted-foreground">
                            Showing {{ students.meta.from }} to {{ students.meta.to }} of {{ students.meta.total }} results
                        </div>
                        <div class="flex gap-1">
                            <Link
                                v-for="link in students.links"
                                :key="link.label"
                                :href="link.url"
                                :class="[
                                    'px-3 py-1 text-sm rounded border',
                                    link.active
                                        ? 'bg-primary text-primary-foreground border-primary'
                                        : 'bg-background hover:bg-muted border-input',
                                    !link.url && 'opacity-50 cursor-not-allowed'
                                ]"
                                :innerHTML="link.label"
                            />
                        </div>
                    </div>
                </div>
            </div>

            <!-- Empty State -->
            <div
                v-if="students.data.length === 0"
                class="flex flex-col items-center justify-center py-12 text-center"
            >
                <div class="rounded-full bg-muted p-3 mb-4">
                    <svg class="h-6 w-6 text-muted-foreground" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197m13.5-9a2.5 2.5 0 11-5 0 2.5 2.5 0 015 0z" />
                    </svg>
                </div>
                <h3 class="text-lg font-medium text-foreground mb-1">No students found</h3>
                <p class="text-sm text-muted-foreground mb-4">
                    {{ search || selectedClass || selectedSection ? 'Try adjusting your filters' : 'Get started by adding your first student' }}
                </p>
                <Link href="/students/create">
                    <Button>Add Student</Button>
                </Link>
            </div>
        </div>
    </AppLayout>
</template>
