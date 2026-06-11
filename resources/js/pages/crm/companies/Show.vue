<script setup lang="ts">
import { Head, Link, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowUpRight,
    Building2,
    CalendarDays,
    ClipboardList,
    Globe,
    History,
    Info,
    Mail,
    MapPin,
    Pencil,
    Phone,
    Plus,
    Target,
    Trash2,
    Trophy,
    User as UserIcon,
    Users,
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
import { destroy, edit, index } from '@/routes/companies';
import {
    create as createContact,
    show as showContact,
} from '@/routes/contacts';
import { board, create as createDeal } from '@/routes/deals';
import type {
    ActivityItem,
    CompanyDetail,
    CustomFieldDefinition,
    SelectOption,
    TaskItem,
    Team,
} from '@/types';

type RelatedContact = {
    id: number;
    full_name: string;
    email: string | null;
    job_title: string | null;
};

type RelatedDeal = {
    id: number;
    name: string;
    amount: number | null;
    status: string;
    stage: { name: string; color: string | null };
};

type CompanyStats = {
    contacts: number;
    open_deals: number;
    pipeline_value: number;
    won_value: number;
    deals_total: number;
};

const props = defineProps<{
    company: CompanyDetail;
    stats: CompanyStats;
    contacts: RelatedContact[];
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
        label: t('Contacts'),
        value: props.stats.contacts,
        icon: Users,
        accent: 'indigo' as const,
        format: 'number' as const,
    },
    {
        label: t('Open deals'),
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
        label: t('Won amount'),
        value: props.stats.won_value,
        icon: Trophy,
        accent: 'emerald' as const,
        format: 'currency' as const,
    },
]);

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const target = computed(() => ({
    type: 'company' as const,
    id: props.company.id,
}));

const deleteForm = useForm({});

const websiteHref = computed(() => {
    if (!props.company.website) {
        return null;
    }

    return /^https?:\/\//i.test(props.company.website)
        ? props.company.website
        : `https://${props.company.website}`;
});

const websiteLabel = computed(() => {
    if (!websiteHref.value) {
        return null;
    }

    try {
        return new URL(websiteHref.value).hostname.replace(/^www\./, '');
    } catch {
        return props.company.website;
    }
});

const location = computed(() =>
    [props.company.city, props.company.country].filter(Boolean).join(', '),
);

const fullAddress = computed(() =>
    [
        props.company.address,
        [props.company.postal_code, props.company.city]
            .filter(Boolean)
            .join(' '),
        props.company.country,
    ]
        .filter(Boolean)
        .join(', '),
);

const createdLabel = computed(() => {
    if (!props.company.created_at) {
        return null;
    }

    return new Intl.DateTimeFormat(localeTag.value, {
        day: 'numeric',
        month: 'long',
        year: 'numeric',
    }).format(new Date(props.company.created_at));
});

type InfoField = {
    icon: typeof Globe;
    label: string;
    value: string;
    href?: string;
    external?: boolean;
    avatarName?: string;
};

const fields = computed<InfoField[]>(() =>
    (
        [
            {
                icon: Globe,
                label: t('Website'),
                value: websiteLabel.value,
                href: websiteHref.value ?? undefined,
                external: true,
            },
            {
                icon: Phone,
                label: t('Phone'),
                value: props.company.phone,
                href: props.company.phone
                    ? `tel:${props.company.phone}`
                    : undefined,
            },
            { icon: MapPin, label: t('Address'), value: fullAddress.value },
            {
                icon: UserIcon,
                label: t('Manager'),
                value: props.company.owner?.name,
                avatarName: props.company.owner?.name,
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

const openTasksCount = computed(
    () => props.tasks.filter((task) => !task.is_completed).length,
);

function remove() {
    deleteForm.delete(destroy([teamSlug.value, props.company.id]).url, {
        preserveScroll: true,
    });
}

defineOptions({
    layout: (props: { currentTeam?: Team | null; company: CompanyDetail }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Companies'),
                          href: index(props.currentTeam.slug),
                      },
                      { title: props.company.name, href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="company.name" />

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
                        <Building2 class="h-7 w-7" />
                    </span>
                    <div class="min-w-0 space-y-1.5">
                        <div class="flex flex-wrap items-center gap-2">
                            <h1 class="text-2xl font-bold tracking-tight">
                                {{ company.name }}
                            </h1>
                            <Badge v-if="company.industry" variant="secondary">
                                {{ company.industry }}
                            </Badge>
                        </div>
                        <div
                            class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-muted-foreground"
                        >
                            <span
                                v-if="location"
                                class="inline-flex items-center gap-1.5"
                            >
                                <MapPin class="h-3.5 w-3.5" /> {{ location }}
                            </span>
                            <a
                                v-if="websiteHref"
                                :href="websiteHref"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center gap-1.5 transition-colors hover:text-primary"
                            >
                                <Globe class="h-3.5 w-3.5" /> {{ websiteLabel }}
                                <ArrowUpRight class="h-3 w-3" />
                            </a>
                            <span
                                v-if="company.owner"
                                class="inline-flex items-center gap-1.5"
                            >
                                <InitialsAvatar
                                    :name="company.owner.name"
                                    size="sm"
                                />
                                {{ company.owner.name }}
                            </span>
                        </div>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <Button v-if="can.update" variant="outline" as-child>
                        <Link :href="edit([teamSlug, company.id])">
                            <Pencil class="h-4 w-4" /> {{ t('Edit') }}
                        </Link>
                    </Button>
                    <ConfirmDialog
                        v-if="can.delete"
                        :description="
                            t('Permanently delete « :name » ?', {
                                name: company.name,
                            })
                        "
                        :processing="deleteForm.processing"
                        @confirm="remove"
                    >
                        <Button
                            variant="ghost"
                            :aria-label="t('Delete company')"
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
                            {{ t('Information') }}
                        </CardTitle>
                    </CardHeader>
                    <CardContent class="space-y-4">
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
                                    :target="
                                        field.external ? '_blank' : undefined
                                    "
                                    :rel="
                                        field.external
                                            ? 'noopener noreferrer'
                                            : undefined
                                    "
                                    class="inline-flex max-w-full items-center gap-1 font-medium transition-colors hover:text-primary hover:underline"
                                >
                                    <span class="truncate">{{
                                        field.value
                                    }}</span>
                                    <ArrowUpRight
                                        v-if="field.external"
                                        class="h-3 w-3 shrink-0"
                                    />
                                </a>
                                <p v-else class="font-medium break-words">
                                    {{ field.value }}
                                </p>
                            </div>
                        </div>
                        <div
                            v-if="fields.length === 0"
                            class="space-y-2 text-sm text-muted-foreground"
                        >
                            <p>{{ t('No information provided.') }}</p>
                            <Button
                                v-if="can.update"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link :href="edit([teamSlug, company.id])">
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
                            :model-value="company.custom_fields[definition.key]"
                            readonly
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader
                        class="flex flex-row items-center justify-between gap-2 space-y-0"
                    >
                        <CardTitle class="flex items-center gap-2">
                            <Users class="h-4 w-4 text-muted-foreground" />
                            {{ t('Contacts') }}
                            <Badge
                                v-if="contacts.length"
                                variant="secondary"
                                class="tabular-nums"
                            >
                                {{ contacts.length }}
                            </Badge>
                        </CardTitle>
                        <Button
                            v-if="can.update && contacts.length"
                            variant="ghost"
                            size="sm"
                            :aria-label="t('Add a contact')"
                            as-child
                        >
                            <Link
                                :href="
                                    createContact(teamSlug, {
                                        query: { company_id: company.id },
                                    })
                                "
                            >
                                <Plus class="h-4 w-4" />
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent class="space-y-2">
                        <div
                            v-for="contact in contacts"
                            :key="contact.id"
                            class="group relative flex items-center gap-3 rounded-lg border p-2.5 transition-colors hover:bg-muted/50"
                        >
                            <InitialsAvatar
                                :name="contact.full_name"
                                size="md"
                            />
                            <div class="min-w-0 flex-1">
                                <Link
                                    :href="showContact([teamSlug, contact.id])"
                                    class="text-sm font-medium after:absolute after:inset-0"
                                >
                                    {{ contact.full_name }}
                                </Link>
                                <p
                                    v-if="contact.job_title || contact.email"
                                    class="truncate text-xs text-muted-foreground"
                                >
                                    {{ contact.job_title ?? contact.email }}
                                </p>
                            </div>
                            <a
                                v-if="contact.email"
                                :href="`mailto:${contact.email}`"
                                :aria-label="
                                    t('Write to :name', {
                                        name: contact.full_name,
                                    })
                                "
                                class="relative z-10 rounded-md p-1.5 text-muted-foreground opacity-0 transition-opacity group-hover:opacity-100 hover:bg-muted hover:text-primary focus-visible:opacity-100"
                            >
                                <Mail class="h-4 w-4" />
                            </a>
                        </div>
                        <EmptyState
                            v-if="contacts.length === 0"
                            :icon="Users"
                            :title="t('No contacts')"
                            :description="t('Link this company\'s contacts to centralize your exchanges.')"
                        >
                            <Button
                                v-if="can.update"
                                variant="outline"
                                size="sm"
                                as-child
                            >
                                <Link
                                    :href="
                                        createContact(teamSlug, {
                                            query: { company_id: company.id },
                                        })
                                    "
                                >
                                    <Plus class="h-4 w-4" /> {{ t('Add a contact') }}
                                </Link>
                            </Button>
                        </EmptyState>
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
                                v-if="stats.deals_total"
                                variant="secondary"
                                class="tabular-nums"
                            >
                                {{ stats.deals_total }}
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
                                        query: { company_id: company.id },
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
                                v-if="stats.deals_total > deals.length"
                                :href="board(teamSlug)"
                                class="mt-3 inline-flex items-center gap-1 text-sm font-medium text-primary transition-colors hover:underline"
                            >
                                {{
                                    t(
                                        'View the :count opportunities in the pipeline',
                                        { count: stats.deals_total },
                                    )
                                }}
                                <ArrowUpRight class="h-3.5 w-3.5" />
                            </Link>
                        </template>
                        <EmptyState
                            v-else
                            :icon="Target"
                            :title="t('No opportunities')"
                            :description="t('Create a deal to track this company\'s sales potential.')"
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
                                            query: { company_id: company.id },
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
