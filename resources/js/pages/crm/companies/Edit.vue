<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Building2, Globe, MapPin, User as UserIcon } from '@lucide/vue';
import { computed } from 'vue';
import CompanyForm from '@/components/crm/CompanyForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { index, show } from '@/routes/companies';
import { useTranslations } from '@/composables/useTranslations';
import type {
    CompanyDetail,
    CustomFieldDefinition,
    SelectOption,
    Team,
} from '@/types';

const props = defineProps<{
    company: CompanyDetail;
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
        label: t('Keep the record up to date'),
        text: t('Fresh data helps the whole team speak with one voice.'),
    },
    {
        icon: Globe,
        label: t('Check the domain'),
        text: t('A correct domain automatically links new contacts.'),
    },
    {
        icon: MapPin,
        label: t('Full address'),
        text: t('A precise location sharpens your analysis by territory.'),
    },
    {
        icon: UserIcon,
        label: t('Up-to-date manager'),
        text: t('Reassign the account if the owner has changed.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null; company: CompanyDetail }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Companies'),
                          href: index(props.currentTeam.slug),
                      },
                      {
                          title: props.company.name,
                          href: show([
                              props.currentTeam.slug,
                              props.company.id,
                          ]),
                      },
                      { title: t('Edit'), href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head
        :title="t('Edit — :name', { name: props.company.name })"
    />

    <FormShell
        :icon="Building2"
        :title="t('Edit :name', { name: props.company.name })"
        :description="t('Update the company information')"
    >
        <CompanyForm
            :team-slug="teamSlug"
            :owners="owners"
            :industries="industries"
            :company="company"
            :custom-fields="customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
