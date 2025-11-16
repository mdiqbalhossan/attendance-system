<!--
  StatCard Component
  
  Reusable card component for displaying statistics with icon, title, value, and optional description
-->

<script setup lang="ts">
import { Icon } from '@iconify/vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { computed } from 'vue';

interface Props {
    title: string;
    value: string | number;
    icon: string;
    description?: string;
    color?: string;
    percentage?: number;
    trend?: 'up' | 'down' | 'neutral';
    loading?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    color: 'text-gray-600 dark:text-gray-400 bg-gray-50 dark:bg-gray-950/30',
    loading: false,
});

// Extract background and text colors from the color prop
const colorClasses = computed(() => {
    return props.color;
});

// Get trend icon and color
const trendConfig = computed(() => {
    if (!props.trend) return null;
    
    const configs = {
        up: {
            icon: 'lucide:trending-up',
            color: 'text-green-600 dark:text-green-400',
        },
        down: {
            icon: 'lucide:trending-down',
            color: 'text-red-600 dark:text-red-400',
        },
        neutral: {
            icon: 'lucide:minus',
            color: 'text-gray-600 dark:text-gray-400',
        },
    };
    
    return configs[props.trend];
});
</script>

<template>
    <Card class="overflow-hidden transition-all hover:shadow-md">
        <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
            <CardTitle class="text-sm font-medium">
                {{ title }}
            </CardTitle>
            <div
                :class="colorClasses"
                class="rounded-lg p-2"
            >
                <Icon
                    :icon="icon"
                    class="h-4 w-4"
                />
            </div>
        </CardHeader>
        <CardContent>
            <!-- Loading State -->
            <div v-if="loading" class="space-y-2">
                <div class="h-8 w-24 animate-pulse rounded bg-muted"></div>
                <div class="h-4 w-32 animate-pulse rounded bg-muted"></div>
            </div>
            
            <!-- Content -->
            <div v-else>
                <div class="flex items-baseline gap-2">
                    <div class="text-2xl font-bold">{{ value }}</div>
                    <div
                        v-if="percentage !== undefined"
                        class="text-xs text-muted-foreground"
                    >
                        ({{ percentage }}%)
                    </div>
                </div>
                
                <div
                    v-if="description || trend"
                    class="mt-1 flex items-center gap-2"
                >
                    <p
                        v-if="description"
                        class="text-xs text-muted-foreground"
                    >
                        {{ description }}
                    </p>
                    
                    <div
                        v-if="trendConfig"
                        :class="trendConfig.color"
                        class="flex items-center gap-1 text-xs font-medium"
                    >
                        <Icon
                            :icon="trendConfig.icon"
                            class="h-3 w-3"
                        />
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

