<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(
    defineProps<{
        data: number[];
        color?: string;
        height?: number;
    }>(),
    { color: 'var(--primary)', height: 36 },
);

const width = 120;

const path = computed(() => {
    const values = props.data.length ? props.data : [0, 0];
    const max = Math.max(...values, 1);
    const min = Math.min(...values, 0);
    const span = max - min || 1;
    const step = width / Math.max(values.length - 1, 1);

    const points = values.map((v, i) => {
        const x = i * step;
        const y = props.height - ((v - min) / span) * (props.height - 4) - 2;

        return [x, y] as const;
    });

    const line = points
        .map(([x, y], i) => `${i === 0 ? 'M' : 'L'}${x.toFixed(1)},${y.toFixed(1)}`)
        .join(' ');
    const area = `${line} L${width},${props.height} L0,${props.height} Z`;

    return { line, area };
});

const gradientId = computed(
    () => `spark-${Math.abs(props.data.reduce((a, b) => a + b, props.data.length) | 0)}`,
);
</script>

<template>
    <svg
        :viewBox="`0 0 ${width} ${height}`"
        :height="height"
        class="w-full overflow-visible"
        preserveAspectRatio="none"
        aria-hidden="true"
    >
        <defs>
            <linearGradient :id="gradientId" x1="0" y1="0" x2="0" y2="1">
                <stop offset="0%" :stop-color="color" stop-opacity="0.28" />
                <stop offset="100%" :stop-color="color" stop-opacity="0" />
            </linearGradient>
        </defs>
        <path :d="path.area" :fill="`url(#${gradientId})`" />
        <path
            :d="path.line"
            fill="none"
            :stroke="color"
            stroke-width="2"
            stroke-linecap="round"
            stroke-linejoin="round"
        />
    </svg>
</template>
