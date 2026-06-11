<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarClock, Layers, Target, Wallet } from '@lucide/vue';
import { computed } from 'vue';
import DealForm from '@/components/crm/DealForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { useTranslations } from '@/composables/useTranslations';
import { board, show } from '@/routes/deals';
import type {
    CustomFieldDefinition,
    DealDetail,
    PipelineWithStages,
    SelectOption,
    Team,
} from '@/types';

const props = defineProps<{
    deal: DealDetail;
    pipelines: PipelineWithStages[];
    companies: SelectOption[];
    contacts: SelectOption[];
    owners: SelectOption[];
    customFields: CustomFieldDefinition[];
}>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const { t } = useTranslations();

const tips = computed(() => [
    {
        icon: Layers,
        label: t('Advance the stage'),
        text: t('Moving the deal to the won/lost stage automatically closes its status.'),
    },
    {
        icon: Wallet,
        label: t('Up-to-date amount'),
        text: t('Readjust the amount if the scope of the deal has changed.'),
    },
    {
        icon: CalendarClock,
        label: t('Realistic deadline'),
        text: t('Push back or move up the date based on the latest exchanges.'),
    },
    {
        icon: Target,
        label: t('Company & contact'),
        text: t('Keep the links correct for a reliable history.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null; deal: DealDetail }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  { title: t('Pipeline'), href: board(props.currentTeam.slug) },
                  {
                      title: props.deal.name,
                      href: show([props.currentTeam.slug, props.deal.id]),
                  },
                  { title: t('Edit'), href: '#' },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('Edit — :name', { name: props.deal.name })" />

    <FormShell
        :icon="Target"
        :title="t('Edit :name', { name: props.deal.name })"
        :description="t('Update the opportunity')"
    >
        <DealForm
            :team-slug="teamSlug"
            :deal="props.deal"
            :pipelines="props.pipelines"
            :companies="props.companies"
            :contacts="props.contacts"
            :owners="props.owners"
            :custom-fields="props.customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
