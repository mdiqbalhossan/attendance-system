<script setup lang="ts">
import type { HTMLAttributes } from 'vue';

defineOptions({
    inheritAttrs: false,
});

interface Props {
    className?: HTMLAttributes['class'];
}

defineProps<Props>();
</script>

<template>
    <svg
        xmlns="http://www.w3.org/2000/svg"
        viewBox="0 0 40 42"
        :class="className"
        v-bind="$attrs"
    >
        <!-- Icon: Calendar with check, representing "attendance" -->
        <rect x="4" y="8" width="32" height="28" rx="4" fill="currentColor" fill-opacity="0.12"/>
        <rect x="4" y="14" width="32" height="22" rx="3" fill="none" stroke="currentColor" stroke-width="2"/>
        <rect x="10" y="4" width="4" height="8" rx="2" fill="currentColor"/>
        <rect x="26" y="4" width="4" height="8" rx="2" fill="currentColor"/>
        <path
            d="M16 25l4 4 8-8"
            fill="none"
            stroke="currentColor"
            stroke-width="2.2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
