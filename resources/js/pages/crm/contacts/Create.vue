<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Building2, Mail, Phone, UserPlus } from '@lucide/vue';
import { computed } from 'vue';
import ContactForm from '@/components/crm/ContactForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { useTranslations } from '@/composables/useTranslations';
import { index } from '@/routes/contacts';
import type { CustomFieldDefinition, SelectOption, Team } from '@/types';

const props = defineProps<{
    owners: SelectOption[];
    companies: SelectOption[];
    companyId: number | null;
    customFields: CustomFieldDefinition[];
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const tips = computed(() => [
    {
        icon: Building2,
        label: t('Attach a company'),
        text: t('A contact linked to a company automatically feeds its record.'),
    },
    {
        icon: Mail,
        label: t('Business email'),
        text: t('The email is the key for follow-up and sales reminders.'),
    },
    {
        icon: Phone,
        label: t('Direct number'),
        text: t('A reachable phone number speeds up getting in touch.'),
    },
    {
        icon: UserPlus,
        label: t('Contact job title'),
        text: t('Knowing the role helps tailor your pitch to the right decision-maker.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Contacts'),
                          href: index(props.currentTeam.slug),
                      },
                      { title: t('New'), href: '#' },
                  ]
                : [],
        };
    },
});
</script>

<template>
    <Head :title="t('New contact')" />

    <FormShell
        :icon="UserPlus"
        :title="t('New contact')"
        :description="t('Add a contact')"
    >
        <ContactForm
            :team-slug="teamSlug"
            :owners="owners"
            :companies="companies"
            :default-company-id="props.companyId"
            :custom-fields="customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
