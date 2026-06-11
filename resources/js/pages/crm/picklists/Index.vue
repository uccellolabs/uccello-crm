<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    ListChecks,
    Lock,
    Pencil,
    Plus,
    Trash2,
} from '@lucide/vue';
import { computed, ref, watch } from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import EmptyState from '@/components/crm/EmptyState.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent } from '@/components/ui/card';
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
import { useTranslations } from '@/composables/useTranslations';
import { cn } from '@/lib/utils';
import { destroy, index, reorder, store, update } from '@/routes/picklists';
import type { Team } from '@/types';

type PicklistMeta = {
    value: string;
    label: string;
    description: string;
};

type PicklistOptionItem = {
    id: number;
    value: string;
    label: string;
    color: string | null;
    position: number;
    is_system: boolean;
};

const props = defineProps<{
    options: Record<string, PicklistOptionItem[]>;
    picklists: PicklistMeta[];
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const activeList = ref(props.picklists[0]?.value ?? 'industry');

const activeMeta = computed(() =>
    props.picklists.find((list) => list.value === activeList.value),
);

const items = computed(() => props.options[activeList.value] ?? []);

const SWATCHES = [
    '#2740e0',
    '#8b5cf6',
    '#06b6d4',
    '#10b981',
    '#f59e0b',
    '#f43f5e',
    '#94a3b8',
];

const editorOpen = ref(false);
const editing = ref<PicklistOptionItem | null>(null);

const form = useForm({
    picklist: activeList.value,
    label: '',
    color: null as string | null,
});

watch(activeList, (value) => {
    form.picklist = value;
});

function openCreate() {
    editing.value = null;
    form.clearErrors();
    form.label = '';
    form.color = null;
    editorOpen.value = true;
}

function openEdit(option: PicklistOptionItem) {
    editing.value = option;
    form.clearErrors();
    form.label = option.label;
    form.color = option.color;
    editorOpen.value = true;
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => (editorOpen.value = false),
    };

    if (editing.value) {
        form.patch(update([teamSlug.value, editing.value.id]).url, options);
    } else {
        form.post(store(teamSlug.value).url, options);
    }
}

function remove(id: number) {
    router.delete(destroy([teamSlug.value, id]).url, { preserveScroll: true });
}

function move(option: PicklistOptionItem, direction: -1 | 1) {
    const ids = items.value.map((item) => item.id);
    const from = ids.indexOf(option.id);
    const to = from + direction;

    if (to < 0 || to >= ids.length) {
        return;
    }

    [ids[from], ids[to]] = [ids[to], ids[from]];

    router.patch(
        reorder(teamSlug.value).url,
        { ids },
        { preserveScroll: true },
    );
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  {
                      title: useTranslations().t('Picklists'),
                      href: index(props.currentTeam.slug),
                  },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('Picklists')" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
        <div class="flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-3">
                <span
                    class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
                >
                    <ListChecks class="h-5 w-5" />
                </span>
                <Heading
                    variant="small"
                    :title="t('Picklists')"
                    :description="
                        t('Configure the values offered in dropdown lists')
                    "
                />
            </div>
            <Button @click="openCreate">
                <Plus class="h-4 w-4" /> {{ t('New option') }}
            </Button>
        </div>

        <div class="flex flex-wrap gap-2">
            <Button
                v-for="list in picklists"
                :key="list.value"
                size="sm"
                :variant="activeList === list.value ? 'default' : 'outline'"
                @click="activeList = list.value"
            >
                {{ list.label }}
            </Button>
        </div>

        <p v-if="activeMeta" class="text-sm text-muted-foreground">
            {{ activeMeta.description }}
        </p>

        <Card>
            <CardContent class="divide-y p-0">
                <div
                    v-for="(option, optionIndex) in items"
                    :key="option.id"
                    class="flex items-center gap-3 p-3.5"
                >
                    <span
                        class="h-3 w-3 shrink-0 rounded-full border"
                        :style="{
                            backgroundColor: option.color ?? 'transparent',
                        }"
                        :title="option.color ?? t('No color')"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium">{{ option.label }}</span>
                            <Badge
                                v-if="option.is_system"
                                variant="outline"
                                class="gap-1"
                            >
                                <Lock class="h-3 w-3" /> {{ t('System') }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ option.value }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="optionIndex === 0"
                        :aria-label="t('Move option up')"
                        @click="move(option, -1)"
                    >
                        <ArrowUp class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="optionIndex === items.length - 1"
                        :aria-label="t('Move option down')"
                        @click="move(option, 1)"
                    >
                        <ArrowDown class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :aria-label="t('Edit option')"
                        @click="openEdit(option)"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <ConfirmDialog
                        v-if="!option.is_system"
                        :description="
                            t(
                                'Delete the option « :label » ? Records using it will keep the saved value.',
                                { label: option.label },
                            )
                        "
                        @confirm="remove(option.id)"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                            :aria-label="t('Delete option')"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </ConfirmDialog>
                    <span v-else class="h-8 w-8" aria-hidden="true" />
                </div>

                <EmptyState
                    v-if="items.length === 0"
                    :icon="ListChecks"
                    :title="t('No option')"
                    :description="t('Add the first option of this list.')"
                >
                    <Button variant="outline" size="sm" @click="openCreate">
                        <Plus class="h-4 w-4" /> {{ t('New option') }}
                    </Button>
                </EmptyState>
            </CardContent>
        </Card>

        <Dialog v-model:open="editorOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editing ? t('Edit option') : t('New option') }}
                    </DialogTitle>
                    <DialogDescription>
                        {{ activeMeta?.label }} —
                        {{
                            editing
                                ? t(
                                      'the label and color are editable, the saved value stays stable.',
                                  )
                                : t(
                                      'the option will be offered immediately in forms.',
                                  )
                        }}
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="option-label">{{ t('Label') }}</Label>
                        <Input
                            id="option-label"
                            v-model="form.label"
                            required
                        />
                        <InputError :message="form.errors.label" />
                    </div>

                    <div class="grid gap-2">
                        <Label>{{ t('Color') }}</Label>
                        <div class="flex flex-wrap items-center gap-2">
                            <button
                                type="button"
                                :class="
                                    cn(
                                        'flex h-7 w-7 cursor-pointer items-center justify-center rounded-full border text-[10px] text-muted-foreground transition-shadow',
                                        form.color === null &&
                                            'ring-2 ring-ring ring-offset-2',
                                    )
                                "
                                :aria-label="t('No color')"
                                @click="form.color = null"
                            >
                                —
                            </button>
                            <button
                                v-for="swatch in SWATCHES"
                                :key="swatch"
                                type="button"
                                :class="
                                    cn(
                                        'h-7 w-7 cursor-pointer rounded-full border transition-shadow',
                                        form.color === swatch &&
                                            'ring-2 ring-ring ring-offset-2',
                                    )
                                "
                                :style="{ backgroundColor: swatch }"
                                :aria-label="t('Color :color', { color: swatch })"
                                @click="form.color = swatch"
                            />
                        </div>
                        <InputError :message="form.errors.color" />
                    </div>

                    <DialogFooter class="gap-2">
                        <Button
                            type="button"
                            variant="ghost"
                            @click="editorOpen = false"
                        >
                            {{ t('Cancel') }}
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editing ? t('Save') : t('Add') }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
