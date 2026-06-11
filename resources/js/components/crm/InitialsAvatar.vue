<script setup lang="ts">
import { computed } from 'vue';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        name: string;
        size?: 'sm' | 'md' | 'lg';
        class?: string;
    }>(),
    { size: 'md' },
);

const initials = computed(() => {
    const parts = props.name.trim().split(/\s+/).filter(Boolean);

    if (parts.length === 0) {
        return '?';
    }

    if (parts.length === 1) {
        return parts[0].slice(0, 2).toUpperCase();
    }

    return (parts[0][0] + parts[parts.length - 1][0]).toUpperCase();
});

// Deterministic gradient pick — varied hues (warm + cool) so a roster of
// names reads as genuinely colorful, not mono-indigo.
const palettes = [
    ['var(--brand-violet)', 'var(--brand-indigo)'],
    ['var(--brand-cyan)', 'var(--brand-emerald)'],
    ['var(--brand-amber)', 'var(--brand-rose)'],
    ['var(--brand-rose)', 'var(--brand-amber)'],
    ['var(--brand-emerald)', 'var(--brand-cyan)'],
    ['var(--brand-indigo)', 'var(--brand-cyan)'],
    ['var(--brand-amber)', 'var(--brand-emerald)'],
    ['var(--brand-rose)', 'var(--brand-indigo)'],
];

const gradient = computed(() => {
    let hash = 0;

    for (let i = 0; i < props.name.length; i++) {
        hash = (hash * 31 + props.name.charCodeAt(i)) >>> 0;
    }

    const [from, to] = palettes[hash % palettes.length];

    return { backgroundImage: `linear-gradient(135deg, ${from}, ${to})` };
});

const sizeClass = computed(
    () =>
        ({
            sm: 'h-6 w-6 text-[10px]',
            md: 'h-8 w-8 text-xs',
            lg: 'h-10 w-10 text-sm',
        })[props.size],
);
</script>

<template>
    <span
        :style="gradient"
        :class="
            cn(
                'inline-flex shrink-0 items-center justify-center rounded-full font-semibold text-white ring-2 ring-background',
                sizeClass,
                props.class,
            )
        "
        :title="name"
    >
        {{ initials }}
    </span>
</template>
