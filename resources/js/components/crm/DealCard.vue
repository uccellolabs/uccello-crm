<script setup lang="ts">
import { Building2, GripVertical } from '@lucide/vue';
import { computed } from 'vue';
import InitialsAvatar from '@/components/crm/InitialsAvatar.vue';
import type { DealCard } from '@/types';

const props = defineProps<{ deal: DealCard }>();

const amountLabel = computed(() => {
    if (props.deal.amount == null) {
        return null;
    }

    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: props.deal.currency || 'EUR',
        maximumFractionDigits: 0,
    }).format(props.deal.amount);
});
</script>

<template>
    <div
        class="group card-hover relative cursor-grab rounded-xl border border-border/70 bg-card p-3 shadow-card active:cursor-grabbing"
    >
        <GripVertical
            class="absolute top-2.5 right-1.5 h-4 w-4 text-muted-foreground/40 opacity-0 transition-opacity group-hover:opacity-100"
        />

        <p class="line-clamp-2 pr-4 text-sm font-semibold">{{ deal.name }}</p>

        <p
            v-if="amountLabel"
            class="text-gradient mt-1.5 text-base font-bold tracking-tight tabular-nums"
        >
            {{ amountLabel }}
        </p>

        <div class="mt-3 flex items-center justify-between gap-2">
            <span
                v-if="deal.company"
                class="inline-flex min-w-0 items-center gap-1 rounded-md bg-muted px-1.5 py-0.5 text-xs text-muted-foreground"
            >
                <Building2 class="h-3 w-3 shrink-0" />
                <span class="truncate">{{ deal.company.name }}</span>
            </span>
            <span v-else />

            <InitialsAvatar
                v-if="deal.owner"
                :name="deal.owner.name"
                size="sm"
            />
        </div>
    </div>
</template>
