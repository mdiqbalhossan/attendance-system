<!--
  RecentActivity Component
  
  Displays recent attendance records with student information
-->

<script setup lang="ts">
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Icon } from '@iconify/vue';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import type { RecentActivity } from '@/composables/useDashboard';
import { useInitials } from '@/composables/useInitials';

interface Props {
    activities: RecentActivity[];
}

const props = defineProps<Props>();

const { getInitials } = useInitials();

// Get status badge variant and icon
const getStatusConfig = (status: string) => {
    const configs: Record<string, { variant: string; icon: string; class: string }> = {
        present: {
            variant: 'default',
            icon: 'lucide:check-circle',
            class: 'bg-green-500/10 text-green-600 hover:bg-green-500/20 dark:text-green-400',
        },
        absent: {
            variant: 'destructive',
            icon: 'lucide:x-circle',
            class: 'bg-red-500/10 text-red-600 hover:bg-red-500/20 dark:text-red-400',
        },
        late: {
            variant: 'secondary',
            icon: 'lucide:clock',
            class: 'bg-amber-500/10 text-amber-600 hover:bg-amber-500/20 dark:text-amber-400',
        },
        excused: {
            variant: 'outline',
            icon: 'lucide:info',
            class: 'bg-blue-500/10 text-blue-600 hover:bg-blue-500/20 dark:text-blue-400',
        },
    };

    return configs[status] || configs.present;
};
</script>

<template>
    <Card>
        <CardHeader>
            <CardTitle>Recent Activity</CardTitle>
            <CardDescription>
                Latest attendance records
            </CardDescription>
        </CardHeader>
        <CardContent>
            <div v-if="activities.length === 0" class="flex flex-col items-center justify-center py-8 text-center">
                <Icon icon="lucide:inbox" class="mb-2 h-12 w-12 text-muted-foreground" />
                <p class="text-sm text-muted-foreground">
                    No recent activity
                </p>
            </div>
            
            <div v-else class="space-y-4">
                <div
                    v-for="activity in activities"
                    :key="activity.id"
                    class="flex items-start gap-4 rounded-lg border border-border p-3 transition-colors hover:bg-muted/50"
                >
                    <!-- Student Avatar -->
                    <Avatar class="h-10 w-10">
                        <AvatarFallback class="text-xs">
                            {{ getInitials(activity.student_name) }}
                        </AvatarFallback>
                    </Avatar>

                    <!-- Activity Details -->
                    <div class="flex-1 space-y-1">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="font-medium leading-none">
                                    {{ activity.student_name }}
                                </p>
                                <p class="text-sm text-muted-foreground">
                                    ID: {{ activity.student_id }}
                                </p>
                            </div>
                            <Badge :class="getStatusConfig(activity.status).class" class="gap-1">
                                <Icon
                                    :icon="getStatusConfig(activity.status).icon"
                                    class="h-3 w-3"
                                />
                                {{ activity.status }}
                            </Badge>
                        </div>
                        
                        <div class="flex items-center gap-4 text-xs text-muted-foreground">
                            <span class="flex items-center gap-1">
                                <Icon icon="lucide:calendar" class="h-3 w-3" />
                                {{ activity.date }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Icon icon="lucide:user" class="h-3 w-3" />
                                {{ activity.recorded_by }}
                            </span>
                            <span class="flex items-center gap-1">
                                <Icon icon="lucide:clock" class="h-3 w-3" />
                                {{ activity.recorded_at }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </CardContent>
    </Card>
</template>

