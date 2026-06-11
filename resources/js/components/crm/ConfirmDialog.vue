<script setup lang="ts">
import { computed, ref, watch } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

const props = withDefaults(
    defineProps<{
        title?: string;
        description?: string;
        confirmLabel?: string;
        processing?: boolean;
    }>(),
    {
        title: () => useTranslations().t('Confirm deletion'),
        description: () => useTranslations().t('This action cannot be undone.'),
        confirmLabel: () => useTranslations().t('Delete'),
        processing: false,
    },
);

const emit = defineEmits<{ confirm: [] }>();

const open = ref(false);
const submitting = ref(false);

// Disabled while the parent reports an in-flight request OR right after the
// first click — guarantees a single emit even on a rapid double-click.
const busy = computed(() => submitting.value || props.processing);

watch(open, (isOpen) => {
    if (!isOpen) {
        submitting.value = false;
    }
});

function confirm() {
    if (busy.value) {
        return;
    }

    submitting.value = true;
    emit('confirm');
}
</script>

<template>
    <Dialog v-model:open="open">
        <DialogTrigger as-child>
            <slot />
        </DialogTrigger>
        <DialogContent>
            <DialogHeader>
                <DialogTitle>{{ title }}</DialogTitle>
                <DialogDescription>{{ description }}</DialogDescription>
            </DialogHeader>
            <DialogFooter class="gap-2">
                <DialogClose as-child>
                    <Button variant="ghost" :disabled="busy">{{ t('Cancel') }}</Button>
                </DialogClose>
                <Button
                    variant="destructive"
                    :disabled="busy"
                    @click="confirm"
                >
                    {{ confirmLabel }}
                </Button>
            </DialogFooter>
        </DialogContent>
    </Dialog>
</template>
