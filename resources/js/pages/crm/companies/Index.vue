<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Building2, Plus, Search } from '@lucide/vue';
import { watchDebounced } from '@vueuse/core';
import { computed, ref } from 'vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import InitialsAvatar from '@/components/crm/InitialsAvatar.vue';
import Pagination from '@/components/crm/Pagination.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/components/ui/table';
import { create, index, show } from '@/routes/companies';
import { useTranslations } from '@/composables/useTranslations';
import type { CompanyListItem, Paginated, Team } from '@/types';

type Props = {
    companies: Paginated<CompanyListItem>;
    filters: { search: string };
    can: { create: boolean };
};

const props = defineProps<Props>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const search = ref(props.filters.search);

watchDebounced(
    search,
    (value) => {
        router.get(
            index(teamSlug.value).url,
            { search: value || undefined },
            { preserveState: true, replace: true, preserveScroll: true },
        );
    },
    { debounce: 300 },
);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [{ title: t('Companies'), href: index(props.currentTeam.slug) }]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('Companies')" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <Building2 class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Companies')"
                    :description="t('Manage the companies in your portfolio')"
                />
                <Badge
                    v-if="companies.total"
                    variant="secondary"
                    class="tabular-nums"
                >
                    {{ companies.total }}
                </Badge>
            </div>

            <Button v-if="can.create" as-child>
                <Link :href="create(teamSlug)">
                    <Plus class="h-4 w-4" /> {{ t('New company') }}
                </Link>
            </Button>
        </div>

        <div class="relative max-w-sm">
            <Search
                class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-muted-foreground"
            />
            <Input
                v-model="search"
                type="search"
                :placeholder="t('Search for a company…')"
                class="pl-9"
                :aria-label="t('Search for a company')"
            />
        </div>

        <!-- Mobile: stacked cards -->
        <div class="space-y-2.5 md:hidden">
            <Link
                v-for="company in companies.data"
                :key="company.id"
                :href="show([teamSlug, company.id])"
                class="card-hover flex items-center gap-3 rounded-xl border border-border/70 bg-card p-3 shadow-card"
            >
                <InitialsAvatar :name="company.name" size="lg" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ company.name }}</p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{
                            [company.industry, company.city]
                                .filter(Boolean)
                                .join(' · ') || '—'
                        }}
                    </p>
                </div>
                <InitialsAvatar
                    v-if="company.owner"
                    :name="company.owner.name"
                    size="sm"
                />
            </Link>
            <EmptyState
                v-if="companies.data.length === 0"
                :icon="Building2"
                :title="t('No companies')"
                :description="t('Add your first company to get started.')"
            >
                <Button v-if="can.create" as-child>
                    <Link :href="create(teamSlug)">
                        <Plus class="h-4 w-4" /> {{ t('New company') }}
                    </Link>
                </Button>
            </EmptyState>
        </div>

        <!-- Desktop: table -->
        <div
            class="hidden overflow-hidden rounded-xl border border-border/70 bg-card shadow-card md:block"
        >
            <Table>
                <TableHeader>
                    <TableRow class="hover:bg-transparent">
                        <TableHead>{{ t('Name') }}</TableHead>
                        <TableHead>{{ t('Industry') }}</TableHead>
                        <TableHead>{{ t('City') }}</TableHead>
                        <TableHead>{{ t('Phone') }}</TableHead>
                        <TableHead>{{ t('Manager') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="company in companies.data"
                        :key="company.id"
                        class="group cursor-pointer"
                        @click="router.visit(show([teamSlug, company.id]).url)"
                    >
                        <TableCell>
                            <Link
                                :href="show([teamSlug, company.id])"
                                class="flex items-center gap-3"
                                @click.stop
                            >
                                <InitialsAvatar :name="company.name" />
                                <span
                                    class="font-medium group-hover:text-primary"
                                >
                                    {{ company.name }}
                                </span>
                            </Link>
                        </TableCell>
                        <TableCell>
                            <Badge v-if="company.industry" variant="secondary">
                                {{ company.industry }}
                            </Badge>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                        <TableCell>{{ company.city ?? '—' }}</TableCell>
                        <TableCell class="tabular-nums">
                            <a
                                v-if="company.phone"
                                :href="`tel:${company.phone}`"
                                class="transition-colors hover:text-primary hover:underline"
                                @click.stop
                            >
                                {{ company.phone }}
                            </a>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                        <TableCell>
                            <span
                                v-if="company.owner"
                                class="flex items-center gap-2"
                            >
                                <InitialsAvatar
                                    :name="company.owner.name"
                                    size="sm"
                                />
                                <span class="text-sm">{{
                                    company.owner.name
                                }}</span>
                            </span>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                    </TableRow>

                    <TableRow
                        v-if="companies.data.length === 0"
                        class="hover:bg-transparent"
                    >
                        <TableCell :colspan="5" class="p-0">
                            <EmptyState
                                :icon="Building2"
                                :title="t('No companies')"
                                :description="t('Add your first company to start building your portfolio.')"
                            >
                                <Button v-if="can.create" as-child>
                                    <Link :href="create(teamSlug)">
                                        <Plus class="h-4 w-4" />
                                        {{ t('New company') }}
                                    </Link>
                                </Button>
                            </EmptyState>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination
            :links="companies.links"
            :from="companies.from"
            :to="companies.to"
            :total="companies.total"
        />
    </div>
</template>
