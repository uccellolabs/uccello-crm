<script setup lang="ts">
import { Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { show as showDeal } from '@/routes/deals';
import type { Team } from '@/types';

type MiniDeal = {
    id: number;
    name: string;
    amount: number | null;
    status: string;
    stage: { name: string; color: string | null };
};

defineProps<{
    deals: MiniDeal[];
}>();

const page = usePage<{ currentTeam?: Team | null }>();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

function amountLabel(amount: number | null): string {
    if (amount == null) {
        return '—';
    }

    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
        maximumFractionDigits: 0,
    }).format(amount);
}
</script>

<template>
    <div class="space-y-2">
        <Link
            v-for="deal in deals"
            :key="deal.id"
            :href="showDeal([teamSlug, deal.id])"
            class="card-hover flex items-center justify-between gap-3 rounded-lg border p-3 text-sm"
        >
            <div class="flex min-w-0 items-center gap-2.5">
                <span
                    class="h-2.5 w-2.5 shrink-0 rounded-full"
                    :style="{ backgroundColor: deal.stage.color ?? 'var(--brand-indigo)' }"
                    :title="deal.stage.name"
                />
                <div class="min-w-0">
                    <p class="truncate font-medium">{{ deal.name }}</p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{ deal.stage.name }}
                    </p>
                </div>
            </div>
            <span class="shrink-0 font-semibold tabular-nums">
                {{ amountLabel(deal.amount) }}
            </span>
        </Link>
    </div>
</template>
