<script setup lang="ts">
import { computed } from 'vue';
import type { Component } from 'vue';
import { useCountUp } from '@/composables/useCountUp';

type Accent = 'indigo' | 'sky' | 'emerald' | 'amber' | 'rose' | 'violet';

const props = withDefaults(
    defineProps<{
        label: string;
        value: number;
        format?: 'number' | 'currency' | 'percent';
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

// Currency reads cleaner without count-up decimals; keep integers.
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
</script>

<template>
    <div
        class="card-hover flex items-center gap-3 rounded-xl border bg-card p-3 shadow-card"
    >
        <span
            v-if="icon"
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl text-white shadow-sm"
            :style="tileStyle"
        >
            <component :is="icon" class="h-5 w-5" />
        </span>
        <div class="min-w-0">
            <p class="truncate text-xs font-medium text-muted-foreground">
                {{ label }}
            </p>
            <p class="text-xl leading-tight font-bold tracking-tight tabular-nums">
                {{ formatted }}
            </p>
        </div>
    </div>
</template>
