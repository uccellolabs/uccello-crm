<script setup lang="ts">
import { ArrowDownRight, ArrowUpRight } from '@lucide/vue';
import { computed  } from 'vue';
import type {Component} from 'vue';
import { Card, CardContent } from '@/components/ui/card';
import { useCountUp } from '@/composables/useCountUp';
import { cn } from '@/lib/utils';

type Accent = 'indigo' | 'sky' | 'emerald' | 'amber' | 'rose' | 'violet';

const props = withDefaults(
    defineProps<{
        label: string;
        value: number;
        format?: 'number' | 'currency' | 'percent';
        hint?: string;
        delta?: number | null;
        icon?: Component;
        accent?: Accent;
    }>(),
    { format: 'number', accent: 'indigo' },
);

const accents: Record<Accent, { from: string; to: string }> = {
    indigo: { from: 'var(--brand-violet)', to: 'var(--brand-indigo)' },
    sky: { from: 'var(--brand-cyan)', to: 'var(--brand-violet)' },
    emerald: { from: 'var(--brand-emerald)', to: 'var(--brand-cyan)' },
    amber: { from: 'var(--brand-amber)', to: 'var(--brand-rose)' },
    rose: { from: 'var(--brand-rose)', to: 'var(--brand-violet)' },
    violet: { from: 'var(--brand-indigo)', to: 'var(--brand-violet)' },
};

const tileStyle = computed(() => ({
    backgroundImage: `linear-gradient(135deg, ${accents[props.accent].from}, ${accents[props.accent].to})`,
}));

const { display } = useCountUp(() => props.value, { decimals: 0 });

const formatted = computed(() => {
    if (props.format === 'currency') {
        return `${display.value} €`;
    }

    if (props.format === 'percent') {
        return `${display.value} %`;
    }

    return display.value;
});

const hasDelta = computed(() => props.delta != null && Number.isFinite(props.delta));
const isUp = computed(() => (props.delta ?? 0) >= 0);

// Clamp runaway percentages (e.g. 2100%) to a readable multiplier (×22).
const deltaLabel = computed(() => {
    const d = props.delta ?? 0;
    const abs = Math.abs(d);

    if (abs >= 1000) {
        return `×${Math.round(1 + abs / 100)}`;
    }

    return `${abs} %`;
});
</script>

<template>
    <Card class="card-hover h-full gap-0 py-4">
        <CardContent class="flex h-full flex-col gap-1.5">
            <div class="flex items-start justify-between gap-3">
                <p class="text-sm font-medium text-muted-foreground">{{ label }}</p>
                <span
                    v-if="icon"
                    class="flex h-8 w-8 shrink-0 items-center justify-center rounded-lg text-white shadow-sm"
                    :style="tileStyle"
                >
                    <component :is="icon" class="h-4 w-4" />
                </span>
            </div>

            <p class="text-2xl leading-none font-bold tracking-tight tabular-nums">
                {{ formatted }}
            </p>

            <div class="mt-auto flex min-h-[20px] items-center gap-2 text-xs">
                <span
                    v-if="hasDelta"
                    :class="
                        cn(
                            'inline-flex items-center gap-0.5 rounded-full px-1.5 py-0.5 font-semibold',
                            isUp
                                ? 'bg-emerald-500/12 text-emerald-600 dark:text-emerald-400'
                                : 'bg-rose-500/12 text-rose-600 dark:text-rose-400',
                        )
                    "
                >
                    <component
                        :is="isUp ? ArrowUpRight : ArrowDownRight"
                        class="h-3 w-3"
                    />
                    {{ deltaLabel }}
                </span>
                <span v-if="hint" class="truncate text-muted-foreground">
                    {{ hint }}
                </span>
            </div>
        </CardContent>
    </Card>
</template>
