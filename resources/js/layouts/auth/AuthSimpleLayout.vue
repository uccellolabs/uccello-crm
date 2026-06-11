<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import AppLogoIcon from '@/components/AppLogoIcon.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { Card, CardContent } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';
import { home } from '@/routes';

const { t } = useTranslations();

defineProps<{
    title?: string;
    description?: string;
}>();

const year = new Date().getFullYear();
</script>

<template>
    <div
        class="bg-aurora relative flex min-h-svh flex-col items-center justify-center gap-7 p-6 md:p-10"
    >
        <LanguageSwitcher class="absolute top-6 right-6 z-10" />

        <Link :href="home()" class="flex flex-col items-center gap-3">
            <span
                class="flex h-12 w-12 items-center justify-center rounded-2xl bg-brand-gradient text-white shadow-glow-violet"
            >
                <AppLogoIcon class="size-6 text-white" />
            </span>
            <span class="text-lg font-semibold tracking-tight">
                Uccello <span class="text-gradient">CRM</span>
            </span>
        </Link>

        <Card
            class="w-full max-w-md rounded-2xl border-border/60 bg-card/90 shadow-glow-violet backdrop-blur-sm"
        >
            <CardContent class="px-8 py-8 sm:px-10">
                <div class="mb-6 space-y-1.5 text-center">
                    <h1 class="text-xl font-bold tracking-tight">
                        {{ title ? t(title) : '' }}
                    </h1>
                    <p class="text-sm text-muted-foreground">
                        {{ description ? t(description) : '' }}
                    </p>
                </div>
                <slot />
            </CardContent>
        </Card>

        <p class="text-xs text-muted-foreground">
            © {{ year }} Uccello CRM · Sales workspace
        </p>
    </div>
</template>
