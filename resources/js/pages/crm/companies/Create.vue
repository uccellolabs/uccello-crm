<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Building2, Globe, MapPin, User as UserIcon } from '@lucide/vue';
import { computed } from 'vue';
import CompanyForm from '@/components/crm/CompanyForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { useTranslations } from '@/composables/useTranslations';
import { index } from '@/routes/companies';
import type { CustomFieldDefinition, SelectOption, Team } from '@/types';

defineProps<{
    owners: SelectOption[];
    industries: SelectOption[];
    customFields: CustomFieldDefinition[];
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const tips = computed(() => [
    {
        icon: Building2,
        label: t('A name is enough to get started'),
        text: t('You can fill in the other details at any time.'),
    },
    {
        icon: Globe,
        label: t('Enter the domain'),
        text: t('The domain helps automatically link the company\'s contacts.'),
    },
    {
        icon: MapPin,
        label: t('Full address'),
        text: t('A precise address makes geographic targeting and visits easier.'),
    },
    {
        icon: UserIcon,
        label: t('Assign a manager'),
        text: t('A clear owner prevents accounts from slipping through the cracks.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Companies'),
                          href: index(props.currentTeam.slug),
                      },
                      { title: t('New company'), href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('New company')" />

    <FormShell
        :icon="Building2"
        :title="t('New company')"
        :description="t('Add a company to your portfolio')"
    >
        <CompanyForm
            :team-slug="teamSlug"
            :owners="owners"
            :industries="industries"
            :custom-fields="customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
