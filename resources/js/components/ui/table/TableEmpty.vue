<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    colspan?: number;
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <tr>
        <td
            data-slot="table-empty"
            :colspan="props.colspan ?? 1"
            :class="cn('p-8 text-center text-sm text-muted-foreground', props.class)"
        >
            <slot />
        </td>
    </tr>
</template>
