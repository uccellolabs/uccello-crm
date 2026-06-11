<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Briefcase,
    Building2,
    CalendarDays,
    ClipboardList,
    History,
    Info,
    Layers,
    Mail,
    Pencil,
    Phone,
    Plus,
    Target,
    Trash2,
    Trophy,
    User as UserIcon,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import ActivityTimeline from '@/components/crm/ActivityTimeline.vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import CustomFieldRenderer from '@/components/crm/CustomFieldRenderer.vue';
import DealMiniList from '@/components/crm/DealMiniList.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import InitialsAvatar from '@/components/crm/InitialsAvatar.vue';
import StatTile from '@/components/crm/StatTile.vue';
import TaskList from '@/components/crm/TaskList.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';
import { show as showCompany } from '@/routes/companies';
import { destroy, edit, index } from '@/routes/contacts';
import { board, create as createDeal } from '@/routes/deals';
import type {
    ActivityItem,
    ContactDetail,
    CustomFieldDefinition,
    SelectOption,
    TaskItem,
    Team,
} from '@/types';

type RelatedDeal = {
    id: number;
    name: string;
    amount: number | null;
    status: string;
    stage: { name: string; color: string | null };
};

type ContactStats = {
    deals: number;
    open_deals: number;
    pipeline_value: number;
    won_value: number;
};

const props = defineProps<{
    contact: ContactDetail;
    stats: ContactStats;
    deals: RelatedDeal[];
    activities: ActivityItem[];
    tasks: TaskItem[];
    members: SelectOption[];
    activityTypes: SelectOption[];
    taskPriorities: SelectOption[];
    customFields: CustomFieldDefinition[];
    can: { update: boolean; delete: boolean };
}>();

const { t, localeTag } = useTranslations();

const statTiles = computed(() => [
    {
        label: t('Deals'),
        value: props.stats.deals,
        icon: Layers,
        accent: 'indigo' as const,
        format: 'number' as const,
    },
    {
        label: t('Open'),
        value: props.stats.open_deals,
        icon: Target,
        accent: 'sky' as const,
        format: 'number' as const,
    },
    {
        label: t('Pipeline'),
        value: props.stats.pipeline_value,
        icon: Wallet,
        accent: 'violet' as const,
        format: 'currency' as const,
    },
    {
        label: t('Won'),
        value: props.stats.won_value,
        icon: Trophy,
        accent: 'emerald' as const,
        format: 'currency' as const,
    },
]);

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const target = computed(() => ({
    type: 'contact' as const,
    id: props.contact.id,
}));

const deleteForm = useForm({});

const createdLabel = computed(() => {
    if (!props.contact.created_at) {
        return null;
    }

    return new Intl.DateTimeFormat(localeTag.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(props.contact.created_at));
});

type InfoField = {
    icon: typeof Mail;
    label: string;
    value: string;
    href?: string;
    avatarName?: string;
};

const fields = computed<InfoField[]>(() =>
    (
        [
            {
                icon: Briefcase,
                label: t('Job title'),
                value: props.contact.job_title,
            },
            {
                icon: Mail,
                label: t('Email'),
                value: props.contact.email,
                href: props.contact.email
                    ? `mailto:${props.contact.email}`
                    : undefined,
            },
            {
                icon: Phone,
                label: t('Phone'),
                value: props.contact.phone,
                href: props.contact.phone
                    ? `tel:${props.contact.phone}`
                    : undefined,
            },
            {
                icon: UserIcon,
                label: t('Owner'),
                value: props.contact.owner?.name,
                avatarName: props.contact.owner?.name,
            },
            {
                icon: CalendarDays,
                label: t('In the CRM since'),
                value: createdLabel.value,
            },
        ] as Array<
            Omit<InfoField, 'value'> & { value: string | null | undefined }
        >
    ).filter((field): field is InfoField => Boolean(field.value)),
);

const dealCreateQuery = computed(() => ({
    contact_id: props.contact.id,
    ...(props.contact.company_id
        ? { company_id: props.contact.company_id }
        : {}),
}));

const openTasksCount = computed(
    () => props.tasks.filter((task) => !task.is_completed).length,
);

function remove() {
    deleteForm.delete(destroy([teamSlug.value, props.contact.id]).url, {
        preserveScroll: true,
    });
}

defineOptions({
    layout: (props: { currentTeam?: Team | null; contact: ContactDetail }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Contacts'),
                          href: index(props.currentTeam.slug),
                      },
                      { title: props.contact.full_name, href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="contact.full_name" />

    <div class="flex w-full flex-col gap-6 p-4">
        <div
            class="relative overflow-hidden rounded-2xl border bg-card shadow-card"
        >
            <div class="bg-mesh absolute inset-0" aria-hidden="true" />
            <div
                class="relative flex flex-wrap items-start justify-between gap-4 p-5 sm:p-6"
            >
                <div class="flex min-w-0 items-start gap-4">
                    <InitialsAvatar
                        :name="contact.full_name"
                        class="h-14 w-14 text-lg"
                    />
                    <div class="min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ contact.full_name }}
                            </h1>
                            <Badge v-if="contact.job_title" variant="secondary">
                                {{ contact.job_title }}
                            </Badge>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                        >
                            <Link
                                v-if="contact.company"
                                :href="
                                    showCompany([teamSlug, contact.company.id])
                                "
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <Building2 class="h-3.5 w-3.5" />
                                {{ contact.company.name }}
                            </Link>
                            <a
                                v-if="contact.email"
                                :href="`mailto:${contact.email}`"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <Mail class="h-3.5 w-3.5" />
                                {{ contact.email }}
                            </a>
                            <a
                                v-if="contact.phone"
                                :href="`tel:${contact.phone}`"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <Phone class="h-3.5 w-3.5" />
                                {{ contact.phone }}
                            </a>
                            <span
                                v-if="contact.owner"
                                class="inline-flex items-center gap-1.5"
                            >
                                <InitialsAvatar
                                    :name="contact.owner.name"
                                    size="sm"
                                />
                                {{ contact.owner.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="can.update" variant="outline" as-child>
                        <Link :href="edit([teamSlug, contact.id])">
                            <Pencil class="h-4 w-4" /> {{ t('Edit') }}
                        </Link>
                    </Button>
                    <ConfirmDialog
                        v-if="can.delete"
                        :description="
                            t('Permanently delete « :name »?', {
                                name: contact.full_name,
                            })
                        "
                        :processing="deleteForm.processing"
                        @confirm="remove"
                    >
                        <Button
                            variant="ghost"
                            :aria-label="t('Delete contact')"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </ConfirmDialog>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-2 gap-3 lg:grid-cols-4">
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
                            {{ t('Contact details') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <Link
                            v-if="contact.company"
                            :href="showCompany([teamSlug, contact.company.id])"
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
                                    {{ contact.company.name }}
                                </span>
                            </span>
                        </Link>

                        <div
                            v-for="field in fields"
                            :key="field.label"
                            class="flex items-start gap-3"
                        >
                            <InitialsAvatar
                                v-if="field.avatarName"
                                :name="field.avatarName"
                                size="sm"
                                class="mt-0.5"
                            />
                            <component
                                :is="field.icon"
                                v-else
                                class="mt-0.5 h-4 w-4 shrink-0 text-muted-foreground"
                            />
                            <div class="min-w-0">
                                <p class="text-xs text-muted-foreground">
                                    {{ field.label }}
                                </p>
                                <a
                                    v-if="field.href"
                                    :href="field.href"
                                    class="inline-flex max-w-full items-center gap-1 font-medium transition-colors hover:text-primary hover:underline"
                                >
                                    <span class="truncate">
                                        {{ field.value }}
                                    </span>
                                </a>
                                <p v-else class="font-medium break-words">
                                    {{ field.value }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="fields.length === 0 && !contact.company"
                            class="space-y-2 text-sm text-muted-foreground"
                        >
                            <p>{{ t('No contact details provided.') }}</p>
                            <Button
                                v-if="can.update"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="edit([teamSlug, contact.id])">
                                    <Pencil class="h-3.5 w-3.5" />
                                    {{ t('Complete the record') }}
                                </Link>
                            </Button>
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
                            :model-value="contact.custom_fields[definition.key]"
                            readonly
                        />
                    </CardContent>
                </Card>
            </div>

            <div class="space-y-6 lg:col-span-2">
                <Card>
                    <CardHeader
                        class="flex flex-row flex-wrap items-center justify-between gap-2 space-y-0"
                    >
                        <CardTitle class="flex items-center gap-2">
                            <Target class="h-4 w-4 text-muted-foreground" />
                            {{ t('Opportunities') }}
                            <Badge
                                v-if="stats.deals"
                                variant="secondary"
                                class="tabular-nums"
                            >
                                {{ stats.deals }}
                            </Badge>
                        </CardTitle>
                        <Button
                            v-if="can.update && deals.length"
                            variant="outline"
                            size="sm"
                            as-child
                        >
                            <Link
                                :href="
                                    createDeal(teamSlug, {
                                        query: dealCreateQuery,
                                    })
                                "
                            >
                                <Plus class="h-4 w-4" /> {{ t('New opportunity') }}
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent>
                        <template v-if="deals.length">
                            <DealMiniList :deals="deals" />
                            <Link
                                v-if="stats.deals > deals.length"
                                :href="board(teamSlug)"
                                class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:underline"
                            >
                                {{
                                    t(
                                        'View all :count opportunities in the pipeline',
                                        { count: stats.deals },
                                    )
                                }}
                                <ArrowUpRight class="h-3.5 w-3.5" />
                            </Link>
                        </template>
                        <EmptyState
                            v-else
                            :icon="Target"
                            :title="t('No opportunities')"
                            :description="
                                t(
                                    'Create a deal to track the commercial potential of this contact.',
                                )
                            "
                        >
                            <Button
                                v-if="can.update"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link
                                    :href="
                                        createDeal(teamSlug, {
                                            query: dealCreateQuery,
                                        })
                                    "
                                >
                                    <Plus class="h-4 w-4" />
                                    {{ t('Create an opportunity') }}
                                </Link>
                            </Button>
                        </EmptyState>
                    </CardContent>
                </Card>

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
                                {{ t(':count to do', { count: openTasksCount }) }}
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
