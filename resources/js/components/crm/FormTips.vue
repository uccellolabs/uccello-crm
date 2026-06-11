<script setup lang="ts">
import { Lightbulb } from '@lucide/vue';
import type { Component } from 'vue';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

defineProps<{
    title?: string;
    items: { icon: Component; label: string; text: string }[];
}>();
</script>

<template>
    <Card class="surface-glass border-primary/10">
        <CardHeader class="flex flex-row items-center gap-2 space-y-0">
            <span
                class="flex h-7 w-7 items-center justify-center rounded-lg bg-brand-gradient text-white"
            >
                <Lightbulb class="h-4 w-4" />
            </span>
            <CardTitle class="text-base">{{ title ?? t('Best practices') }}</CardTitle>
        </CardHeader>
        <CardContent class="space-y-4">
            <div
                v-for="item in items"
                :key="item.label"
                class="flex items-start gap-3"
            >
                <component
                    :is="item.icon"
                    class="mt-0.5 h-4 w-4 shrink-0 text-primary"
                />
                <div class="min-w-0 space-y-0.5">
                    <p class="text-sm font-medium leading-tight">{{ item.label }}</p>
                    <p class="text-xs leading-relaxed text-muted-foreground">
                        {{ item.text }}
                    </p>
                </div>
            </div>
        </CardContent>
    </Card>
</template>
