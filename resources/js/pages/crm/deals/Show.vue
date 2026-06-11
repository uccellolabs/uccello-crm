<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    Activity as ActivityIcon,
    Building2,
    CalendarClock,
    CheckSquare,
    ClipboardList,
    Clock,
    History,
    Info,
    Pencil,
    Percent,
    Target,
    Trash2,
    User as UserIcon,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import ActivityTimeline from '@/components/crm/ActivityTimeline.vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import CustomFieldRenderer from '@/components/crm/CustomFieldRenderer.vue';
import InitialsAvatar from '@/components/crm/InitialsAvatar.vue';
import StageStepper from '@/components/crm/StageStepper.vue';
import StatTile from '@/components/crm/StatTile.vue';
import TaskList from '@/components/crm/TaskList.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';
import { show as showCompany } from '@/routes/companies';
import { show as showContact } from '@/routes/contacts';
import { board, destroy, edit } from '@/routes/deals';
import type {
    ActivityItem,
    CustomFieldDefinition,
    DealDetail,
    SelectOption,
    TaskItem,
    Team,
} from '@/types';

type DealStage = {
    id: number;
    name: string;
    color: string | null;
    is_won: boolean;
    is_lost: boolean;
};

type DealStats = {
    amount: number | null;
    probability: number | null;
    days_open: number;
    tasks_count: number;
    activities_count: number;
};

const props = defineProps<{
    deal: DealDetail;
    stats: DealStats;
    stages: DealStage[];
    activities: ActivityItem[];
    tasks: TaskItem[];
    members: SelectOption[];
    activityTypes: SelectOption[];
    taskPriorities: SelectOption[];
    customFields: CustomFieldDefinition[];
    can: { update: boolean; delete: boolean };
}>();

const { t } = useTranslations();

const statTiles = computed(() => {
    const tiles: {
        label: string;
        value: number;
        icon: typeof Wallet;
        accent: 'indigo' | 'sky' | 'emerald' | 'amber' | 'rose' | 'violet';
        format: 'number' | 'currency' | 'percent';
    }[] = [];

    if (props.stats.amount != null) {
        tiles.push({
            label: t('Amount'),
            value: props.stats.amount,
            icon: Wallet,
            accent: 'violet',
            format: 'currency',
        });
    }

    if (props.stats.probability != null) {
        tiles.push({
            label: t('Probability'),
            value: props.stats.probability,
            icon: Percent,
            accent: 'sky',
            format: 'percent',
        });
    }

    tiles.push({
        label: t('Days open'),
        value: props.stats.days_open,
        icon: Clock,
        accent: 'amber',
        format: 'number',
    });
    tiles.push({
        label: t('Open tasks'),
        value: props.stats.tasks_count,
        icon: CheckSquare,
        accent: 'indigo',
        format: 'number',
    });
    tiles.push({
        label: t('Activities'),
        value: props.stats.activities_count,
        icon: ActivityIcon,
        accent: 'emerald',
        format: 'number',
    });

    return tiles;
});

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const target = computed(() => ({ type: 'deal' as const, id: props.deal.id }));
const deleteForm = useForm({});

const openTasksCount = computed(
    () => props.tasks.filter((task) => !task.is_completed).length,
);

const amountLabel = computed(() =>
    props.deal.amount == null
        ? null
        : new Intl.NumberFormat('fr-FR', {
              style: 'currency',
              currency: props.deal.currency || 'EUR',
              maximumFractionDigits: 0,
          }).format(props.deal.amount),
);

const statusVariant = computed<'default' | 'secondary' | 'destructive'>(() => {
    if (props.deal.status === 'won') {
        return 'default';
    }

    if (props.deal.status === 'lost') {
        return 'destructive';
    }

    return 'secondary';
});

const closeDateLabel = computed(() =>
    props.deal.expected_close_date
        ? new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' }).format(
              new Date(props.deal.expected_close_date),
          )
        : null,
);

function remove() {
    deleteForm.delete(destroy([teamSlug.value, props.deal.id]).url, {
        preserveScroll: true,
    });
}

defineOptions({
    layout: (props: { currentTeam?: Team | null; deal: DealDetail }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  { title: t('Pipeline'), href: board(props.currentTeam.slug) },
                  { title: props.deal.name, href: '#' },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="deal.name" />

    <div class="flex w-full flex-col gap-6 p-4">
        <div
            class="relative overflow-hidden rounded-2xl border bg-card shadow-card"
        >
            <div class="bg-mesh absolute inset-0" aria-hidden="true" />
            <div
                class="relative flex flex-wrap items-start justify-between gap-4 p-5 sm:p-6"
            >
                <div class="flex min-w-0 items-start gap-4">
                    <span
                        class="bg-brand-gradient shadow-glow-violet flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-white"
                    >
                        <Target class="h-7 w-7" />
                    </span>
                    <div class="min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ deal.name }}
                            </h1>
                            <Badge
                                v-if="deal.stage"
                                :style="{
                                    backgroundColor:
                                        deal.stage.color ?? undefined,
                                    color: deal.stage.color
                                        ? '#fff'
                                        : undefined,
                                }"
                            >
                                {{ deal.stage.name }}
                            </Badge>
                            <Badge :variant="statusVariant">
                                {{ deal.status_label }}
                            </Badge>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                        >
                            <span
                                v-if="amountLabel"
                                class="font-semibold text-foreground tabular-nums"
                            >
                                {{ amountLabel }}
                            </span>
                            <Link
                                v-if="deal.company"
                                :href="showCompany([teamSlug, deal.company.id])"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <Building2 class="h-3.5 w-3.5" />
                                {{ deal.company.name }}
                            </Link>
                            <Link
                                v-if="deal.contact"
                                :href="showContact([teamSlug, deal.contact.id])"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <UserIcon class="h-3.5 w-3.5" />
                                {{ deal.contact.name }}
                            </Link>
                            <span
                                v-if="closeDateLabel"
                                class="inline-flex items-center gap-1.5"
                            >
                                <CalendarClock class="h-3.5 w-3.5" />
                                {{ t('Close :date', { date: closeDateLabel }) }}
                            </span>
                            <span
                                v-if="deal.owner"
                                class="inline-flex items-center gap-1.5"
                            >
                                <InitialsAvatar
                                    :name="deal.owner.name"
                                    size="sm"
                                />
                                {{ deal.owner.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="can.update" variant="outline" as-child>
                        <Link :href="edit([teamSlug, deal.id])">
                            <Pencil class="h-4 w-4" /> {{ t('Edit') }}
                        </Link>
                    </Button>
                    <ConfirmDialog
                        v-if="can.delete"
                        :description="
                            t('Permanently delete « :name » ?', {
                                name: deal.name,
                            })
                        "
                        :processing="deleteForm.processing"
                        @confirm="remove"
                    >
                        <Button
                            variant="ghost"
                            :aria-label="t('Delete opportunity')"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </ConfirmDialog>
                </div>
            </div>
        </div>

        <Card v-if="stages.length > 1">
            <CardContent class="overflow-x-auto py-5">
                <StageStepper
                    :stages="stages"
                    :current-id="deal.pipeline_stage_id"
                />
            </CardContent>
        </Card>

        <div class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
            <StatTile
                v-for="tile in statTiles"
                :key="tile.label"
                :label="tile.label"
                :value="tile.value"
                :icon="tile.icon"
                :accent="tile.accent"
                :format="tile.format"
            />
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <div class="space-y-6 lg:col-span-1">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Info class="h-4 w-4 text-muted-foreground" />
                            {{ t('Details') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <Link
                            v-if="deal.company"
                            :href="showCompany([teamSlug, deal.company.id])"
                            class="card-hover flex items-center gap-3 rounded-lg border p-3"
                        >
                            <span
                                class="bg-brand-gradient flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white"
                            >
                                <Building2 class="h-4 w-4" />
                            </span>
                            <span class="min-w-0">
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    {{ t('Company') }}
                                </span>
                                <span class="block truncate font-medium">
                                    {{ deal.company.name }}
                                </span>
                            </span>
                        </Link>
                        <Link
                            v-if="deal.contact"
                            :href="showContact([teamSlug, deal.contact.id])"
                            class="card-hover flex items-center gap-3 rounded-lg border p-3"
                        >
                            <InitialsAvatar
                                :name="deal.contact.name"
                                size="md"
                            />
                            <span class="min-w-0">
                                <span
                                    class="block text-xs text-muted-foreground"
                                >
                                    {{ t('Contact') }}
                                </span>
                                <span class="block truncate font-medium">
                                    {{ deal.contact.name }}
                                </span>
                            </span>
                        </Link>
                        <div
                            v-if="closeDateLabel"
                            class="flex items-start gap-3"
                        >
                            <CalendarClock
                                class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                            />
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('Expected close date') }}
                                </p>
                                <p class="font-medium">{{ closeDateLabel }}</p>
                            </div>
                        </div>
                        <div v-if="deal.owner" class="flex items-start gap-3">
                            <InitialsAvatar
                                :name="deal.owner.name"
                                size="sm"
                                class="mt-0.5"
                            />
                            <div>
                                <p class="text-xs text-muted-foreground">
                                    {{ t('Responsible') }}
                                </p>
                                <p class="font-medium">{{ deal.owner.name }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card v-if="customFields.length">
                    <CardHeader>
                        <CardTitle>{{ t('Custom fields') }}</CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <CustomFieldRenderer
                            v-for="definition in customFields"
                            :key="definition.id"
                            :definition="definition"
                            :model-value="deal.custom_fields[definition.key]"
                            readonly
                        />
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <ClipboardList
                                class="h-4 w-4 text-muted-foreground"
                            />
                            {{ t('Tasks') }}
                            <Badge
                                v-if="openTasksCount"
                                variant="secondary"
                                class="tabular-nums"
                            >
                                {{
                                    t(':count to do', { count: openTasksCount })
                                }}
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <TaskList
                            :tasks="tasks"
                            :target="target"
                            :priorities="taskPriorities"
                            :members="members"
                            :can-manage="can.update"
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <History class="h-4 w-4 text-muted-foreground" />
                            {{ t('Activity') }}
                            <Badge
                                v-if="activities.length"
                                variant="secondary"
                                class="tabular-nums"
                            >
                                {{ activities.length }}
                            </Badge>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <ActivityTimeline
                            :activities="activities"
                            :target="target"
                            :types="activityTypes"
                            :can-manage="can.update"
                        />
                    </CardContent>
                </Card>
            </div>
        </div>
    </div>
</template>
