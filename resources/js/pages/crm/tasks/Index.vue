<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { CheckSquare, Plus, Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import InitialsAvatar from '@/components/crm/InitialsAvatar.vue';
import Pagination from '@/components/crm/Pagination.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableEmpty,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import { create, destroy, index, toggle } from '@/routes/tasks';
import type { Paginated, SelectOption, TaskItem, Team } from '@/types';

type Props = {
    tasks: Paginated<TaskItem>;
    filters: { status: string };
    assignees: SelectOption[];
    taskPriorities: SelectOption[];
    can: { create: boolean };
};

defineProps<Props>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const statuses = computed(() => [
    { value: 'open', label: t('Open tasks') },
    { value: 'completed', label: t('Completed') },
    { value: 'all', label: t('All') },
]);

function setStatus(status: string) {
    router.get(
        index(teamSlug.value).url,
        { status },
        { preserveState: true, replace: true, preserveScroll: true },
    );
}

function toggleTask(task: TaskItem) {
    router.patch(
        toggle([teamSlug.value, task.id]).url,
        {},
        { preserveScroll: true },
    );
}

function remove(id: number) {
    router.delete(destroy([teamSlug.value, id]).url, { preserveScroll: true });
}

const priorityVariant: Record<string, 'destructive' | 'secondary' | 'outline'> =
    {
        high: 'destructive',
        normal: 'secondary',
        low: 'outline',
    };

const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

function formatDate(iso: string | null): string {
    return iso ? dateFormatter.format(new Date(iso)) : '—';
}

function isOverdue(task: TaskItem): boolean {
    if (task.is_completed || !task.due_at) {
        return false;
    }

    return new Date(task.due_at).getTime() < Date.now();
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: props.currentTeam
            ? [{ title: t('Tasks'), href: index(props.currentTeam.slug) }]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('Tasks')" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <CheckSquare class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Tasks')"
                    :description="t('Track the actions you need to take')"
                />
                <Badge
                    v-if="tasks.total"
                    variant="secondary"
                    class="tabular-nums"
                >
                    {{ tasks.total }}
                </Badge>
            </div>

            <Button v-if="can.create" as-child>
                <Link :href="create(teamSlug)">
                    <Plus class="h-4 w-4" /> {{ t('New task') }}
                </Link>
            </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="status in statuses"
                :key="status.value"
                size="sm"
                :variant="
                    filters.status === status.value ? 'default' : 'outline'
                "
                @click="setStatus(status.value)"
            >
                {{ status.label }}
            </Button>
        </div>

        <!-- Mobile: stacked cards -->
        <div class="space-y-2.5 md:hidden">
            <div
                v-for="task in tasks.data"
                :key="task.id"
                class="flex items-center gap-3 rounded-xl border border-border/70 bg-card p-3 shadow-card"
            >
                <Checkbox
                    :model-value="task.is_completed"
                    :aria-label="t('Toggle task status')"
                    @update:model-value="toggleTask(task)"
                />
                <div class="min-w-0 flex-1">
                    <p
                        :class="
                            cn(
                                'truncate text-sm font-medium',
                                task.is_completed &&
                                    'text-muted-foreground line-through',
                            )
                        "
                    >
                        {{ task.title }}
                    </p>
                    <p
                        class="truncate text-xs"
                        :class="
                            isOverdue(task)
                                ? 'font-medium text-destructive'
                                : 'text-muted-foreground'
                        "
                    >
                        {{
                            [
                                task.due_at ? formatDate(task.due_at) : null,
                                task.assignee?.name,
                            ]
                                .filter(Boolean)
                                .join(' · ') || '—'
                        }}
                    </p>
                </div>
                <Badge :variant="priorityVariant[task.priority] ?? 'secondary'">
                    {{ task.priority_label }}
                </Badge>
            </div>
            <EmptyState
                v-if="tasks.data.length === 0"
                :icon="CheckSquare"
                :title="t('No tasks')"
                :description="t('All caught up! Create a task to plan your next action.')"
            >
                <Button v-if="can.create" variant="outline" size="sm" as-child>
                    <Link :href="create(teamSlug)">
                        <Plus class="h-4 w-4" /> {{ t('New task') }}
                    </Link>
                </Button>
            </EmptyState>
        </div>

        <!-- Desktop: table -->
        <div class="hidden overflow-hidden rounded-xl border bg-card md:block">
            <Table>
                <TableHeader>
                    <TableRow>
                        <TableHead class="w-10"></TableHead>
                        <TableHead>{{ t('Task') }}</TableHead>
                        <TableHead>{{ t('Due date') }}</TableHead>
                        <TableHead>{{ t('Priority') }}</TableHead>
                        <TableHead>{{ t('Assigned to') }}</TableHead>
                        <TableHead class="w-12"></TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow v-for="task in tasks.data" :key="task.id">
                        <TableCell>
                            <Checkbox
                                :model-value="task.is_completed"
                                :aria-label="t('Toggle task status')"
                                @update:model-value="toggleTask(task)"
                            />
                        </TableCell>
                        <TableCell>
                            <p
                                :class="
                                    cn(
                                        'font-medium',
                                        task.is_completed &&
                                            'text-muted-foreground line-through',
                                    )
                                "
                            >
                                {{ task.title }}
                            </p>
                            <p
                                v-if="task.related"
                                class="text-xs text-muted-foreground"
                            >
                                {{ task.related.label }}
                            </p>
                        </TableCell>
                        <TableCell
                            :class="
                                isOverdue(task) &&
                                'font-medium text-destructive'
                            "
                        >
                            {{ formatDate(task.due_at) }}
                        </TableCell>
                        <TableCell>
                            <Badge
                                :variant="
                                    priorityVariant[task.priority] ??
                                    'secondary'
                                "
                            >
                                {{ task.priority_label }}
                            </Badge>
                        </TableCell>
                        <TableCell>
                            <span
                                v-if="task.assignee"
                                class="inline-flex items-center gap-2"
                            >
                                <InitialsAvatar
                                    :name="task.assignee.name"
                                    size="sm"
                                />
                                {{ task.assignee.name }}
                            </span>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                        <TableCell>
                            <ConfirmDialog
                                :description="t('Delete this task?')"
                                @confirm="remove(task.id)"
                            >
                                <Button
                                    variant="ghost"
                                    size="icon"
                                    class="h-8 w-8"
                                    :aria-label="t('Delete task')"
                                >
                                    <Trash2
                                        class="h-4 w-4 text-muted-foreground"
                                    />
                                </Button>
                            </ConfirmDialog>
                        </TableCell>
                    </TableRow>

                    <TableEmpty v-if="tasks.data.length === 0" :colspan="6">
                        <EmptyState
                            :icon="CheckSquare"
                            :title="t('No tasks')"
                            :description="t('All caught up! Create a task to plan your next action.')"
                        >
                            <Button
                                v-if="can.create"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="create(teamSlug)">
                                    <Plus class="h-4 w-4" /> {{ t('New task') }}
                                </Link>
                            </Button>
                        </EmptyState>
                    </TableEmpty>
                </TableBody>
            </Table>
        </div>

        <Pagination
            :links="tasks.links"
            :from="tasks.from"
            :to="tasks.to"
            :total="tasks.total"
        />
    </div>
</template>
