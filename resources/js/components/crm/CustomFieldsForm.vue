<script setup lang="ts">
import CustomFieldRenderer from '@/components/crm/CustomFieldRenderer.vue';
import type {
    CustomFieldDefinition,
    CustomFieldValue,
    CustomFieldValues,
} from '@/types';

const props = defineProps<{
    definitions: CustomFieldDefinition[];
    modelValue: CustomFieldValues;
    errors?: Record<string, string>;
}>();

const emit = defineEmits<{ 'update:modelValue': [CustomFieldValues] }>();

function setValue(key: string, value: unknown) {
    emit('update:modelValue', {
        ...props.modelValue,
        [key]: value as CustomFieldValue,
    });
}

function errorFor(key: string): string | undefined {
    return props.errors?.[`custom_fields.${key}`];
}
</script>

<template>
    <div v-if="definitions.length" class="grid gap-5 sm:grid-cols-2">
        <CustomFieldRenderer
            v-for="definition in definitions"
            :key="definition.id"
            :definition="definition"
            :model-value="modelValue[definition.key]"
            :error="errorFor(definition.key)"
            @update:model-value="setValue(definition.key, $event)"
        />
    </div>
</template>
