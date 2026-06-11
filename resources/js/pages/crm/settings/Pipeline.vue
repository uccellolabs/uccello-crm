<script setup lang="ts">
import { Head, router, useForm, usePage } from '@inertiajs/vue3';
import {
    ArrowDown,
    ArrowUp,
    KanbanSquare,
    Pencil,
    Plus,
    Trash2,
    Trophy,
    X,
} from '@lucide/vue';
import { computed, ref } from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import Heading from '@/components/Heading.vue';
import InputError from '@/components/InputError.vue';
import { Badge } from '@/components/ui/badge';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/components/ui/card';
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
import { index } from '@/routes/pipeline-settings';
import {
    destroy as destroyStage,
    reorder as reorderStages,
    store as storeStage,
    update as updateStage,
} from '@/routes/pipeline-settings/stages';
import type { Team } from '@/types';

type StageItem = {
    id: number;
    name: string;
    color: string | null;
    position: number;
    probability: number | null;
    is_won: boolean;
    is_lost: boolean;
    deals_count: number;
};

type PipelineItem = {
    id: number;
    name: string;
    stages: StageItem[];
};

defineProps<{
    pipelines: PipelineItem[];
}>();

const { t, tChoice } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

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
const editing = ref<StageItem | null>(null);
const targetPipelineId = ref<number | null>(null);

const form = useForm({
    pipeline_id: null as number | null,
    name: '',
    color: '#2740e0' as string | null,
    probability: 50,
});

function openCreate(pipeline: PipelineItem) {
    editing.value = null;
    targetPipelineId.value = pipeline.id;
    form.clearErrors();
    form.pipeline_id = pipeline.id;
    form.name = '';
    form.color = '#2740e0';
    form.probability = 50;
    editorOpen.value = true;
}

function openEdit(stage: StageItem) {
    editing.value = stage;
    form.clearErrors();
    form.name = stage.name;
    form.color = stage.color ?? '#2740e0';
    form.probability = stage.probability ?? 50;
    editorOpen.value = true;
}

function submit() {
    const options = {
        preserveScroll: true,
        onSuccess: () => (editorOpen.value = false),
    };

    if (editing.value) {
        form.patch(
            updateStage([teamSlug.value, editing.value.id]).url,
            options,
        );
    } else {
        form.post(storeStage(teamSlug.value).url, options);
    }
}

function remove(id: number) {
    router.delete(destroyStage([teamSlug.value, id]).url, {
        preserveScroll: true,
    });
}

function move(pipeline: PipelineItem, stage: StageItem, direction: -1 | 1) {
    const ids = pipeline.stages.map((item) => item.id);
    const from = ids.indexOf(stage.id);
    const to = from + direction;

    if (to < 0 || to >= ids.length) {
        return;
    }

    [ids[from], ids[to]] = [ids[to], ids[from]];

    router.patch(
        reorderStages(teamSlug.value).url,
        { ids },
        { preserveScroll: true },
    );
}

function canDelete(stage: StageItem): boolean {
    return !stage.is_won && !stage.is_lost && stage.deals_count === 0;
}

defineOptions({
    layout: (props: { currentTeam?: Team | null }) => ({
        breadcrumbs: props.currentTeam
            ? [
                  {
                      title: useTranslations().t('Pipeline stages'),
                      href: index(props.currentTeam.slug),
                  },
              ]
            : [],
    }),
});
</script>

<template>
    <Head :title="t('Pipeline stages')" />

    <div class="mx-auto flex w-full max-w-4xl flex-col gap-6 p-4">
        <div class="flex items-center gap-3">
            <span
                class="bg-brand-gradient shadow-glow-violet flex h-10 w-10 items-center justify-center rounded-xl text-white"
            >
                <KanbanSquare class="h-5 w-5" />
            </span>
            <Heading
                variant="small"
                :title="t('Pipeline stages')"
                :description="
                    t('Rename, color and reorder your sales stages')
                "
            />
        </div>

        <Card v-for="pipeline in pipelines" :key="pipeline.id">
            <CardHeader
                class="flex flex-row flex-wrap items-center justify-between gap-2 space-y-0"
            >
                <CardTitle>{{ pipeline.name }}</CardTitle>
                <Button
                    variant="outline"
                    size="sm"
                    @click="openCreate(pipeline)"
                >
                    <Plus class="h-4 w-4" /> {{ t('New stage') }}
                </Button>
            </CardHeader>
            <CardContent class="divide-y p-0">
                <div
                    v-for="(stage, stageIndex) in pipeline.stages"
                    :key="stage.id"
                    class="flex items-center gap-3 p-3.5"
                >
                    <span
                        class="h-3 w-3 shrink-0 rounded-full"
                        :style="{
                            backgroundColor: stage.color ?? 'var(--primary)',
                        }"
                    />
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center gap-2">
                            <span class="font-medium">{{ stage.name }}</span>
                            <Badge
                                v-if="stage.is_won"
                                class="gap-1 bg-emerald-600 text-white"
                            >
                                <Trophy class="h-3 w-3" /> {{ t('Won stage') }}
                            </Badge>
                            <Badge
                                v-if="stage.is_lost"
                                variant="destructive"
                                class="gap-1"
                            >
                                <X class="h-3 w-3" /> {{ t('Lost stage') }}
                            </Badge>
                        </div>
                        <p class="text-xs text-muted-foreground">
                            {{ stage.probability ?? 0 }} % ·
                            {{ stage.deals_count }}
                            {{
                                stage.deals_count > 1
                                    ? 'opportunités'
                                    : 'opportunité'
                            }}
                        </p>
                    </div>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="stageIndex === 0"
                        aria-label="Monter l'étape"
                        @click="move(pipeline, stage, -1)"
                    >
                        <ArrowUp class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        :disabled="stageIndex === pipeline.stages.length - 1"
                        aria-label="Descendre l'étape"
                        @click="move(pipeline, stage, 1)"
                    >
                        <ArrowDown class="h-4 w-4" />
                    </Button>
                    <Button
                        variant="ghost"
                        size="icon"
                        class="h-8 w-8"
                        aria-label="Modifier l'étape"
                        @click="openEdit(stage)"
                    >
                        <Pencil class="h-4 w-4" />
                    </Button>
                    <ConfirmDialog
                        v-if="canDelete(stage)"
                        :description="`Supprimer l'étape « ${stage.name} » ?`"
                        @confirm="remove(stage.id)"
                    >
                        <Button
                            variant="ghost"
                            size="icon"
                            class="h-8 w-8"
                            aria-label="Supprimer l'étape"
                        >
                            <Trash2 class="h-4 w-4 text-destructive" />
                        </Button>
                    </ConfirmDialog>
                    <span
                        v-else
                        class="h-8 w-8"
                        aria-hidden="true"
                        :title="
                            stage.is_won || stage.is_lost
                                ? 'Étape terminale : non supprimable'
                                : 'Déplacez d\'abord les opportunités de cette étape'
                        "
                    />
                </div>
            </CardContent>
        </Card>

        <Dialog v-model:open="editorOpen">
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>
                        {{ editing ? "Modifier l'étape" : 'Nouvelle étape' }}
                    </DialogTitle>
                    <DialogDescription>
                        {{
                            editing
                                ? 'Le nom, la couleur et la probabilité sont modifiables.'
                                : "L'étape sera insérée avant les étapes terminales (Gagné / Perdu)."
                        }}
                    </DialogDescription>
                </DialogHeader>

                <form class="space-y-4" @submit.prevent="submit">
                    <div class="grid gap-2">
                        <Label for="stage-name">Nom</Label>
                        <Input id="stage-name" v-model="form.name" required />
                        <InputError :message="form.errors.name" />
                    </div>

                    <div class="grid gap-2">
                        <Label for="stage-probability">
                            Probabilité de gain (%)
                        </Label>
                        <Input
                            id="stage-probability"
                            v-model.number="form.probability"
                            type="number"
                            min="0"
                            max="100"
                            class="w-32"
                        />
                        <InputError :message="form.errors.probability" />
                    </div>

                    <div class="grid gap-2">
                        <Label>Couleur</Label>
                        <div class="flex flex-wrap items-center gap-2">
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
                                :aria-label="`Couleur ${swatch}`"
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
                            Annuler
                        </Button>
                        <Button type="submit" :disabled="form.processing">
                            {{ editing ? 'Enregistrer' : 'Ajouter' }}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    </div>
</template>
