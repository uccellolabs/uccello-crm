<script setup lang="ts">
import { CalendarClock, Sparkles } from '@lucide/vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const bookingUrl = 'https://calendar.app.google/qLg5TxaxEcJdsw9T7';
</script>

<template>
    <div
        class="bg-brand-gradient relative overflow-hidden rounded-xl p-3.5 text-white shadow-glow-primary group-data-[collapsible=icon]:hidden"
    >
        <Sparkles
            class="pointer-events-none absolute -top-3 -right-3 size-14 text-white/15"
            aria-hidden="true"
        />

        <p class="relative text-sm leading-snug font-semibold">
            {{ t('Using this CRM?') }}<br />{{ t('I can build you your own.') }}
        </p>
        <p class="relative mt-1 text-xs font-medium text-white/80">
            {{ t('Jonathan · Uccello Labs') }}
        </p>
        <p class="relative mt-2 text-xs leading-relaxed text-white/90">
            {{ t('Custom AI-powered tools, hosted in France.') }}<br>
            {{ t('Your data stays with you.') }}<br>
            {{ t('Tools designed for your business.') }}
        </p>

        <a
            :href="bookingUrl"
            target="_blank"
            rel="noopener noreferrer"
            class="relative mt-3 flex cursor-pointer items-center justify-center gap-1.5 rounded-lg bg-white px-3 py-2 text-xs font-semibold text-primary transition-transform duration-200 hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-white focus-visible:outline-none motion-reduce:transform-none"
        >
            <CalendarClock class="size-4" />
            {{ t('Book 30 min') }}
            <span aria-hidden="true">→</span>
        </a>
    </div>
</template>
