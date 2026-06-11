<script setup lang="ts">
import { Head, usePage } from '@inertiajs/vue3';
import { Building2, Mail, Phone, User as UserIcon } from '@lucide/vue';
import { computed } from 'vue';
import ContactForm from '@/components/crm/ContactForm.vue';
import FormShell from '@/components/crm/FormShell.vue';
import FormTips from '@/components/crm/FormTips.vue';
import { useTranslations } from '@/composables/useTranslations';
import { index, show } from '@/routes/contacts';
import type {
    ContactDetail,
    CustomFieldDefinition,
    SelectOption,
    Team,
} from '@/types';

const props = defineProps<{
    contact: ContactDetail;
    owners: SelectOption[];
    companies: SelectOption[];
    customFields: CustomFieldDefinition[];
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const tips = computed(() => [
    {
        icon: Building2,
        label: t('Company up to date'),
        text: t('Check the link if the contact has changed employer.'),
    },
    {
        icon: Mail,
        label: t('Valid contact details'),
        text: t('Up-to-date emails prevent lost reminders.'),
    },
    {
        icon: Phone,
        label: t('Direct phone'),
        text: t('Prefer a direct line to save time.'),
    },
    {
        icon: UserIcon,
        label: t('Owner'),
        text: t('Confirm who manages the relationship with this contact.'),
    },
]);

defineOptions({
    layout: (props: { currentTeam?: Team | null; contact: ContactDetail }) => {
        const { t } = useTranslations();

        return {
            breadcrumbs: props.currentTeam
                ? [
                      {
                          title: t('Contacts'),
                          href: index(props.currentTeam.slug),
                      },
                      {
                          title: props.contact.full_name,
                          href: show([
                              props.currentTeam.slug,
                              props.contact.id,
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
        :title="t('Edit — :name', { name: props.contact.full_name })"
    />

    <FormShell
        :icon="UserIcon"
        :title="t('Edit :name', { name: props.contact.full_name })"
        :description="t('Update the contact information')"
    >
        <ContactForm
            :team-slug="teamSlug"
            :owners="owners"
            :companies="companies"
            :contact="contact"
            :custom-fields="customFields"
        />

        <template #aside>
            <FormTips :items="tips" />
        </template>
    </FormShell>
</template>
