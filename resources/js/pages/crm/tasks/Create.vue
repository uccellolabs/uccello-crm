<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import { CalendarClock, CheckSquare, Flag, Link2, UserPlus } from '@lucide/vue';
import { computed, watch } from 'vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import { index, store } from '@/routes/tasks';
import type { SelectOption, Team } from '@/types';

type RelatableType = 'company' | 'contact' | 'deal';

const props = defineProps<{
    assignees: SelectOption[];
    taskPriorities: SelectOption[];
    relatable: Record<RelatableType, SelectOption[]>;
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const form = useForm({
    title: '',
    description: '',
    due_at: '',
    priority: 'normal',
    assigned_to: null as number | null,
    taskable_type: null as RelatableType | null,
    taskable_id: null as number | null,
    to_index: true,
});

const typeOptions = computed<{ value: RelatableType; label: string }[]>(() => [
    { value: 'company', label: t('Company') },
    { value: 'contact', label: t('Contact') },
    { value: 'deal', label: t('Opportunity') },
]);

// reka-ui Select compares values as strings and disallows empty-string
// items, so the "none" sentinel converts at the boundary.
const TYPE_NONE = 'none';

const typeProxy = computed<string>({
    get: () => form.taskable_type ?? TYPE_NONE,
    set: (value) => {
        form.taskable_type =
            value === TYPE_NONE ? null : (value as RelatableType);
    },
});

const recordOptions = computed<SelectOption[]>(() =>
    form.taskable_type ? props.relatable[form.taskable_type] : [],
);

// Switching the record type invalidates the previously picked record.
watch(
    () => form.taskable_type,
    () => {
        form.taskable_id = null;
    },
);

function submit() {
    form.post(store(teamSlug.value).url, { preserveScroll: true });
}

const tips = computed(() => [
    {
        icon: CalendarClock,
        label: t('Set a due date'),
        text: t('A dated task shows up at the right time and turns red once overdue.'),
    },
    {
        icon: Flag,
        label: t('Prioritize honestly'),
        text: t('Reserve high priority for actions that block a deal.'),
    },
    {
        icon: UserPlus,
        label: t('Assign an owner'),
        text: t('An assigned task has a clear owner — nobody passes the buck.'),
    },
    {
        icon: Link2,
        label: t('Link to a record'),
        text: t('Linked to a company, contact or deal, the task keeps all its context.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      { title: t('Tasks'), href: index(props.currentTeam.slug) },
                      { title: t('New task'), href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('New task')" />

    <FormShell
        :icon="CheckSquare"
        :title="t('New task')"
        :description="t('Plan an action to take, standalone or linked to a record')"
    >
        <form class="space-y-6" @submit.prevent="submit">
            <div class="grid gap-2">
                <Label for="title">
                    {{ t('Title') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="title"
                    v-model="form.title"
                    :placeholder="t('E.g. Call the client back')"
                    required
                />
                <InputError :message="form.errors.title" />
            </div>

            <div class="grid gap-2">
                <Label for="description">{{ t('Description') }}</Label>
                <Textarea
                    id="description"
                    v-model="form.description"
                    :placeholder="t('Context, points to cover…')"
                    rows="3"
                />
                <InputError :message="form.errors.description" />
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div class="grid gap-2">
                    <Label for="due_at">{{ t('Due date') }}</Label>
                    <DatePicker
                        id="due_at"
                        v-model="form.due_at"
                        :placeholder="t('Choose a date')"
                    />
                    <InputError :message="form.errors.due_at" />
                </div>
                <div class="grid gap-2">
                    <Label for="priority">{{ t('Priority') }}</Label>
                    <Select v-model="form.priority">
                        <SelectTrigger id="priority">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="priority in taskPriorities"
                                :key="priority.value"
                                :value="String(priority.value)"
                            >
                                {{ priority.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <InputError :message="form.errors.priority" />
                </div>
            </div>

            <div class="grid gap-2">
                <Label for="assigned_to">{{ t('Assigned to') }}</Label>
                <NullableSelect
                    id="assigned_to"
                    v-model="form.assigned_to"
                    :options="assignees"
                    :placeholder="t('Assigned to')"
                    :empty-label="t('Unassigned')"
                />
                <InputError :message="form.errors.assigned_to" />
            </div>

            <div class="space-y-4 border-t pt-6">
                <h3 class="text-sm font-medium text-muted-foreground">
                    {{ t('Link to a record (optional)') }}
                </h3>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div class="grid gap-2">
                        <Label for="taskable_type">{{ t('Record type') }}</Label>
                        <Select v-model="typeProxy">
                            <SelectTrigger id="taskable_type">
                                <SelectValue :placeholder="t('None')" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem :value="TYPE_NONE">
                                    {{ t('None') }}
                                </SelectItem>
                                <SelectItem
                                    v-for="type in typeOptions"
                                    :key="type.value"
                                    :value="type.value"
                                >
                                    {{ type.label }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.taskable_type" />
                    </div>
                    <div v-if="form.taskable_type" class="grid gap-2">
                        <Label for="taskable_id">{{ t('Record') }}</Label>
                        <NullableSelect
                            id="taskable_id"
                            v-model="form.taskable_id"
                            :options="recordOptions"
                            :placeholder="t('Choose a record')"
                            :empty-label="t('None')"
                        />
                        <InputError :message="form.errors.taskable_id" />
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3 border-t pt-6">
                <Button type="submit" :disabled="form.processing">
                    {{ t('Create task') }}
                </Button>
                <Button variant="ghost" as-child>
                    <Link :href="index(teamSlug)">{{ t('Cancel') }}</Link>
                </Button>
            </div>
        </form>

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
