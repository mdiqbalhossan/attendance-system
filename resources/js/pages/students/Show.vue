<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Head, Link, router } from '@inertiajs/vue3';
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
    student: Student;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Students',
        href: '/students',
    },
    {
        title: props.student.name,
        href: `/students/${props.student.id}`,
    },
];

const deleteStudent = () => {
    if (confirm(`Are you sure you want to delete ${props.student.name}?`)) {
        router.delete(`/students/${props.student.id}`);
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
};
</script>

<template>
    <Head :title="student.name" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">{{ student.name }}</h1>
                    <p class="text-sm text-muted-foreground">
                        Student ID: {{ student.student_id }}
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/students/${student.id}/edit`">
                        <Button>Edit Student</Button>
                    </Link>
                    <Link href="/students">
                        <Button variant="outline">Back to Students</Button>
                    </Link>
                </div>
            </div>

            <!-- Student Details -->
            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Photo and Basic Info -->
                <div class="lg:col-span-1">
                    <div class="rounded-lg border bg-card p-6">
                        <div class="text-center">
                            <div class="mx-auto mb-4 h-32 w-32 rounded-full bg-muted flex items-center justify-center overflow-hidden">
                                <img
                                    v-if="student.photo_url"
                                    :src="student.photo_url"
                                    :alt="student.name"
                                    class="h-full w-full object-cover"
                                />
                                <span v-else class="text-3xl font-medium text-muted-foreground">
                                    {{ student.name.charAt(0).toUpperCase() }}
                                </span>
                            </div>
                            <h2 class="text-xl font-semibold text-foreground">{{ student.name }}</h2>
                            <p class="text-sm text-muted-foreground">{{ student.student_id }}</p>
                        </div>
                    </div>
                </div>

                <!-- Detailed Information -->
                <div class="lg:col-span-2">
                    <div class="rounded-lg border bg-card p-6">
                        <h3 class="text-lg font-medium text-foreground mb-4">Student Information</h3>

                        <div class="grid gap-4 sm:grid-cols-2">
                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Full Name</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ student.name }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Student ID</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ student.student_id }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Class</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ student.class }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Section</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ student.section }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Created At</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ formatDate(student.created_at) }}</dd>
                            </div>

                            <div>
                                <dt class="text-sm font-medium text-muted-foreground">Last Updated</dt>
                                <dd class="mt-1 text-sm text-foreground">{{ formatDate(student.updated_at) }}</dd>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Actions -->
            <div class="rounded-lg border bg-card p-6">
                <h3 class="text-lg font-medium text-foreground mb-4">Actions</h3>
                <div class="flex gap-4">
                    <Link :href="`/students/${student.id}/edit`">
                        <Button>
                            <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                            </svg>
                            Edit Student
                        </Button>
                    </Link>

                    <Button variant="destructive" @click="deleteStudent">
                        <svg class="mr-2 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                        </svg>
                        Delete Student
                    </Button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>
