<script setup lang="ts">
import { router } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { update as updateLocale } from '@/routes/locale';

const { locale } = useTranslations();

const options: { value: 'fr' | 'en'; label: string }[] = [
    { value: 'fr', label: 'FR' },
    { value: 'en', label: 'EN' },
];

function select(value: 'fr' | 'en'): void {
    if (value === locale.value) {
        return;
    }

    router.put(updateLocale.url(), { locale: value }, { preserveScroll: true });
}
</script>

<template>
    <div
        class="inline-flex items-center gap-0.5 rounded-lg border border-border bg-card p-0.5"
        role="group"
        aria-label="Langue / Language"
    >
        <button
            v-for="option in options"
            :key="option.value"
            type="button"
            class="cursor-pointer rounded-md px-2.5 py-1 text-xs font-semibold transition-colors duration-200 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
            :class="
                option.value === locale
                    ? 'bg-primary text-primary-foreground'
                    : 'text-muted-foreground hover:text-foreground'
            "
            :aria-pressed="option.value === locale"
            @click="select(option.value)"
        >
            {{ option.label }}
        </button>
    </div>
</template>
