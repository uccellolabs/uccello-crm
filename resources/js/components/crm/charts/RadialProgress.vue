<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        /** 0–100 */
        value: number;
        size?: number;
        stroke?: number;
        color?: string;
    }>(),
    { size: 116, stroke: 10, color: 'var(--primary)' },
);

const radius = computed(() => (props.size - props.stroke) / 2);
const circumference = computed(() => 2 * Math.PI * radius.value);

const animated = ref(0);

onMounted(() => {
    const reduce = window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    if (reduce) {
        animated.value = props.value;

        return;
    }

    const start = performance.now();
    const duration = 900;
    function frame(now: number) {
        const p = Math.min((now - start) / duration, 1);
        animated.value = props.value * (1 - Math.pow(1 - p, 3));

        if (p < 1) {
            requestAnimationFrame(frame);
        }
    }
    requestAnimationFrame(frame);
});

const dashoffset = computed(
    () => circumference.value * (1 - Math.min(animated.value, 100) / 100),
);
const gid = computed(() => `radial-${props.color.replace(/\W/g, '')}`);
</script>

<template>
    <div class="relative inline-flex" :style="{ width: `${size}px`, height: `${size}px` }">
        <svg :width="size" :height="size" class="-rotate-90">
            <defs>
                <linearGradient :id="gid" x1="0" y1="0" x2="1" y2="1">
                    <stop offset="0%" stop-color="var(--brand-cyan)" />
                    <stop offset="55%" :stop-color="color" />
                    <stop offset="100%" stop-color="var(--brand-indigo)" />
                </linearGradient>
            </defs>
            <circle
                :cx="size / 2"
                :cy="size / 2"
                :r="radius"
                fill="none"
                stroke="var(--muted)"
                :stroke-width="stroke"
            />
            <circle
                :cx="size / 2"
                :cy="size / 2"
                :r="radius"
                fill="none"
                :stroke="`url(#${gid})`"
                :stroke-width="stroke"
                stroke-linecap="round"
                :stroke-dasharray="circumference"
                :stroke-dashoffset="dashoffset"
            />
        </svg>
        <div class="absolute inset-0 flex flex-col items-center justify-center">
            <slot />
        </div>
    </div>
</template>
