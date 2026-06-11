<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Plus, Target } from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import Confetti from '@/components/crm/Confetti.vue';
import DealCard from '@/components/crm/DealCard.vue';
import Heading from '@/components/Heading.vue';
import { Button } from '@/components/ui/button';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import { board, create, move, show } from '@/routes/deals';
import type { BoardStage, PipelineRef, Team } from '@/types';

type Props = {
    pipeline: PipelineRef;
    pipelines: PipelineRef[];
    stages: BoardStage[];
    can: { manage: boolean };
};

const props = defineProps<Props>();

const { t, localeTag } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

// Deep clone via JSON (the payload is plain serializable data); structuredClone
// fails on Inertia's reactive proxies.
function clone(stages: BoardStage[]): BoardStage[] {
    return JSON.parse(JSON.stringify(stages)) as BoardStage[];
}

// Local, mutable copy for optimistic drag-and-drop; resynced to server truth.
const columns = ref<BoardStage[]>(clone(props.stages));
watch(
    () => props.stages,
    (next) => {
        columns.value = clone(next);
    },
);

const selectedPipeline = computed<string>({
    get: () => String(props.pipeline.id),
    set: (value) =>
        router.get(
            board(teamSlug.value).url,
            { pipeline: value },
            { preserveScroll: true },
        ),
});

const drag = ref<{ dealId: number; from: number } | null>(null);
const dragOverStage = ref<number | null>(null);
const celebrate = ref(false);

function celebrateWin() {
    celebrate.value = false;
    requestAnimationFrame(() => {
        celebrate.value = true;
        window.setTimeout(() => (celebrate.value = false), 1800);
    });
}

const currency = computed(
    () =>
        new Intl.NumberFormat(localeTag.value, {
            style: 'currency',
            currency: 'EUR',
            maximumFractionDigits: 0,
        }),
);

function onDragStart(dealId: number, stageId: number) {
    if (!props.can.manage) {
        return;
    }

    drag.value = { dealId, from: stageId };
}

function openDeal(dealId: number) {
    router.visit(show([teamSlug.value, dealId]).url);
}

function onDrop(toStageId: number, index: number | null) {
    dragOverStage.value = null;
    const current = drag.value;
    drag.value = null;

    if (!current) {
        return;
    }

    const fromColumn = columns.value.find((c) => c.id === current.from);
    const toColumn = columns.value.find((c) => c.id === toStageId);

    if (!fromColumn || !toColumn) {
        return;
    }

    const fromIndex = fromColumn.deals.findIndex(
        (d) => d.id === current.dealId,
    );

    if (fromIndex === -1) {
        return;
    }

    let insertAt = index ?? toColumn.deals.length;

    if (current.from === toStageId && fromIndex < insertAt) {
        insertAt -= 1;
    }

    // No-op self-drop: skip the needless round-trip.
    if (current.from === toStageId && insertAt === fromIndex) {
        return;
    }

    const [moved] = fromColumn.deals.splice(fromIndex, 1);
    toColumn.deals.splice(insertAt, 0, moved);

    if (toColumn.is_won && !fromColumn.is_won) {
        celebrateWin();
    }

    router.patch(
        move([teamSlug.value, current.dealId]).url,
        { stage_id: toStageId, position: insertAt },
        { preserveScroll: true },
    );
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [{ title: t('Pipeline'), href: board(props.currentTeam.slug) }]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('Pipeline')" />

    <div class="relative flex h-full flex-1 flex-col gap-6 p-4">
        <Confetti :active="celebrate" />

        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <Target class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Pipeline')"
                    :description="t('Track your sales opportunities')"
                />
            </div>
            <div class="flex items-center gap-2">
                <Select v-if="pipelines.length > 1" v-model="selectedPipeline">
                    <SelectTrigger class="w-52" :aria-label="t('Pipeline')">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="p in pipelines"
                            :key="p.id"
                            :value="String(p.id)"
                        >
                            {{ p.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Button v-if="can.manage" as-child>
                    <Link :href="create(teamSlug)">
                        <Plus class="h-4 w-4" /> {{ t('New opportunity') }}
                    </Link>
                </Button>
            </div>
        </div>

        <div class="flex flex-1 gap-4 overflow-x-auto pb-4">
            <section
                v-for="column in columns"
                :key="column.id"
                class="flex w-[290px] shrink-0 flex-col overflow-hidden rounded-2xl border border-border/70 bg-muted/30 transition-colors"
                :class="
                    cn(dragOverStage === column.id && 'ring-2 ring-primary/50')
                "
                @dragover.prevent="dragOverStage = column.id"
                @dragleave="dragOverStage = null"
                @drop="onDrop(column.id, null)"
            >
                <div
                    class="h-1 w-full"
                    :style="{
                        backgroundColor: column.color ?? 'var(--primary)',
                    }"
                />
                <header
                    class="flex items-center justify-between gap-2 px-3 py-2.5"
                    :class="
                        cn(
                            column.is_won && 'bg-emerald-500/10',
                            column.is_lost && 'bg-rose-500/10',
                        )
                    "
                >
                    <div class="flex min-w-0 items-center gap-2">
                        <span class="truncate text-sm font-semibold">{{
                            column.name
                        }}</span>
                        <span
                            class="rounded-full bg-background px-1.5 py-0.5 text-[11px] font-semibold text-muted-foreground"
                        >
                            {{ column.deals.length }}
                        </span>
                    </div>
                    <span class="shrink-0 text-xs font-semibold tabular-nums">
                        {{ currency.format(column.total_amount) }}
                    </span>
                </header>

                <div
                    class="flex min-h-28 flex-1 flex-col gap-2.5 p-2.5 transition-colors"
                    :class="cn(dragOverStage === column.id && 'bg-primary/5')"
                >
                    <div
                        v-for="(deal, index) in column.deals"
                        :key="deal.id"
                        :draggable="can.manage"
                        role="button"
                        tabindex="0"
                        class="rounded-xl outline-none focus-visible:ring-2 focus-visible:ring-ring"
                        @dragstart="onDragStart(deal.id, column.id)"
                        @dragover.prevent.stop="dragOverStage = column.id"
                        @drop.stop="onDrop(column.id, index)"
                        @click="openDeal(deal.id)"
                        @keydown.enter="openDeal(deal.id)"
                    >
                        <DealCard :deal="deal" />
                    </div>

                    <div
                        v-if="column.deals.length === 0"
                        class="flex flex-1 items-center justify-center rounded-xl border border-dashed border-border/80 px-2 py-8 text-center text-xs text-muted-foreground"
                    >
                        {{ t('Drop an opportunity here') }}
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
