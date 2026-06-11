<script setup lang="ts">
import { useForm, usePage } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';
import InputError from '@/components/InputError.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import {
    Dialog,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
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
import { store, update } from '@/routes/custom-fields';
import type { CustomFieldType, SelectOption } from '@/types';

type AdminField = {
    id: number;
    label: string;
    type: CustomFieldType;
    choices: string[];
    related_module: string | null;
    is_required: boolean;
    is_filterable: boolean;
    help_text: string | null;
};

const { t } = useTranslations();

const relatableModules = computed(() => [
    { value: 'company', label: t('Companies') },
    { value: 'contact', label: t('Contacts') },
    { value: 'deal', label: t('Opportunities') },
]);

const props = defineProps<{
    open: boolean;
    entity: string;
    fieldTypes: SelectOption[];
    field?: AdminField | null;
}>();

const emit = defineEmits<{ 'update:open': [boolean] }>();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');
const isEdit = computed(() => props.field != null);

const choicesText = ref('');

const form = useForm({
    entity_type: props.entity,
    label: '',
    type: 'text' as CustomFieldType,
    choices: [] as string[],
    related_module: 'company',
    is_required: false,
    is_filterable: false,
    help_text: '',
});

const needsChoices = computed(
    () => form.type === 'select' || form.type === 'multiselect',
);

const isRelation = computed(() => form.type === 'relation');

watch(
    () => props.open,
    (open) => {
        if (!open) {
            return;
        }

        form.clearErrors();
        form.entity_type = props.entity;

        if (props.field) {
            form.label = props.field.label;
            form.type = props.field.type;
            form.related_module = props.field.related_module ?? 'company';
            form.is_required = props.field.is_required;
            form.is_filterable = props.field.is_filterable;
            form.help_text = props.field.help_text ?? '';
            choicesText.value = props.field.choices.join('\n');
        } else {
            form.reset();
            form.entity_type = props.entity;
            choicesText.value = '';
        }
    },
);

function submit() {
    form.choices = choicesText.value
        .split('\n')
        .map((line) => line.trim())
        .filter(Boolean);

    const options = {
        preserveScroll: true,
        onSuccess: () => emit('update:open', false),
    };

    if (isEdit.value && props.field) {
        form.put(update([teamSlug.value, props.field.id]).url, options);
    } else {
        form.post(store(teamSlug.value).url, options);
    }
}
</script>

<template>
    <Dialog :open="open" @update:open="emit('update:open', $event)">
        <DialogContent>
            <DialogHeader>
                <DialogTitle>
                    {{ isEdit ? t('Edit field') : t('New field') }}
                </DialogTitle>
                <DialogDescription>
                    {{ t('Define a custom field for this module.') }}
                </DialogDescription>
            </DialogHeader>

            <form class="space-y-4" @submit.prevent="submit">
                <div class="grid gap-2">
                    <Label for="cf-label">{{ t('Label') }}</Label>
                    <Input id="cf-label" v-model="form.label" required />
                    <InputError :message="form.errors.label" />
                </div>

                <div class="grid gap-2">
                    <Label for="cf-type">{{ t('Type') }}</Label>
                    <Select v-model="form.type" :disabled="isEdit">
                        <SelectTrigger id="cf-type">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="type in fieldTypes"
                                :key="type.value"
                                :value="String(type.value)"
                            >
                                {{ type.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p v-if="isEdit" class="text-xs text-muted-foreground">
                        {{ t('The type cannot be changed after creation.') }}
                    </p>
                    <InputError :message="form.errors.type" />
                </div>

                <div v-if="needsChoices" class="grid gap-2">
                    <Label for="cf-choices">{{
                        t('Choices (one per line)')
                    }}</Label>
                    <Textarea id="cf-choices" v-model="choicesText" />
                    <InputError :message="form.errors.choices" />
                </div>

                <div v-if="isRelation" class="grid gap-2">
                    <Label for="cf-related">{{ t('Target module') }}</Label>
                    <Select v-model="form.related_module" :disabled="isEdit">
                        <SelectTrigger id="cf-related">
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            <SelectItem
                                v-for="module in relatableModules"
                                :key="module.value"
                                :value="module.value"
                            >
                                {{ module.label }}
                            </SelectItem>
                        </SelectContent>
                    </Select>
                    <p class="text-xs text-muted-foreground">
                        {{ t('The field will point to a record of this module.') }}
                        <template v-if="isEdit">
                            {{ t('The module cannot be changed after creation.') }}
                        </template>
                    </p>
                    <InputError :message="form.errors.related_module" />
                </div>

                <div class="grid gap-2">
                    <Label for="cf-help">{{ t('Help (optional)') }}</Label>
                    <Input id="cf-help" v-model="form.help_text" />
                    <InputError :message="form.errors.help_text" />
                </div>

                <div class="flex flex-wrap gap-6">
                    <label class="flex items-center gap-2 text-sm">
                        <Checkbox v-model="form.is_required" /> {{ t('Required') }}
                    </label>
                    <label class="flex items-center gap-2 text-sm">
                        <Checkbox v-model="form.is_filterable" /> {{ t('Filterable') }}
                    </label>
                </div>

                <DialogFooter class="gap-2">
                    <Button
                        type="button"
                        variant="ghost"
                        @click="emit('update:open', false)"
                    >
                        {{ t('Cancel') }}
                    </Button>
                    <Button type="submit" :disabled="form.processing">
                        {{ isEdit ? t('Save') : t('Create') }}
                    </Button>
                </DialogFooter>
            </form>
        </DialogContent>
    </Dialog>
</template>
