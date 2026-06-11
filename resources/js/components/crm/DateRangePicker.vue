<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { CalendarDays } from '@lucide/vue';
import { computed, onMounted, ref } from 'vue';
import { Button } from '@/components/ui/button';
import { DatePicker } from '@/components/ui/date-picker';
import { Label } from '@/components/ui/label';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';

const { t } = useTranslations();

const props = defineProps<{
    url: string;
    from: string;
    to: string;
}>();

function iso(date: Date): string {
    // Build from local components so "today" matches the user's calendar
    // (toISOString would shift across the UTC boundary).
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');

    return `${date.getFullYear()}-${month}-${day}`;
}

function buildPresets(): {
    key: string;
    label: string;
    from: string;
    to: string;
}[] {
    const now = new Date();
    const today = iso(now);
    const days = (n: number) => {
        const d = new Date();
        d.setDate(d.getDate() - n);

        return iso(d);
    };
    const startOfMonth = iso(new Date(now.getFullYear(), now.getMonth(), 1));
    const lastMonthStart = iso(
        new Date(now.getFullYear(), now.getMonth() - 1, 1),
    );
    const lastMonthEnd = iso(new Date(now.getFullYear(), now.getMonth(), 0));
    const quarterStart = iso(
        new Date(now.getFullYear(), Math.floor(now.getMonth() / 3) * 3, 1),
    );
    const yearStart = iso(new Date(now.getFullYear(), 0, 1));

    return [
        { key: 'today', label: t('Today'), from: today, to: today },
        { key: '7d', label: t('Last 7 days'), from: days(6), to: today },
        { key: '30d', label: t('Last 30 days'), from: days(29), to: today },
        { key: 'month', label: t('This month'), from: startOfMonth, to: today },
        {
            key: 'last_month',
            label: t('Last month'),
            from: lastMonthStart,
            to: lastMonthEnd,
        },
        {
            key: 'quarter',
            label: t('This quarter'),
            from: quarterStart,
            to: today,
        },
        { key: 'year', label: t('This year'), from: yearStart, to: today },
    ];
}

const presets = buildPresets();
const open = ref(false);
const customFrom = ref(props.from);
const customTo = ref(props.to);

const STORAGE_KEY = 'crm.dashboard.range';

const activePreset = computed(() =>
    presets.find((p) => p.from === props.from && p.to === props.to),
);

const dateFormatter = new Intl.DateTimeFormat('fr-FR', { dateStyle: 'medium' });

const label = computed(() => {
    if (activePreset.value) {
        return activePreset.value.label;
    }

    return `${dateFormatter.format(new Date(props.from))} – ${dateFormatter.format(new Date(props.to))}`;
});

function apply(from: string, to: string) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify({ from, to }));
    open.value = false;
    router.get(
        props.url,
        { from, to },
        {
            only: ['kpis', 'charts', 'lists', 'range'],
            preserveState: true,
            preserveScroll: true,
        },
    );
}

function applyCustom() {
    if (customFrom.value && customTo.value) {
        apply(customFrom.value, customTo.value);
    }
}

onMounted(() => {
    const stored = localStorage.getItem(STORAGE_KEY);

    if (!stored) {
        return;
    }

    try {
        const range = JSON.parse(stored) as { from: string; to: string };

        if (range.from !== props.from || range.to !== props.to) {
            apply(range.from, range.to);
        }
    } catch {
        // ignore malformed storage
    }
});
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <Button variant="outline" class="gap-2">
                <CalendarDays class="h-4 w-4" />
                {{ label }}
            </Button>
        </PopoverTrigger>
        <PopoverContent class="w-72" align="end">
            <div class="grid gap-1">
                <button
                    v-for="preset in presets"
                    :key="preset.key"
                    type="button"
                    :class="
                        cn(
                            'rounded-md px-3 py-2 text-left text-sm transition-colors hover:bg-muted',
                            activePreset?.key === preset.key &&
                                'bg-secondary font-medium',
                        )
                    "
                    @click="apply(preset.from, preset.to)"
                >
                    {{ preset.label }}
                </button>
            </div>

            <div class="mt-3 space-y-2 border-t pt-3">
                <p class="text-xs font-medium text-muted-foreground">
                    {{ t('Custom') }}
                </p>
                <div class="grid gap-2">
                    <div class="grid gap-1">
                        <Label for="range-from" class="text-xs">{{ t('From') }}</Label>
                        <DatePicker
                            id="range-from"
                            v-model="customFrom"
                            :clearable="false"
                        />
                    </div>
                    <div class="grid gap-1">
                        <Label for="range-to" class="text-xs">{{ t('To') }}</Label>
                        <DatePicker
                            id="range-to"
                            v-model="customTo"
                            :clearable="false"
                        />
                    </div>
                </div>
                <Button size="sm" class="w-full" @click="applyCustom">
                    {{ t('Apply') }}
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
