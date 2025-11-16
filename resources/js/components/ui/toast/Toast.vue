<script setup lang="ts">
import { computed } from 'vue'
import { X } from 'lucide-vue-next'
import type { Toast as ToastType } from './use-toast'

interface Props {
    toast: ToastType
    onDismiss: (id: string) => void
}

const props = defineProps<Props>()

const variantClasses = computed(() => {
    const variants = {
        default: 'bg-background border-border',
        destructive: 'bg-red-600 text-white border-red-700',
        success: 'bg-green-600 text-white border-green-700',
    }
    return variants[props.toast.variant || 'default']
})
</script>

<template>
    <div
        :class="[
            'pointer-events-auto relative flex w-full items-center justify-between space-x-4 overflow-hidden rounded-lg border p-4 pr-8 shadow-lg transition-all',
            variantClasses
        ]"
        role="alert"
    >
        <div class="grid gap-1">
            <div v-if="toast.title" class="text-sm font-semibold">
                {{ toast.title }}
            </div>
            <div v-if="toast.description" class="text-sm opacity-90">
                {{ toast.description }}
            </div>
        </div>
        <button
            @click="onDismiss(toast.id)"
            class="absolute right-2 top-2 rounded-md p-1 opacity-70 transition-opacity hover:opacity-100"
        >
            <X class="h-4 w-4" />
            <span class="sr-only">Close</span>
        </button>
    </div>
</template>

