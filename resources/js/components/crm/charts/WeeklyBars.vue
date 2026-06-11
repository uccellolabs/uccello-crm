<script setup lang="ts">
import { computed } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

type Point = { week: string; created: number; won: number };

const props = defineProps<{ data: Point[] }>();

const { t, localeTag } = useTranslations();

const width = 640;
const height = 220;
const padding = { top: 16, right: 12, bottom: 28, left: 28 };

const innerW = width - padding.left - padding.right;
const innerH = height - padding.top - padding.bottom;

const max = computed(() =>
    Math.max(1, ...props.data.flatMap((d) => [d.created, d.won])),
);

const groupWidth = computed(() =>
    props.data.length ? innerW / props.data.length : innerW,
);

const barWidth = computed(() => Math.min(18, groupWidth.value / 3));

function y(value: number): number {
    return padding.top + innerH - (value / max.value) * innerH;
}

function barHeight(value: number): number {
    return (value / max.value) * innerH;
}

const labelFormatter = new Intl.DateTimeFormat(localeTag.value, {
    day: '2-digit',
    month: '2-digit',
});

function weekLabel(week: string): string {
    return labelFormatter.format(new Date(week));
}

const gridLines = computed(() => {
    const steps = 4;

    return Array.from({ length: steps + 1 }, (_, i) => {
        const value = (max.value / steps) * i;

        return { value: Math.round(value), y: y(value) };
    });
});
</script>

<template>
    <div class="space-y-2">
        <div class="flex items-center gap-4 text-xs text-muted-foreground">
            <span class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-sm bg-[var(--brand-violet)]" />
                {{ t('Created') }}
            </span>
            <span class="flex items-center gap-1.5">
                <span class="h-2.5 w-2.5 rounded-sm bg-[var(--brand-emerald)]" />
                {{ t('Won (plural)') }}
            </span>
        </div>

        <svg
            :viewBox="`0 0 ${width} ${height}`"
            class="w-full"
            role="img"
            :aria-label="t('Opportunities created and won per week')"
        >
            <defs>
                <linearGradient id="bar-created" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--brand-violet)" />
                    <stop offset="100%" stop-color="var(--brand-indigo)" />
                </linearGradient>
                <linearGradient id="bar-won" x1="0" y1="0" x2="0" y2="1">
                    <stop offset="0%" stop-color="var(--brand-emerald)" />
                    <stop offset="100%" stop-color="var(--brand-cyan)" />
                </linearGradient>
            </defs>

            <g v-for="line in gridLines" :key="line.value">
                <line
                    :x1="padding.left"
                    :x2="width - padding.right"
                    :y1="line.y"
                    :y2="line.y"
                    class="stroke-border"
                    stroke-width="1"
                />
                <text
                    :x="padding.left - 6"
                    :y="line.y + 3"
                    text-anchor="end"
                    class="fill-muted-foreground text-[9px]"
                >
                    {{ line.value }}
                </text>
            </g>

            <g v-for="(point, index) in data" :key="point.week">
                <rect
                    class="bar-grow"
                    :style="{ animationDelay: `${index * 45}ms` }"
                    :x="padding.left + index * groupWidth + groupWidth / 2 - barWidth - 1.5"
                    :y="y(point.created)"
                    :width="barWidth"
                    :height="barHeight(point.created)"
                    rx="3"
                    fill="url(#bar-created)"
                />
                <rect
                    class="bar-grow"
                    :style="{ animationDelay: `${index * 45 + 80}ms` }"
                    :x="padding.left + index * groupWidth + groupWidth / 2 + 1.5"
                    :y="y(point.won)"
                    :width="barWidth"
                    :height="barHeight(point.won)"
                    rx="3"
                    fill="url(#bar-won)"
                />
                <text
                    :x="padding.left + index * groupWidth + groupWidth / 2"
                    :y="height - 10"
                    text-anchor="middle"
                    class="fill-muted-foreground text-[9px]"
                >
                    {{ weekLabel(point.week) }}
                </text>
            </g>
        </svg>
    </div>
</template>
