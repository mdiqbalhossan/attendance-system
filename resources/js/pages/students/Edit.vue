<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import InputError from '@/components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
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
    {
        title: 'Edit',
        href: `/students/${props.student.id}/edit`,
    },
];

const form = useForm({
    name: props.student.name,
    student_id: props.student.student_id,
    class: props.student.class,
    section: props.student.section,
    photo: null as File | null,
});

const photoPreview = ref<string | null>(props.student.photo_url);

const handlePhotoChange = (event: Event) => {
    const target = event.target as HTMLInputElement;
    const file = target.files?.[0];

    if (file) {
        form.photo = file;

        // Create preview
        const reader = new FileReader();
        reader.onload = (e) => {
            photoPreview.value = e.target?.result as string;
        };
        reader.readAsDataURL(file);
    } else {
        form.photo = null;
        photoPreview.value = props.student.photo_url;
    }
};

const removePhoto = () => {
    form.photo = null;
    photoPreview.value = null;
    // Reset file input
    const fileInput = document.getElementById('photo') as HTMLInputElement;
    if (fileInput) {
        fileInput.value = '';
    }
};

const submit = () => {
    form.post(`/students/${props.student.id}`, {
        method: 'put',
        onSuccess: () => {
            // Redirect will be handled by the controller
        },
    });
};
</script>

<template>
    <Head :title="`Edit ${student.name}`" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 overflow-x-auto rounded-xl p-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-semibold text-foreground">Edit Student</h1>
                    <p class="text-sm text-muted-foreground">
                        Update {{ student.name }}'s information
                    </p>
                </div>
                <div class="flex gap-2">
                    <Link :href="`/students/${student.id}`">
                        <Button variant="outline">View Student</Button>
                    </Link>
                    <Link href="/students">
                        <Button variant="outline">Back to Students</Button>
                    </Link>
                </div>
            </div>

            <!-- Form -->
            <div class="max-w-2xl">
                <form @submit.prevent="submit" class="space-y-6">
                    <div class="rounded-lg border bg-card p-6">
                        <h2 class="text-lg font-medium text-foreground mb-4">Student Information</h2>

                        <div class="grid gap-6">
                            <!-- Photo Upload -->
                            <div>
                                <Label for="photo">Photo</Label>
                                <div class="mt-2">
                                    <div v-if="photoPreview" class="mb-4">
                                        <div class="relative inline-block">
                                            <img
                                                :src="photoPreview"
                                                alt="Photo preview"
                                                class="h-32 w-32 rounded-lg object-cover border"
                                            />
                                            <button
                                                type="button"
                                                @click="removePhoto"
                                                class="absolute -top-2 -right-2 h-6 w-6 rounded-full bg-destructive text-destructive-foreground flex items-center justify-center text-xs hover:bg-destructive/90"
                                            >
                                                ×
                                            </button>
                                        </div>
                                    </div>
                                    <input
                                        id="photo"
                                        type="file"
                                        accept="image/*"
                                        @change="handlePhotoChange"
                                        class="block w-full text-sm text-muted-foreground file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-medium file:bg-primary file:text-primary-foreground hover:file:bg-primary/90"
                                    />
                                    <p class="mt-1 text-xs text-muted-foreground">
                                        Leave empty to keep current photo. Upload a new image to replace it.
                                    </p>
                                </div>
                                <InputError class="mt-2" :message="form.errors.photo" />
                            </div>

                            <!-- Name -->
                            <div>
                                <Label for="name">Full Name</Label>
                                <Input
                                    id="name"
                                    v-model="form.name"
                                    type="text"
                                    class="mt-1"
                                    placeholder="Enter student's full name"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.name" />
                            </div>

                            <!-- Student ID -->
                            <div>
                                <Label for="student_id">Student ID</Label>
                                <Input
                                    id="student_id"
                                    v-model="form.student_id"
                                    type="text"
                                    class="mt-1"
                                    placeholder="Enter unique student ID"
                                    required
                                />
                                <InputError class="mt-2" :message="form.errors.student_id" />
                            </div>

                            <!-- Class and Section -->
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <Label for="class">Class</Label>
                                    <Input
                                        id="class"
                                        v-model="form.class"
                                        type="text"
                                        class="mt-1"
                                        placeholder="e.g., 10"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.class" />
                                </div>
                                <div>
                                    <Label for="section">Section</Label>
                                    <Input
                                        id="section"
                                        v-model="form.section"
                                        type="text"
                                        class="mt-1"
                                        placeholder="e.g., A"
                                        required
                                    />
                                    <InputError class="mt-2" :message="form.errors.section" />
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Actions -->
                    <div class="flex items-center gap-4">
                        <Button type="submit" :disabled="form.processing">
                            <span v-if="form.processing">Updating...</span>
                            <span v-else>Update Student</span>
                        </Button>
                        <Link :href="`/students/${student.id}`">
                            <Button type="button" variant="outline">
                                Cancel
                            </Button>
                        </Link>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
