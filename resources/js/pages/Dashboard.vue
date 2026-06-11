<script setup lang="ts">
import { Head, Link, usePage } from '@inertiajs/vue3';
import {
    Activity as ActivityIcon,
    AlertTriangle,
    Building2,
    Sparkles,
    Target,
    TrendingUp,
    Trophy,
    Users,
    Wallet,
} from '@lucide/vue';
import { computed } from 'vue';
import RadialProgress from '@/components/crm/charts/RadialProgress.vue';
import StageDistribution from '@/components/crm/charts/StageDistribution.vue';
import WeeklyBars from '@/components/crm/charts/WeeklyBars.vue';
import DateRangePicker from '@/components/crm/DateRangePicker.vue';
import KpiCard from '@/components/crm/KpiCard.vue';
import Heading from '@/components/Heading.vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';
import { dashboard } from '@/routes';
import { show as showDeal } from '@/routes/deals';
import type { Team } from '@/types';

type Metric = { value: number; previous?: number; amount?: number };

type Props = {
    range: { from: string; to: string };
    kpis: {
        new_companies: Metric;
        new_contacts: Metric;
        deals_created: Metric;
        deals_won: Metric;
        activities: Metric;
        pipeline_value: Metric;
        open_deals: Metric;
        conversion_rate: Metric;
        overdue_tasks: Metric;
    };
    charts: {
        weekly: { week: string; created: number; won: number }[];
        by_stage: { name: string; color: string | null; count: number; amount: number }[];
    };
    lists: {
        upcoming_tasks: {
            id: number;
            title: string;
            due_at: string | null;
            is_overdue: boolean;
        }[];
        recent_activities: {
            id: number;
            type_label: string;
            subject: string | null;
            occurred_at: string;
            user: string | null;
        }[];
        top_deals: {
            id: number;
            name: string;
            amount: number | null;
            currency: string;
            company: string | null;
            stage: string | null;
        }[];
    };
};

const props = defineProps<Props>();

const { t, localeTag } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const dashboardUrl = computed(() =>
    teamSlug.value ? dashboard(teamSlug.value).url : '/',
);

const currency = new Intl.NumberFormat(localeTag.value, {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});
const dateFmt = new Intl.DateTimeFormat(localeTag.value, { dateStyle: 'medium' });

function delta(metric: Metric): number | null {
    if (metric.previous == null || metric.previous === 0) {
        return null;
    }

    return Math.round(((metric.value - metric.previous) / metric.previous) * 100);
}

function formatDate(iso: string | null): string {
    return iso ? dateFmt.format(new Date(iso)) : '—';
}

const cards = computed(() => [
    {
        label: t('Pipeline value'),
        value: props.kpis.pipeline_value.value,
        format: 'currency' as const,
        icon: Wallet,
        accent: 'indigo' as const,
        hint: t('open opportunities'),
    },
    {
        label: t('Open opportunities'),
        value: props.kpis.open_deals.value,
        format: 'number' as const,
        icon: Target,
        accent: 'sky' as const,
        hint: t('in progress'),
    },
    {
        label: t('Deals won'),
        value: props.kpis.deals_won.value,
        format: 'number' as const,
        icon: Trophy,
        accent: 'emerald' as const,
        delta: delta(props.kpis.deals_won),
        hint: currency.format(props.kpis.deals_won.amount ?? 0),
    },
    {
        label: t('Conversion rate'),
        value: props.kpis.conversion_rate.value,
        format: 'percent' as const,
        icon: TrendingUp,
        accent: 'violet' as const,
        hint: t('won / closed'),
    },
    {
        label: t('New companies'),
        value: props.kpis.new_companies.value,
        format: 'number' as const,
        icon: Building2,
        accent: 'indigo' as const,
        delta: delta(props.kpis.new_companies),
    },
    {
        label: t('New contacts'),
        value: props.kpis.new_contacts.value,
        format: 'number' as const,
        icon: Users,
        accent: 'sky' as const,
        delta: delta(props.kpis.new_contacts),
    },
    {
        label: t('Activities'),
        value: props.kpis.activities.value,
        format: 'number' as const,
        icon: ActivityIcon,
        accent: 'amber' as const,
        delta: delta(props.kpis.activities),
    },
    {
        label: t('Overdue tasks'),
        value: props.kpis.overdue_tasks.value,
        format: 'number' as const,
        icon: AlertTriangle,
        accent: 'rose' as const,
        hint: t('to handle'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: [
            {
                title: useTranslations().t('Dashboard'),
                href: props.currentTeam ? dashboard(props.currentTeam.slug) : '/',
            },
        ],
    }),
});
</script>

<template>
    <Head :title="t('Dashboard')" />

    <div class="flex flex-1 flex-col gap-5 p-4">
        <!-- Header banner -->
        <div
            class="bg-mesh flex flex-wrap items-center justify-between gap-3 rounded-xl border border-border/60 px-4 py-3"
        >
            <div class="flex items-center gap-3">
                <span
                    class="flex h-9 w-9 items-center justify-center rounded-lg bg-brand-gradient text-white shadow-glow-violet"
                >
                    <Sparkles class="h-[18px] w-[18px]" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Dashboard')"
                    :description="t('Overview of your sales activity')"
                />
            </div>
            <DateRangePicker :url="dashboardUrl" :from="range.from" :to="range.to" />
        </div>

        <!-- KPI grid -->
        <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <KpiCard
                v-for="card in cards"
                :key="card.label"
                :label="card.label"
                :value="card.value"
                :format="card.format"
                :icon="card.icon"
                :accent="card.accent"
                :delta="card.delta"
                :hint="card.hint"
            />
        </div>

        <!-- Charts -->
        <div class="grid items-start gap-4 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardHeader>
                    <CardTitle>{{ t('Opportunities per week') }}</CardTitle>
                </CardHeader>
                <CardContent>
                    <WeeklyBars :data="charts.weekly" />
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('Pipeline health') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-5">
                    <div class="flex flex-col items-center gap-2 pt-1">
                        <RadialProgress :value="kpis.conversion_rate.value">
                            <span class="text-2xl font-bold tracking-tight tabular-nums">
                                {{ kpis.conversion_rate.value }}%
                            </span>
                            <span class="text-[11px] text-muted-foreground">
                                {{ t('conversion') }}
                            </span>
                        </RadialProgress>
                        <p class="text-xs text-muted-foreground">
                            {{ t(':count won', { count: kpis.deals_won.value }) }} ·
                            {{ t(':amount in progress', { amount: currency.format(kpis.pipeline_value.value) }) }}
                        </p>
                    </div>
                    <div class="border-t pt-4">
                        <StageDistribution :data="charts.by_stage" />
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Lists -->
        <div class="grid gap-4 lg:grid-cols-3">
            <Card>
                <CardHeader>
                    <CardTitle>{{ t('Top opportunities') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1.5">
                    <Link
                        v-for="deal in lists.top_deals"
                        :key="deal.id"
                        :href="showDeal([teamSlug, deal.id])"
                        class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm transition-colors hover:bg-muted/60"
                    >
                        <span class="min-w-0">
                            <span class="block truncate font-medium">{{ deal.name }}</span>
                            <span class="text-xs text-muted-foreground">
                                {{ deal.company ?? deal.stage }}
                            </span>
                        </span>
                        <span class="shrink-0 font-semibold tabular-nums">
                            {{ deal.amount != null ? currency.format(deal.amount) : '—' }}
                        </span>
                    </Link>
                    <p
                        v-if="lists.top_deals.length === 0"
                        class="px-2 py-6 text-center text-sm text-muted-foreground"
                    >
                        {{ t('No opportunities.') }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('Upcoming tasks') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1.5">
                    <div
                        v-for="task in lists.upcoming_tasks"
                        :key="task.id"
                        class="flex items-center justify-between gap-2 rounded-lg px-2.5 py-2 text-sm"
                    >
                        <span class="truncate">{{ task.title }}</span>
                        <span
                            class="shrink-0 text-xs font-medium"
                            :class="
                                task.is_overdue
                                    ? 'text-rose-600 dark:text-rose-400'
                                    : 'text-muted-foreground'
                            "
                        >
                            {{ formatDate(task.due_at) }}
                        </span>
                    </div>
                    <p
                        v-if="lists.upcoming_tasks.length === 0"
                        class="px-2 py-6 text-center text-sm text-muted-foreground"
                    >
                        {{ t('No upcoming tasks.') }}
                    </p>
                </CardContent>
            </Card>

            <Card>
                <CardHeader>
                    <CardTitle>{{ t('Latest activities') }}</CardTitle>
                </CardHeader>
                <CardContent class="space-y-1.5">
                    <div
                        v-for="activity in lists.recent_activities"
                        :key="activity.id"
                        class="rounded-lg px-2.5 py-2 text-sm"
                    >
                        <div class="flex items-center justify-between gap-2">
                            <span class="truncate font-medium">
                                {{ activity.subject || activity.type_label }}
                            </span>
                            <span class="shrink-0 text-xs text-muted-foreground">
                                {{ formatDate(activity.occurred_at) }}
                            </span>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ activity.type_label }}
                            <template v-if="activity.user">· {{ activity.user }}</template>
                        </p>
                    </div>
                    <p
                        v-if="lists.recent_activities.length === 0"
                        class="px-2 py-6 text-center text-sm text-muted-foreground"
                    >
                        {{ t('No activity.') }}
                    </p>
                </CardContent>
            </Card>
        </div>
    </div>
</template>
