<script setup lang="ts">
import { computed } from 'vue';
import NullableSelect from '@/components/crm/NullableSelect.vue';
import InputError from '@/components/InputError.vue';
import { Checkbox } from '@/components/ui/checkbox';
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
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import type { CustomFieldDefinition } from '@/types';

const props = defineProps<{
    definition: CustomFieldDefinition;
    modelValue: unknown;
    error?: string;
    readonly?: boolean;
}>();

const emit = defineEmits<{ 'update:modelValue': [unknown] }>();

const { t } = useTranslations();

const fieldId = computed(() => `cf_${props.definition.key}`);

// Select/multiselect compare string values; relation choices are numeric and
// handled separately below.
const choices = computed(() =>
    (props.definition.options.choices ?? []).map((choice) => ({
        value: String(choice.value),
        label: choice.label,
    })),
);

const textType = computed(() => {
    switch (props.definition.type) {
        case 'email':
            return 'email';
        case 'url':
            return 'url';
        case 'phone':
            return 'tel';
        case 'number':
            return 'number';
        default:
            return 'text';
    }
});

const SELECT_NONE = '__none__';

const selectProxy = computed<string>({
    get: () => {
        const value = props.modelValue;

        return value == null || value === '' ? SELECT_NONE : String(value);
    },
    set: (value) =>
        emit('update:modelValue', value === SELECT_NONE ? null : value),
});

const multiValue = computed<string[]>(() =>
    Array.isArray(props.modelValue) ? (props.modelValue as string[]) : [],
);

// Relation fields: the backend injects the target module's records as
// numeric {value, label} choices.
const relationOptions = computed(() =>
    (props.definition.options.choices ?? []).map((choice) => ({
        value: Number(choice.value),
        label: choice.label,
    })),
);

const relationValue = computed<number | null>({
    get: () =>
        props.modelValue == null || props.modelValue === ''
            ? null
            : Number(props.modelValue),
    set: (value) => emit('update:modelValue', value),
});

function toggleMulti(value: string, checked: boolean) {
    const current = new Set(multiValue.value);

    if (checked) {
        current.add(value);
    } else {
        current.delete(value);
    }

    emit('update:modelValue', [...current]);
}

// ---- read-only display -------------------------------------------------
const displayValue = computed<string>(() => {
    const value = props.modelValue;

    if (value == null || value === '') {
        return '—';
    }

    switch (props.definition.type) {
        case 'checkbox':
            return value ? t('Yes') : t('No');
        case 'multiselect':
            return Array.isArray(value) && value.length
                ? value.join(', ')
                : '—';
        case 'date':
            return new Intl.DateTimeFormat('fr-FR', {
                dateStyle: 'medium',
            }).format(new Date(String(value)));
        case 'relation':
            return (
                relationOptions.value.find((o) => o.value === Number(value))
                    ?.label ?? `#${String(value)}`
            );
        default:
            return String(value);
    }
});
</script>

<template>
    <!-- Read-only rendering (detail views) -->
    <div v-if="readonly" class="space-y-0.5">
        <p class="text-xs text-muted-foreground">{{ definition.label }}</p>
        <p class="font-medium break-words">{{ displayValue }}</p>
    </div>

    <!-- Editable rendering (forms) -->
    <div v-else class="grid gap-2">
        <div class="flex items-center gap-2">
            <Checkbox
                v-if="definition.type === 'checkbox'"
                :id="fieldId"
                :model-value="Boolean(modelValue)"
                @update:model-value="emit('update:modelValue', $event)"
            />
            <Label :for="fieldId">
                {{ definition.label }}
                <span v-if="definition.is_required" class="text-destructive"
                    >*</span
                >
            </Label>
        </div>

        <template v-if="definition.type === 'textarea'">
            <Textarea
                :id="fieldId"
                :model-value="(modelValue as string) ?? ''"
                @update:model-value="emit('update:modelValue', $event)"
            />
        </template>

        <template v-else-if="definition.type === 'select'">
            <Select v-model="selectProxy">
                <SelectTrigger :id="fieldId" class="w-full sm:max-w-sm">
                    <SelectValue :placeholder="t('Select…')" />
                </SelectTrigger>
                <SelectContent>
                    <SelectItem
                        v-if="!definition.is_required"
                        :value="SELECT_NONE"
                    >
                        —
                    </SelectItem>
                    <SelectItem
                        v-for="choice in choices"
                        :key="choice.value"
                        :value="choice.value"
                    >
                        {{ choice.label }}
                    </SelectItem>
                </SelectContent>
            </Select>
        </template>

        <template v-else-if="definition.type === 'multiselect'">
            <div class="flex flex-wrap gap-3">
                <label
                    v-for="choice in choices"
                    :key="choice.value"
                    class="flex items-center gap-2 text-sm"
                >
                    <Checkbox
                        :model-value="multiValue.includes(choice.value)"
                        @update:model-value="
                            toggleMulti(choice.value, Boolean($event))
                        "
                    />
                    {{ choice.label }}
                </label>
            </div>
        </template>

        <template v-else-if="definition.type === 'checkbox'">
            <!-- control rendered inline with the label above -->
        </template>

        <template v-else-if="definition.type === 'date'">
            <DatePicker
                :id="fieldId"
                :model-value="(modelValue as string) ?? ''"
                @update:model-value="emit('update:modelValue', $event)"
            />
        </template>

        <template v-else-if="definition.type === 'relation'">
            <NullableSelect
                :id="fieldId"
                v-model="relationValue"
                :options="relationOptions"
                :placeholder="t('Choose a record')"
                :empty-label="t('None')"
            />
        </template>

        <template v-else>
            <Input
                :id="fieldId"
                :type="textType"
                :model-value="(modelValue as string | number) ?? ''"
                @update:model-value="emit('update:modelValue', $event)"
            />
        </template>

        <p v-if="definition.help_text" class="text-xs text-muted-foreground">
            {{ definition.help_text }}
        </p>
        <InputError :message="error" />
    </div>
</template>
