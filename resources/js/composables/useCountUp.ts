import { computed, ref, watch  } from 'vue';
import type {Ref} from 'vue';

type CountUpOptions = {
    duration?: number;
    /** Number of fraction digits to display. */
    decimals?: number;
};

/**
 * Animates a numeric value from 0 → target with an ease-out curve using
 * requestAnimationFrame. Respects `prefers-reduced-motion` (jumps to the
 * final value instantly). Returns a formatted string ref.
 */
export function useCountUp(
    target: Ref<number> | (() => number),
    options: CountUpOptions = {},
) {
    const { duration = 900, decimals = 0 } = options;
    const current = ref(0);

    const resolve = typeof target === 'function' ? target : () => target.value;

    const prefersReduced =
        typeof window !== 'undefined' &&
        window.matchMedia('(prefers-reduced-motion: reduce)').matches;

    function animate(to: number) {
        if (prefersReduced || duration <= 0) {
            current.value = to;

            return;
        }

        const from = 0;
        const start = performance.now();

        function frame(now: number) {
            const elapsed = now - start;
            const progress = Math.min(elapsed / duration, 1);
            // easeOutCubic
            const eased = 1 - Math.pow(1 - progress, 3);
            current.value = from + (to - from) * eased;

            if (progress < 1) {
                requestAnimationFrame(frame);
            } else {
                current.value = to;
            }
        }

        requestAnimationFrame(frame);
    }

    watch(
        () => resolve(),
        (to) => animate(to),
        { immediate: true },
    );

    const display = computed(() =>
        current.value.toLocaleString('fr-FR', {
            minimumFractionDigits: decimals,
            maximumFractionDigits: decimals,
        }),
    );

    return { value: current, display };
}
