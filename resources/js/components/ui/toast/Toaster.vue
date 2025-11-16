<script setup lang="ts">
import { useToast } from './use-toast'
import Toast from './Toast.vue'

const { toasts, dismiss } = useToast()
</script>

<template>
    <Teleport to="body">
        <div
            class="fixed top-0 right-0 z-[100] flex max-h-screen w-full flex-col-reverse p-4 sm:right-0 sm:top-0 sm:flex-col md:max-w-[420px]"
        >
            <TransitionGroup
                name="toast"
                tag="div"
                class="flex flex-col gap-2"
            >
                <Toast
                    v-for="toast in toasts"
                    :key="toast.id"
                    :toast="toast"
                    :on-dismiss="dismiss"
                />
            </TransitionGroup>
        </div>
    </Teleport>
</template>

<style scoped>
.toast-enter-active,
.toast-leave-active {
    transition: all 0.3s ease;
}

.toast-enter-from {
    opacity: 0;
    transform: translateX(100%);
}

.toast-leave-to {
    opacity: 0;
    transform: translateX(100%);
}
</style>

