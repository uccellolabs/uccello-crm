<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

type Stage = { name: string; color: string | null; count: number; amount: number };

const props = defineProps<{ data: Stage[] }>();

const { t, localeTag } = useTranslations();

const max = computed(() => Math.max(1, ...props.data.map((s) => s.count)));

const currency = new Intl.NumberFormat(localeTag.value, {
    style: 'currency',
    currency: 'EUR',
    maximumFractionDigits: 0,
});

const mounted = ref(false);
onMounted(() => {
    requestAnimationFrame(() => (mounted.value = true));
});

function width(count: number): string {
    if (!mounted.value) {
        return '0%';
    }

    return `${Math.max(3, (count / max.value) * 100)}%`;
}
</script>

<template>
    <div class="space-y-3.5">
        <div v-for="stage in data" :key="stage.name" class="space-y-1.5">
            <div class="flex items-center justify-between text-sm">
                <span class="flex items-center gap-2 font-medium">
                    <span
                        class="h-2.5 w-2.5 rounded-full"
                        :style="{ backgroundColor: stage.color ?? 'var(--primary)' }"
                    />
                    {{ stage.name }}
                </span>
                <span class="text-xs text-muted-foreground tabular-nums">
                    {{ stage.count }} ·
                    <span class="font-medium text-foreground">
                        {{ currency.format(stage.amount) }}
                    </span>
                </span>
            </div>
            <div class="h-2 overflow-hidden rounded-full bg-muted">
                <div
                    class="h-full rounded-full motion-safe:transition-[width] motion-safe:duration-700 motion-safe:ease-out"
                    :style="{
                        width: width(stage.count),
                        backgroundImage: `linear-gradient(90deg, color-mix(in oklch, ${stage.color ?? 'var(--primary)'} 70%, transparent), ${stage.color ?? 'var(--primary)'})`,
                    }"
                />
            </div>
        </div>
        <p v-if="data.length === 0" class="text-sm text-muted-foreground">
            {{ t('No active stage.') }}
        </p>
    </div>
</template>
