<script setup lang="ts">
import type { Component } from 'vue';
import { Card, CardContent } from '@/components/ui/card';

defineProps<{
    title: string;
    description: string;
    icon: Component;
}>();
</script>

<template>
    <div class="mx-auto flex w-full max-w-5xl flex-col gap-6 p-4">
        <div
            class="relative overflow-hidden rounded-2xl border bg-card shadow-card"
        >
            <div class="bg-mesh absolute inset-0" aria-hidden="true" />
            <div class="relative flex items-start gap-4 p-5 sm:p-6">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-14 w-14 shrink-0 items-center justify-center rounded-2xl text-white"
                >
                    <component :is="icon" class="h-7 w-7" />
                </span>
                <div class="min-w-0 space-y-1">
                    <h1 class="text-2xl font-bold tracking-tight">
                        {{ title }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ description }}
                    </p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            <Card class="lg:col-span-2">
                <CardContent>
                    <slot />
                </CardContent>
            </Card>

            <aside class="lg:col-span-1">
                <div class="lg:sticky lg:top-4">
                    <slot name="aside" />
                </div>
            </aside>
        </div>
    </div>
</template>
