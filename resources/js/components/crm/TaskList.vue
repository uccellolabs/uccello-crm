<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import { Trash2 } from '@lucide/vue';
import { computed } from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/composables/useTranslations';
import {
    store as storeTask,
    toggle as toggleTask,
    destroy as destroyTask,
} from '@/routes/tasks';
import type { MorphTarget, SelectOption, TaskItem } from '@/types';

const props = defineProps<{
    tasks: TaskItem[];
    target: MorphTarget;
    priorities: SelectOption[];
    members: SelectOption[];
    canManage: boolean;
}>();

const { t, localeTag } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const form = useForm({
    title: '',
    due_at: '',
    priority: 'normal',
    assigned_to: null as number | null,
    taskable_type: props.target.type,
    taskable_id: props.target.id,
});

function submit() {
    form.post(storeTask(teamSlug.value).url, {
        preserveScroll: true,
        onSuccess: () => form.reset('title', 'due_at', 'assigned_to'),
    });
}

function toggle(task: TaskItem) {
    router.patch(
        toggleTask([teamSlug.value, task.id]).url,
        {},
        { preserveScroll: true },
    );
}

function remove(id: number) {
    router.delete(destroyTask([teamSlug.value, id]).url, {
        preserveScroll: true,
    });
}

const priorityVariant: Record<string, 'destructive' | 'secondary' | 'outline'> =
    {
        high: 'destructive',
        normal: 'secondary',
        low: 'outline',
    };

const dateFormatter = computed(
    () => new Intl.DateTimeFormat(localeTag.value, { dateStyle: 'medium' }),
);

function formatDate(iso: string | null): string | null {
    return iso ? dateFormatter.value.format(new Date(iso)) : null;
}
</script>

<template>
    <div class="space-y-4">
        <form
            v-if="canManage"
            class="space-y-2 rounded-lg border bg-muted/30 p-3"
            @submit.prevent="submit"
        >
            <Input
                v-model="form.title"
                :placeholder="t('New task…')"
                :aria-label="t('Task title')"
                required
            />
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center">
                <DatePicker
                    v-model="form.due_at"
                    class="sm:w-40"
                    :placeholder="t('Due date')"
                    :aria-label="t('Due date')"
                />
                <Select v-model="form.priority">
                    <SelectTrigger class="sm:w-32">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="priority in priorities"
                            :key="priority.value"
                            :value="String(priority.value)"
                        >
                            {{ priority.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <NullableSelect
                    v-model="form.assigned_to"
                    :options="members"
                    :placeholder="t('Assigned to')"
                    :empty-label="t('Unassigned')"
                    class="sm:w-48"
                />
                <Button
                    type="submit"
                    size="sm"
                    class="sm:ml-auto"
                    :disabled="form.processing"
                >
                    {{ t('Add') }}
                </Button>
            </div>
        </form>

        <ul v-if="tasks.length" class="divide-y rounded-lg border">
            <li
                v-for="task in tasks"
                :key="task.id"
                class="flex items-center gap-3 p-3"
            >
                <Checkbox
                    :model-value="task.is_completed"
                    :disabled="!canManage"
                    :aria-label="
                        task.is_completed
                            ? t('Mark as not done')
                            : t('Mark as done')
                    "
                    @update:model-value="toggle(task)"
                />
                <div class="min-w-0 flex-1">
                    <p
                        class="truncate text-sm font-medium"
                        :class="{
                            'text-muted-foreground line-through':
                                task.is_completed,
                        }"
                    >
                        {{ task.title }}
                    </p>
                    <p class="text-xs text-muted-foreground">
                        <template v-if="formatDate(task.due_at)">
                            {{ t('Due :date', { date: formatDate(task.due_at) ?? '' }) }}
                        </template>
                        <template v-if="task.assignee">
                            · {{ task.assignee.name }}
                        </template>
                    </p>
                </div>
                <Badge :variant="priorityVariant[task.priority] ?? 'secondary'">
                    {{ task.priority_label }}
                </Badge>
                <ConfirmDialog
                    v-if="canManage"
                    :description="t('Delete this task?')"
                    @confirm="remove(task.id)"
                >
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-7 w-7"
                        :aria-label="t('Delete task')"
                    >
                        <Trash2 class="h-3.5 w-3.5 text-muted-foreground" />
                    </Button>
                </ConfirmDialog>
            </li>
        </ul>

        <p v-else class="text-sm text-muted-foreground">{{ t('No tasks.') }}</p>
    </div>
</template>
