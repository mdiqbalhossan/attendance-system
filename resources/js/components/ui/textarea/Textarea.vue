<script setup lang="ts">
import { computed, type TextareaHTMLAttributes } from 'vue'
import { cn } from '@/lib/utils'

interface Props extends /* @vue-ignore */ TextareaHTMLAttributes {
    class?: string
}

const props = defineProps<Props>()

const delegatedProps = computed(() => {
    const { class: _, ...delegated } = props
    return delegated
})
</script>

<template>
    <textarea
        v-bind="delegatedProps"
        :class="cn(
            'flex min-h-[80px] w-full rounded-md border border-input bg-background px-3 py-2 text-sm ring-offset-background placeholder:text-muted-foreground focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ring focus-visible:ring-offset-2 disabled:cursor-not-allowed disabled:opacity-50',
            props.class
        )"
    />
</template>

