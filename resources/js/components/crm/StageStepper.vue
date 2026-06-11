<script setup lang="ts">
import { Check, Trophy, X } from '@lucide/vue';
import { computed } from 'vue';
import { cn } from '@/lib/utils';

type Stage = {
    id: number;
    name: string;
    color: string | null;
    is_won: boolean;
    is_lost: boolean;
};

const props = defineProps<{
    stages: Stage[];
    currentId: number;
}>();

const current = computed(
    () => props.stages.find((stage) => stage.id === props.currentId) ?? null,
);

// Won and lost are mutually-exclusive terminal outcomes, not sequential steps.
// Show the open progression, then append only the outcome the deal reached
// (or neither while it is still open).
const visibleStages = computed(() => {
    const isWon = current.value?.is_won ?? false;
    const isLost = current.value?.is_lost ?? false;

    return props.stages.filter((stage) => {
        if (stage.is_won) {
            return isWon;
        }

        if (stage.is_lost) {
            return isLost;
        }

        return true;
    });
});

const currentIndex = computed(() =>
    visibleStages.value.findIndex((stage) => stage.id === props.currentId),
);

const isLost = computed(() => current.value?.is_lost ?? false);
const isWon = computed(() => current.value?.is_won ?? false);

function state(index: number): 'done' | 'current' | 'todo' {
    if (index < currentIndex.value) {
        return 'done';
    }

    if (index === currentIndex.value) {
        return 'current';
    }

    return 'todo';
}
</script>

<template>
    <div class="flex w-full items-center">
        <template v-for="(stage, index) in visibleStages" :key="stage.id">
            <!-- Connector before each node except the first -->
            <span
                v-if="index > 0"
                :class="
                    cn(
                        'h-0.5 flex-1 rounded-full transition-colors',
                        index <= currentIndex
                            ? isLost
                                ? 'bg-rose-500/60'
                                : 'bg-brand-gradient'
                            : 'bg-border',
                    )
                "
            />

            <div class="flex flex-col items-center gap-1.5">
                <span
                    :class="
                        cn(
                            'flex h-8 w-8 items-center justify-center rounded-full border-2 text-xs font-semibold transition-all',
                            state(index) === 'done' &&
                                'border-transparent bg-brand-gradient text-white shadow-sm',
                            state(index) === 'current' &&
                                isWon &&
                                'border-transparent bg-emerald-500 text-white shadow-sm',
                            state(index) === 'current' &&
                                isLost &&
                                'border-transparent bg-rose-500 text-white shadow-sm',
                            state(index) === 'current' &&
                                !isWon &&
                                !isLost &&
                                'border-transparent bg-brand-gradient text-white shadow-glow-violet motion-safe:animate-pulse',
                            state(index) === 'todo' &&
                                'border-border bg-card text-muted-foreground',
                        )
                    "
                >
                    <Check v-if="state(index) === 'done'" class="h-4 w-4" />
                    <Trophy
                        v-else-if="state(index) === 'current' && isWon"
                        class="h-4 w-4"
                    />
                    <X
                        v-else-if="state(index) === 'current' && isLost"
                        class="h-4 w-4"
                    />
                    <template v-else>{{ index + 1 }}</template>
                </span>
                <span
                    :class="
                        cn(
                            'max-w-[5.5rem] truncate text-center text-[11px] leading-tight',
                            state(index) === 'todo'
                                ? 'text-muted-foreground'
                                : 'font-medium text-foreground',
                        )
                    "
                >
                    {{ stage.name }}
                </span>
            </div>
        </template>
    </div>
</template>
