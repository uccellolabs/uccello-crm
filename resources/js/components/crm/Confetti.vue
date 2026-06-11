<script setup lang="ts">
import { computed } from 'vue';

const props = withDefaults(defineProps<{ active: boolean; count?: number }>(), {
    count: 70,
});

const colors = [
    'var(--brand-violet)',
    'var(--brand-cyan)',
    'var(--brand-emerald)',
    'var(--brand-amber)',
    'var(--brand-rose)',
    'var(--brand-indigo)',
];

// Deterministic pseudo-random so SSR/build stays stable; varied per index.
function rand(i: number, salt: number): number {
    const x = Math.sin(i * 12.9898 + salt * 78.233) * 43758.5453;

    return x - Math.floor(x);
}

const pieces = computed(() =>
    Array.from({ length: props.count }, (_, i) => ({
        left: `${rand(i, 1) * 100}%`,
        bg: colors[i % colors.length],
        delay: `${rand(i, 2) * 250}ms`,
        duration: `${900 + rand(i, 3) * 900}ms`,
        dx: `${(rand(i, 4) - 0.5) * 220}px`,
        size: `${6 + Math.round(rand(i, 5) * 6)}px`,
        radius: rand(i, 6) > 0.5 ? '9999px' : '2px',
    })),
);
</script>

<template>
    <div
        v-if="active"
        class="pointer-events-none absolute inset-0 z-20 overflow-hidden"
        aria-hidden="true"
    >
        <span
            v-for="(p, i) in pieces"
            :key="i"
            class="confetti-piece absolute top-0"
            :style="{
                left: p.left,
                width: p.size,
                height: p.size,
                backgroundColor: p.bg,
                borderRadius: p.radius,
                '--dx': p.dx,
                animation: `confetti-fall ${p.duration} cubic-bezier(0.22,1,0.36,1) ${p.delay} forwards`,
            }"
        />
    </div>
</template>
