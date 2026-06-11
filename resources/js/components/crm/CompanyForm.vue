<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import CustomFieldsForm from '@/components/crm/CustomFieldsForm.vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/composables/useTranslations';
import { index, show, store, update } from '@/routes/companies';
import type {
    CompanyDetail,
    CustomFieldDefinition,
    CustomFieldValues,
    SelectOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        teamSlug: string;
        owners: SelectOption[];
        industries?: SelectOption[];
        company?: CompanyDetail | null;
        customFields?: CustomFieldDefinition[];
    }>(),
    { customFields: () => [], industries: () => [] },
);

const { t } = useTranslations();

const isEdit = computed(() => props.company != null);

const form = useForm({
    name: props.company?.name ?? '',
    domain: props.company?.domain ?? '',
    industry: props.company?.industry ?? '',
    phone: props.company?.phone ?? '',
    website: props.company?.website ?? '',
    address: props.company?.address ?? '',
    city: props.company?.city ?? '',
    postal_code: props.company?.postal_code ?? '',
    country: props.company?.country ?? '',
    owner_id: (props.company?.owner_id ?? null) as number | null,
    custom_fields: {
        ...(props.company?.custom_fields ?? {}),
    } as CustomFieldValues,
});

// The admin manages the industry list; a legacy value not in it anymore is
// kept selectable so editing an old record doesn't silently drop it.
const INDUSTRY_NONE = 'none';

const industryOptions = computed<SelectOption[]>(() => {
    const current = props.company?.industry;

    if (current && !props.industries.some((o) => o.value === current)) {
        return [{ value: current, label: current }, ...props.industries];
    }

    return props.industries;
});

const industryProxy = computed<string>({
    get: () => (form.industry === '' ? INDUSTRY_NONE : form.industry),
    set: (value) => {
        form.industry = value === INDUSTRY_NONE ? '' : value;
    },
});

const cancelHref = computed(() =>
    props.company
        ? show([props.teamSlug, props.company.id])
        : index(props.teamSlug),
);

function submit() {
    if (isEdit.value && props.company) {
        form.put(update([props.teamSlug, props.company.id]).url, {
            preserveScroll: true,
        });
    } else {
        form.post(store(props.teamSlug).url, { preserveScroll: true });
    }
}
</script>

<template>
    <form class="space-y-6" @submit.prevent="submit">
        <div class="grid gap-2">
            <Label for="name"
                >{{ t('Name') }} <span class="text-destructive">*</span></Label
            >
            <Input
                id="name"
                v-model="form.name"
                required
                autocomplete="organization"
                placeholder="Acme SAS"
            />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="industry">{{ t('Industry') }}</Label>
                <Select v-model="industryProxy">
                    <SelectTrigger id="industry" class="w-full">
                        <SelectValue :placeholder="t('No industry')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem :value="INDUSTRY_NONE">
                            {{ t('No industry') }}
                        </SelectItem>
                        <SelectItem
                            v-for="option in industryOptions"
                            :key="option.value"
                            :value="String(option.value)"
                        >
                            {{ option.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.industry" />
            </div>
            <div class="grid gap-2">
                <Label for="domain">{{ t('Domain') }}</Label>
                <Input
                    id="domain"
                    v-model="form.domain"
                    placeholder="acme.com"
                />
                <InputError :message="form.errors.domain" />
            </div>
            <div class="grid gap-2">
                <Label for="website">{{ t('Website') }}</Label>
                <Input
                    id="website"
                    v-model="form.website"
                    type="url"
                    placeholder="https://acme.com"
                />
                <InputError :message="form.errors.website" />
            </div>
            <div class="grid gap-2">
                <Label for="phone">{{ t('Phone') }}</Label>
                <Input
                    id="phone"
                    v-model="form.phone"
                    placeholder="+33 1 23 45 67 89"
                />
                <InputError :message="form.errors.phone" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="address">{{ t('Address') }}</Label>
            <Input
                id="address"
                v-model="form.address"
                placeholder="12 rue de la Paix"
            />
            <InputError :message="form.errors.address" />
        </div>

        <div class="grid gap-4 sm:grid-cols-3">
            <div class="grid gap-2">
                <Label for="postal_code">{{ t('Postal code') }}</Label>
                <Input
                    id="postal_code"
                    v-model="form.postal_code"
                    placeholder="75002"
                />
                <InputError :message="form.errors.postal_code" />
            </div>
            <div class="grid gap-2">
                <Label for="city">{{ t('City') }}</Label>
                <Input id="city" v-model="form.city" placeholder="Paris" />
                <InputError :message="form.errors.city" />
            </div>
            <div class="grid gap-2">
                <Label for="country">{{ t('Country') }}</Label>
                <Input
                    id="country"
                    v-model="form.country"
                    placeholder="France"
                />
                <InputError :message="form.errors.country" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="owner">{{ t('Manager') }}</Label>
            <NullableSelect
                id="owner"
                v-model="form.owner_id"
                :placeholder="t('No manager')"
                :empty-label="t('No manager')"
                :options="owners"
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
                {{ isEdit ? t('Save') : t('Create the company') }}
            </Button>
            <Button variant="ghost" as-child>
                <Link :href="cancelHref">{{ t('Cancel') }}</Link>
            </Button>
        </div>
    </form>
</template>
