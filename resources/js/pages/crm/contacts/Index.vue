<script setup lang="ts">
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { Plus, Search, Users } from '@lucide/vue';
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
import { useTranslations } from '@/composables/useTranslations';
import { show as showCompany } from '@/routes/companies';
import { create, index, show } from '@/routes/contacts';
import type { ContactListItem, Paginated, Team } from '@/types';

type Props = {
    contacts: Paginated<ContactListItem>;
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
                ? [{ title: t('Contacts'), href: index(props.currentTeam.slug) }]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('Contacts')" />

    <div class="flex h-full flex-1 flex-col gap-5 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <Users class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Contacts')"
                    :description="t('Manage the people across your accounts')"
                />
                <Badge
                    v-if="contacts.total"
                    variant="secondary"
                    class="tabular-nums"
                >
                    {{ contacts.total }}
                </Badge>
            </div>

            <Button v-if="can.create" as-child>
                <Link :href="create(teamSlug)">
                    <Plus class="h-4 w-4" /> {{ t('New contact') }}
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
                :placeholder="t('Search for a contact…')"
                class="pl-9"
                :aria-label="t('Search for a contact')"
            />
        </div>

        <!-- Mobile: stacked cards -->
        <div class="space-y-2.5 md:hidden">
            <Link
                v-for="contact in contacts.data"
                :key="contact.id"
                :href="show([teamSlug, contact.id])"
                class="card-hover flex items-center gap-3 rounded-xl border border-border/70 bg-card p-3 shadow-card"
            >
                <InitialsAvatar :name="contact.full_name" size="lg" />
                <div class="min-w-0 flex-1">
                    <p class="truncate font-medium">{{ contact.full_name }}</p>
                    <p class="truncate text-xs text-muted-foreground">
                        {{
                            [contact.job_title, contact.company?.name]
                                .filter(Boolean)
                                .join(' · ') || '—'
                        }}
                    </p>
                </div>
            </Link>
            <EmptyState
                v-if="contacts.data.length === 0"
                :icon="Users"
                :title="t('No contacts')"
                :description="t('Add your contacts to track your exchanges.')"
            >
                <Button v-if="can.create" as-child>
                    <Link :href="create(teamSlug)">
                        <Plus class="h-4 w-4" /> {{ t('New contact') }}
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
                        <TableHead>{{ t('Job title') }}</TableHead>
                        <TableHead>{{ t('Company') }}</TableHead>
                        <TableHead>{{ t('Email') }}</TableHead>
                        <TableHead>{{ t('Phone') }}</TableHead>
                    </TableRow>
                </TableHeader>
                <TableBody>
                    <TableRow
                        v-for="contact in contacts.data"
                        :key="contact.id"
                        class="group cursor-pointer"
                        @click="router.visit(show([teamSlug, contact.id]).url)"
                    >
                        <TableCell>
                            <Link
                                :href="show([teamSlug, contact.id])"
                                class="flex items-center gap-3"
                                @click.stop
                            >
                                <InitialsAvatar :name="contact.full_name" />
                                <span
                                    class="font-medium group-hover:text-primary"
                                >
                                    {{ contact.full_name }}
                                </span>
                            </Link>
                        </TableCell>
                        <TableCell>{{ contact.job_title ?? '—' }}</TableCell>
                        <TableCell>
                            <Link
                                v-if="contact.company"
                                :href="
                                    showCompany([teamSlug, contact.company.id])
                                "
                                class="transition-colors hover:text-primary hover:underline"
                                @click.stop
                            >
                                {{ contact.company.name }}
                            </Link>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                        <TableCell>
                            <a
                                v-if="contact.email"
                                :href="`mailto:${contact.email}`"
                                class="transition-colors hover:text-primary hover:underline"
                                @click.stop
                            >
                                {{ contact.email }}
                            </a>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                        <TableCell class="tabular-nums">
                            <a
                                v-if="contact.phone"
                                :href="`tel:${contact.phone}`"
                                class="transition-colors hover:text-primary hover:underline"
                                @click.stop
                            >
                                {{ contact.phone }}
                            </a>
                            <span v-else class="text-muted-foreground">—</span>
                        </TableCell>
                    </TableRow>

                    <TableRow
                        v-if="contacts.data.length === 0"
                        class="hover:bg-transparent"
                    >
                        <TableCell :colspan="5" class="p-0">
                            <EmptyState
                                :icon="Users"
                                :title="t('No contacts')"
                                :description="
                                    t('Add your contacts to track your exchanges.')
                                "
                            >
                                <Button v-if="can.create" as-child>
                                    <Link :href="create(teamSlug)">
                                        <Plus class="h-4 w-4" /> {{ t('New contact') }}
                                    </Link>
                                </Button>
                            </EmptyState>
                        </TableCell>
                    </TableRow>
                </TableBody>
            </Table>
        </div>

        <Pagination
            :links="contacts.links"
            :from="contacts.from"
            :to="contacts.to"
            :total="contacts.total"
        />
    </div>
</template>
