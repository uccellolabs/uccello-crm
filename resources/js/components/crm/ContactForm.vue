<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CustomFieldsForm from '@/components/crm/CustomFieldsForm.vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { useTranslations } from '@/composables/useTranslations';
import { index, show, store, update } from '@/routes/contacts';
import type {
    ContactDetail,
    CustomFieldDefinition,
    CustomFieldValues,
    SelectOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        teamSlug: string;
        owners: SelectOption[];
        companies: SelectOption[];
        contact?: ContactDetail | null;
        defaultCompanyId?: number | null;
        customFields?: CustomFieldDefinition[];
    }>(),
    { customFields: () => [] },
);

const { t } = useTranslations();

const isEdit = computed(() => props.contact != null);

const form = useForm({
    first_name: props.contact?.first_name ?? '',
    last_name: props.contact?.last_name ?? '',
    email: props.contact?.email ?? '',
    phone: props.contact?.phone ?? '',
    job_title: props.contact?.job_title ?? '',
    company_id: (props.contact?.company_id ?? props.defaultCompanyId ?? null) as
        | number
        | null,
    owner_id: (props.contact?.owner_id ?? null) as number | null,
    custom_fields: { ...(props.contact?.custom_fields ?? {}) } as CustomFieldValues,
});

const cancelHref = computed(() =>
    props.contact
        ? show([props.teamSlug, props.contact.id])
        : index(props.teamSlug),
);

function submit() {
    if (isEdit.value && props.contact) {
        form.put(update([props.teamSlug, props.contact.id]).url, {
            preserveScroll: true,
        });
    } else {
        form.post(store(props.teamSlug).url, { preserveScroll: true });
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="first_name">
                    {{ t('First name') }}
                    <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="first_name"
                    v-model="form.first_name"
                    required
                    autocomplete="given-name"
                />
                <InputError :message="form.errors.first_name" />
            </div>
            <div class="grid gap-2">
                <Label for="last_name">
                    {{ t('Name') }} <span class="text-destructive">*</span>
                </Label>
                <Input
                    id="last_name"
                    v-model="form.last_name"
                    required
                    autocomplete="family-name"
                />
                <InputError :message="form.errors.last_name" />
            </div>
            <div class="grid gap-2">
                <Label for="email">{{ t('Email') }}</Label>
                <Input
                    id="email"
                    v-model="form.email"
                    type="email"
                    autocomplete="email"
                    placeholder="jean.dupont@acme.com"
                />
                <InputError :message="form.errors.email" />
            </div>
            <div class="grid gap-2">
                <Label for="phone">{{ t('Phone') }}</Label>
                <Input id="phone" v-model="form.phone" placeholder="+33 6 12 34 56 78" />
                <InputError :message="form.errors.phone" />
            </div>
            <div class="grid gap-2">
                <Label for="job_title">{{ t('Job title') }}</Label>
                <Input
                    id="job_title"
                    v-model="form.job_title"
                    :placeholder="t('Sales Director')"
                />
                <InputError :message="form.errors.job_title" />
            </div>
            <div class="grid gap-2">
                <Label for="company">{{ t('Company') }}</Label>
                <NullableSelect
                    id="company"
                    v-model="form.company_id"
                    :options="companies"
                    :placeholder="t('No company')"
                    :empty-label="t('No company')"
                />
                <InputError :message="form.errors.company_id" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="owner">{{ t('Owner') }}</Label>
            <NullableSelect
                id="owner"
                v-model="form.owner_id"
                :options="owners"
                :placeholder="t('No owner')"
                :empty-label="t('No owner')"
            />
            <InputError :message="form.errors.owner_id" />
        </div>

        <div v-if="customFields.length" class="space-y-4 border-t pt-6">
            <h3 class="text-sm font-medium text-muted-foreground">
                {{ t('Custom fields') }}
            </h3>
            <CustomFieldsForm
                v-model="form.custom_fields"
                :definitions="customFields"
                :errors="form.errors"
            />
        </div>

        <div class="flex items-center gap-3">
            <Button type="submit" :disabled="form.processing">
                {{ isEdit ? t('Save') : t('Create contact') }}
            </Button>
            <Button variant="ghost" as-child>
                <Link :href="cancelHref">{{ t('Cancel') }}</Link>
            </Button>
        </div>
    </form>
</template>
