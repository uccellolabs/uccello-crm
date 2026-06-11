<script setup lang="ts">
import type { Component } from 'vue';

defineProps<{
    icon?: Component;
    title: string;
    description?: string;
}>();
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 px-6 py-14 text-center">
        <span
            v-if="icon"
            class="flex h-14 w-14 items-center justify-center rounded-2xl bg-secondary text-muted-foreground"
        >
            <component :is="icon" class="h-6 w-6" />
        </span>
        <div class="space-y-1">
            <p class="font-semibold">{{ title }}</p>
            <p
                v-if="description"
                class="mx-auto max-w-sm text-sm text-muted-foreground"
            >
                {{ description }}
            </p>
        </div>
        <div v-if="$slots.default" class="mt-1">
            <slot />
        </div>
    </div>
</template>
