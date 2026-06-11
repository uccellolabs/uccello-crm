<script setup lang="ts">
import { router, useForm, usePage } from '@inertiajs/vue3';
import {
    Calendar,
    Mail,
    Phone,
    StickyNote,
    Trash2,
} from '@lucide/vue';
import { computed  } from 'vue';
import type {Component} from 'vue';
import ConfirmDialog from '@/components/crm/ConfirmDialog.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Textarea } from '@/components/ui/textarea';
import { useTranslations } from '@/composables/useTranslations';
import { store as storeActivity, destroy as destroyActivity } from '@/routes/activities';
import type { ActivityItem, MorphTarget, SelectOption } from '@/types';

const props = defineProps<{
    activities: ActivityItem[];
    target: MorphTarget;
    types: SelectOption[];
    canManage: boolean;
}>();

const { t } = useTranslations();

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? '');

const icons: Record<string, Component> = {
    call: Phone,
    email: Mail,
    meeting: Calendar,
    note: StickyNote,
};

const typeColor: Record<string, string> = {
    call: 'var(--brand-cyan)',
    email: 'var(--brand-violet)',
    meeting: 'var(--brand-amber)',
    note: 'var(--brand-indigo)',
};

const form = useForm({
    type: 'note',
    subject: '',
    body: '',
    subjectable_type: props.target.type,
    subjectable_id: props.target.id,
});

function submit() {
    form.post(storeActivity(teamSlug.value).url, {
        preserveScroll: true,
        onSuccess: () => form.reset('subject', 'body'),
    });
}

const dateFormatter = new Intl.DateTimeFormat('fr-FR', {
    dateStyle: 'medium',
    timeStyle: 'short',
});

function formatDate(iso: string): string {
    return dateFormatter.format(new Date(iso));
}

function remove(id: number) {
    router.delete(destroyActivity([teamSlug.value, id]).url, {
        preserveScroll: true,
    });
}
</script>

<template>
    <div class="space-y-5">
        <form
            v-if="canManage"
            class="space-y-3 rounded-lg border bg-muted/30 p-4"
            @submit.prevent="submit"
        >
            <div class="flex flex-col gap-3 sm:flex-row">
                <Select v-model="form.type">
                    <SelectTrigger class="sm:w-44" :aria-label="t('Activity type')">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        <SelectItem
                            v-for="type in types"
                            :key="type.value"
                            :value="String(type.value)"
                        >
                            {{ type.label }}
                        </SelectItem>
                    </SelectContent>
                </Select>
                <Input
                    v-model="form.subject"
                    class="flex-1"
                    :placeholder="t('Subject (optional)')"
                    :aria-label="t('Activity subject')"
                />
            </div>
            <Textarea
                v-model="form.body"
                :placeholder="t('Describe the exchange…')"
                :aria-label="t('Activity details')"
            />
            <div class="flex justify-end">
                <Button type="submit" size="sm" :disabled="form.processing">
                    {{ t('Log activity') }}
                </Button>
            </div>
        </form>

        <ol
            v-if="activities.length"
            class="relative space-y-5 before:absolute before:top-1 before:bottom-3 before:left-[15px] before:w-px before:bg-border"
        >
            <li
                v-for="activity in activities"
                :key="activity.id"
                class="relative flex gap-3"
            >
                <span
                    class="z-10 mt-0.5 flex h-8 w-8 shrink-0 items-center justify-center rounded-full text-white ring-4 ring-card"
                    :style="{ backgroundColor: typeColor[activity.type] ?? 'var(--brand-indigo)' }"
                >
                    <component :is="icons[activity.type] ?? StickyNote" class="h-4 w-4" />
                </span>
                <div class="min-w-0 flex-1">
                    <div class="flex items-start justify-between gap-2">
                        <div class="min-w-0">
                            <p class="text-sm font-medium">
                                {{ activity.subject || activity.type_label }}
                            </p>
                            <p class="text-xs text-muted-foreground">
                                {{ activity.type_label }} ·
                                {{ formatDate(activity.occurred_at) }}
                                <template v-if="activity.user">
                                    · {{ activity.user.name }}
                                </template>
                            </p>
                        </div>
                        <ConfirmDialog
                            v-if="canManage"
                            :description="t('Delete this activity?')"
                            @confirm="remove(activity.id)"
                        >
                            <Button
                                variant="ghost"
                                size="icon"
                                class="h-7 w-7"
                                :aria-label="t('Delete activity')"
                            >
                                <Trash2 class="h-3.5 w-3.5 text-muted-foreground" />
                            </Button>
                        </ConfirmDialog>
                    </div>
                    <p
                        v-if="activity.body"
                        class="mt-1 text-sm whitespace-pre-line text-muted-foreground"
                    >
                        {{ activity.body }}
                    </p>
                </div>
            </li>
        </ol>

        <p v-else class="text-sm text-muted-foreground">
            {{ t('No activity logged yet.') }}
        </p>
    </div>
</template>
