<script setup lang="ts">
import { computed  } from 'vue';
import type {HTMLAttributes} from 'vue';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import type { SelectOption } from '@/types';

const props = withDefaults(
    defineProps<{
        modelValue: number | null;
        options: SelectOption[];
        placeholder?: string;
        emptyLabel?: string;
        id?: string;
        class?: HTMLAttributes['class'];
    }>(),
    {
        placeholder: () => useTranslations().t('Select…'),
        emptyLabel: () => useTranslations().t('None'),
    },
);

const emit = defineEmits<{ 'update:modelValue': [number | null] }>();

// reka-ui Select compares values as strings and disallows empty-string items,
// so we use a "none" sentinel and convert at the boundary.
const NONE = 'none';

const proxy = computed<string>({
    get: () => (props.modelValue == null ? NONE : String(props.modelValue)),
    set: (value) =>
        emit('update:modelValue', value === NONE ? null : Number(value)),
});
</script>

<template>
    <Select v-model="proxy">
        <SelectTrigger :id="id" :class="cn('w-full sm:max-w-sm', props.class)">
            <SelectValue :placeholder="placeholder" />
        </SelectTrigger>
        <SelectContent>
            <SelectItem :value="NONE">{{ emptyLabel }}</SelectItem>
            <SelectItem
                v-for="option in options"
                :key="option.value"
                :value="String(option.value)"
            >
                {{ option.label }}
            </SelectItem>
        </SelectContent>
    </Select>
</template>
