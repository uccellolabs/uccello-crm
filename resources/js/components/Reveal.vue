<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

const props = withDefaults(
    defineProps<{
        /** Stagger delay in milliseconds. */
        delay?: number;
        /** Render tag. */
        as?: string;
    }>(),
    { delay: 0, as: 'div' },
);

const shown = ref(false);

onMounted(() => {
    // Reveal on mount (not on scroll) so the first paint never looks broken;
    // reduced-motion users get the final state immediately.
    requestAnimationFrame(() => {
        shown.value = true;
    });
});

const style = computed(() => ({
    transitionDelay: `${props.delay}ms`,
}));
</script>

<template>
    <component
        :is="as"
        :style="style"
        class="motion-safe:transition-all motion-safe:duration-[400ms] motion-safe:ease-out"
        :class="
            shown
                ? 'opacity-100 translate-y-0'
                : 'motion-safe:translate-y-2 motion-safe:opacity-0'
        "
    >
        <slot />
    </component>
</template>
