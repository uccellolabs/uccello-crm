<script setup lang="ts">
type Props = {
    title: string;
    description?: string;
    variant?: 'default' | 'small';
};

withDefaults(defineProps<Props>(), {
    variant: 'default',
});
</script>

<template>
    <header :class="variant === 'small' ? 'space-y-1' : 'mb-8 space-y-1'">
        <h2
            :class="
                variant === 'small'
                    ? 'text-lg font-semibold tracking-tight'
                    : 'text-2xl font-bold tracking-tight'
            "
        >
            {{ title }}
        </h2>
        <p
            v-if="description"
            class="text-sm text-muted-foreground"
            :class="variant === 'small' ? '' : 'text-base'"
        >
            {{ description }}
        </p>
    </header>
</template>
