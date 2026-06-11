<script setup lang="ts">
import AppLogoIcon from '@/components/AppLogoIcon.vue';
</script>

<template>
    <div
        class="flex aspect-square size-9 items-center justify-center rounded-xl bg-brand-gradient text-white shadow-glow-violet"
    >
        <AppLogoIcon class="size-5 text-white" />
    </div>
    <div class="ml-1 grid flex-1 text-left">
        <span class="truncate text-sm leading-tight font-semibold tracking-tight">
            Uccello <span class="text-gradient">CRM</span>
        </span>
        <span class="truncate text-[11px] leading-tight text-muted-foreground">
            Sales workspace
        </span>
    </div>
</template>
