<script setup lang="ts">
import { Link } from '@inertiajs/vue3';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import type { PaginationLink } from '@/types';

const { t } = useTranslations();

defineProps<{
    links: PaginationLink[];
    from: number | null;
    to: number | null;
    total: number;
}>();

// Laravel encodes the prev/next labels with HTML entities; decode the known
// ones to plain text so we can render without v-html.
function cleanLabel(label: string): string {
    return label
        .replace(/&laquo;\s*/g, '« ')
        .replace(/\s*&raquo;/g, ' »')
        .replace(/&hellip;/g, '…');
}
</script>

<template>
    <nav
        v-if="total > 0"
        class="flex flex-col items-center justify-between gap-3 sm:flex-row"
        :aria-label="t('Pagination')"
    >
        <p class="text-sm text-muted-foreground">
            {{ t(':from–:to of :total', { from: from ?? 0, to: to ?? 0, total }) }}
        </p>
        <div class="flex flex-wrap items-center gap-1">
            <template v-for="(link, index) in links" :key="index">
                <span
                    v-if="link.url === null"
                    class="inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm text-muted-foreground/50"
                    >{{ cleanLabel(link.label) }}</span
                >
                <Link
                    v-else
                    :href="link.url"
                    preserve-scroll
                    :class="
                        cn(
                            'inline-flex h-9 min-w-9 items-center justify-center rounded-md px-3 text-sm transition-colors',
                            link.active
                                ? 'bg-primary text-primary-foreground'
                                : 'hover:bg-muted',
                        )
                    "
                    >{{ cleanLabel(link.label) }}</Link
                >
            </template>
        </div>
    </nav>
</template>
