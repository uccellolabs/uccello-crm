<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { CalendarClock, Layers, Target, Wallet } from '@lucide/vue';
import { computed } from 'vue';
import DealForm from '@/components/crm/DealForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { useTranslations } from '@/composables/useTranslations';
import { board } from '@/routes/deals';
import type {
    CustomFieldDefinition,
    PipelineWithStages,
    SelectOption,
    Team,
} from '@/types';

const props = defineProps<{
    pipelines: PipelineWithStages[];
    companies: SelectOption[];
    contacts: SelectOption[];
    owners: SelectOption[];
    stageId: number | null;
    companyId: number | null;
    contactId: number | null;
    customFields: CustomFieldDefinition[];
}>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const { t } = useTranslations();

const tips = computed(() => [
    {
        icon: Wallet,
        label: t('Estimate the deal'),
        text: t('A filled-in amount feeds the value of your pipeline.'),
    },
    {
        icon: Layers,
        label: t('Pick the stage'),
        text: t('Place the opportunity at the stage that reflects its real maturity.'),
    },
    {
        icon: CalendarClock,
        label: t('Close date'),
        text: t('A credible deadline improves the reliability of your forecasts.'),
    },
    {
        icon: Target,
        label: t('Link company & contact'),
        text: t('A well-linked deal keeps the whole history in one place.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  { title: t('Pipeline'), href: board(props.currentTeam.slug) },
                  { title: t('New opportunity'), href: '#' },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('New opportunity')" />

    <FormShell
        :icon="Target"
        :title="t('New opportunity')"
        :description="t('Add a deal to your pipeline')"
    >
        <DealForm
            :team-slug="teamSlug"
            :pipelines="props.pipelines"
            :companies="props.companies"
            :contacts="props.contacts"
            :owners="props.owners"
            :stage-id="props.stageId"
            :company-id="props.companyId"
            :contact-id="props.contactId"
            :custom-fields="props.customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
