<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { Pencil, Plus, SlidersHorizontal, Trash2 } from '@lucide/vue';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import CustomFieldEditor from '@/components/crm/CustomFieldEditor.vue';
import Heading from '@/components/Heading.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
import { useTranslations } from '@/composables/useTranslations';
import { destroy, index } from '@/routes/custom-fields';
import type { CustomFieldType, SelectOption, Team } from '@/types';

type AdminField = {
    id: number;
    entity_type: string;
    key: string;
    label: string;
    type: CustomFieldType;
    type_label: string;
    choices: string[];
    related_module: string | null;
    is_required: boolean;
    is_filterable: boolean;
    help_text: string | null;
    position: number;
};

const props = defineProps<{
    definitions: Record<string, AdminField[]>;
    entities: SelectOption[];
    fieldTypes: SelectOption[];
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const activeEntity = ref(String(props.entities[0]?.value ?? 'company'));

const fields = computed(() => props.definitions[activeEntity.value] ?? []);

const editorOpen = ref(false);
const editing = ref<AdminField | null>(null);

function openCreate() {
    editing.value = null;
    editorOpen.value = true;
}

function openEdit(field: AdminField) {
    editing.value = field;
    editorOpen.value = true;
}

function remove(id: number) {
    router.delete(destroy([teamSlug.value, id]).url, { preserveScroll: true });
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  {
                      title: useTranslations().t('Custom fields'),
                      href: index(props.currentTeam.slug),
                  },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('Custom fields')" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <SlidersHorizontal class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Custom fields')"
                    :description="t('Add tailored fields to each module')"
                />
            </div>
            <Button @click="openCreate">
                <Plus class="h-4 w-4" /> {{ t('New field') }}
            </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="entity in entities"
                :key="entity.value"
                size="sm"
                :variant="
                    activeEntity === String(entity.value)
                        ? 'default'
                        : 'outline'
                "
                @click="activeEntity = String(entity.value)"
            >
                {{ entity.label }}
            </Button>
        </div>

        <Card>
            <CardContent class="divide-y p-0">
                <div
                    v-for="field in fields"
                    :key="field.id"
                    class="flex items-center gap-3 p-4"
                >
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium">{{ field.label }}</span>
                            <Badge variant="secondary">{{
                                field.type_label
                            }}</Badge>
                            <Badge v-if="field.is_required" variant="outline">
                                {{ t('Required') }}
                            </Badge>
                            <Badge v-if="field.is_filterable" variant="outline">
                                {{ t('Filterable') }}
                            </Badge>
                        </div>
                        <p
                            v-if="field.choices.length"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{ field.choices.join(' · ') }}
                        </p>
                        <p
                            v-else-if="field.help_text"
                            class="mt-1 text-xs text-muted-foreground"
                        >
                            {{ field.help_text }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :aria-label="t('Edit field')"
                        @click="openEdit(field)"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <ConfirmDialog
                        :description="
                            t('Delete the field « :label » ?', {
                                label: field.label,
                            })
                        "
                        @confirm="remove(field.id)"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                            :aria-label="t('Delete field')"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </ConfirmDialog>
                </div>

                <p
                    v-if="fields.length === 0"
                    class="p-8 text-center text-sm text-muted-foreground"
                >
                    {{ t('No custom field for this module.') }}
                </p>
            </CardContent>
        </Card>

        <CustomFieldEditor
            v-model:open="editorOpen"
            :entity="activeEntity"
            :field-types="fieldTypes"
            :field="editing"
        />
    </div>
</template>
