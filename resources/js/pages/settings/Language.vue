<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import Heading from '@/components/Heading.vue';
import LanguageSwitcher from '@/components/LanguageSwitcher.vue';
import { useTranslations } from '@/composables/useTranslations';
import { edit } from '@/routes/language';

const { t } = useTranslations();

defineOptions({
    layout: {
        breadcrumbs: [
            {
                title: 'Language settings',
                href: edit(),
            },
        ],
    },
});
</script>

<template>
    <Head :title="t('Language settings')" />

    <h1 class="sr-only">{{ t('Language settings') }}</h1>

    <div class="space-y-6">
        <Heading
            variant="small"
            :title="t('Language settings')"
            :description="t('Choose the language used across the app.')"
        />

        <div class="space-y-3">
            <LanguageSwitcher />
            <p class="text-sm text-muted-foreground">
                {{ t('Your choice is saved to your profile.') }}
            </p>
        </div>
    </div>
</template>
