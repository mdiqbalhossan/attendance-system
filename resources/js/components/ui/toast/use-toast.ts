import { ref } from 'vue'

export type ToastVariant = 'default' | 'destructive' | 'success'

export interface Toast {
    id: string
    title?: string
    description?: string
    variant?: ToastVariant
    duration?: number
}

const toasts = ref<Toast[]>([])
let toastIdCounter = 0

export function useToast() {
    const toast = ({
        title,
        description,
        variant = 'default',
        duration = 5000,
    }: Omit<Toast, 'id'>) => {
        const id = `toast-${++toastIdCounter}`
        
        toasts.value.push({
            id,
            title,
            description,
            variant,
            duration,
        })

        if (duration > 0) {
            setTimeout(() => {
                dismiss(id)
            }, duration)
        }

        return id
    }

    const dismiss = (id: string) => {
        const index = toasts.value.findIndex(t => t.id === id)
        if (index > -1) {
            toasts.value.splice(index, 1)
        }
    }

    return {
        toast,
        toasts,
        dismiss,
    }
}

