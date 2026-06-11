<script setup lang="ts">
import { Link, useForm } from '@inertiajs/vue3';
import { computed, watch } from 'vue';
import CustomFieldsForm from '@/components/crm/CustomFieldsForm.vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { useTranslations } from '@/composables/useTranslations';
import { DatePicker } from '@/components/ui/date-picker';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { board, show, store, update } from '@/routes/deals';
import type {
    CustomFieldDefinition,
    CustomFieldValues,
    DealDetail,
    PipelineWithStages,
    SelectOption,
} from '@/types';

const props = withDefaults(
    defineProps<{
        teamSlug: string;
        pipelines: PipelineWithStages[];
        companies: SelectOption[];
        contacts: SelectOption[];
        owners: SelectOption[];
        deal?: DealDetail | null;
        stageId?: number | null;
        companyId?: number | null;
        contactId?: number | null;
        customFields?: CustomFieldDefinition[];
    }>(),
    { customFields: () => [] },
);

const { t } = useTranslations();

const isEdit = computed(() => props.deal != null);

const firstPipeline = props.pipelines[0];
const initialPipeline = props.deal?.pipeline_id ?? firstPipeline?.id ?? null;
const initialStage =
    props.deal?.pipeline_stage_id ??
    props.stageId ??
    firstPipeline?.stages[0]?.value ??
    null;

const form = useForm({
    name: props.deal?.name ?? '',
    amount: props.deal?.amount != null ? String(props.deal.amount) : '',
    currency: props.deal?.currency ?? 'EUR',
    pipeline_id: initialPipeline as number | null,
    pipeline_stage_id: (initialStage ?? null) as number | null,
    company_id: (props.deal?.company_id ?? props.companyId ?? null) as
        | number
        | null,
    contact_id: (props.deal?.contact_id ?? props.contactId ?? null) as
        | number
        | null,
    expected_close_date: props.deal?.expected_close_date ?? '',
    owner_id: (props.deal?.owner_id ?? null) as number | null,
    custom_fields: {
        ...(props.deal?.custom_fields ?? {}),
    } as CustomFieldValues,
});

const stageOptions = computed<SelectOption[]>(
    () => props.pipelines.find((p) => p.id === form.pipeline_id)?.stages ?? [],
);

// When the pipeline changes, snap the stage to the first of the new pipeline.
watch(
    () => form.pipeline_id,
    () => {
        const stages = stageOptions.value;

        if (!stages.some((s) => s.value === form.pipeline_stage_id)) {
            form.pipeline_stage_id = (stages[0]?.value as number) ?? null;
        }
    },
);

const cancelHref = computed(() =>
    props.deal ? show([props.teamSlug, props.deal.id]) : board(props.teamSlug),
);

function submit() {
    if (isEdit.value && props.deal) {
        form.put(update([props.teamSlug, props.deal.id]).url, {
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
                :placeholder="t('Annual license')"
            />
            <InputError :message="form.errors.name" />
        </div>

        <div class="grid gap-4 sm:grid-cols-2">
            <div class="grid gap-2">
                <Label for="amount">{{ t('Amount (€)') }}</Label>
                <Input
                    id="amount"
                    v-model="form.amount"
                    type="number"
                    min="0"
                    step="100"
                    placeholder="15000"
                />
                <InputError :message="form.errors.amount" />
            </div>
            <div class="grid gap-2">
                <Label for="expected_close_date">{{
                    t('Expected close date')
                }}</Label>
                <DatePicker
                    id="expected_close_date"
                    v-model="form.expected_close_date"
                    :placeholder="t('Choose a date')"
                />
                <InputError :message="form.errors.expected_close_date" />
            </div>
            <div class="grid gap-2">
                <Label for="pipeline">{{ t('Pipeline') }}</Label>
                <Select v-model="form.pipeline_id">
                    <SelectTrigger id="pipeline">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="pipeline in pipelines"
                            :key="pipeline.id"
                            :value="pipeline.id"
                        >
                            {{ pipeline.name }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.pipeline_id" />
            </div>
            <div class="grid gap-2">
                <Label for="stage">{{ t('Stage') }}</Label>
                <Select v-model="form.pipeline_stage_id">
                    <SelectTrigger id="stage">
                        <SelectValue :placeholder="t('Stage')" />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="stage in stageOptions"
                            :key="stage.value"
                            :value="stage.value"
                        >
                            {{ stage.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <InputError :message="form.errors.pipeline_stage_id" />
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
            <div class="grid gap-2">
                <Label for="contact">{{ t('Contact') }}</Label>
                <NullableSelect
                    id="contact"
                    v-model="form.contact_id"
                    :options="contacts"
                    :placeholder="t('No contact')"
                    :empty-label="t('No contact')"
                />
                <InputError :message="form.errors.contact_id" />
            </div>
        </div>

        <div class="grid gap-2">
            <Label for="owner">{{ t('Responsible') }}</Label>
            <NullableSelect
                id="owner"
                v-model="form.owner_id"
                :options="owners"
                :placeholder="t('No assignee')"
                :empty-label="t('No assignee')"
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
                {{ isEdit ? t('Save') : t('Create opportunity') }}
            </Button>
            <Button variant="ghost" as-child>
                <Link :href="cancelHref">{{ t('Cancel') }}</Link>
            </Button>
        </div>
    </form>
</template>
