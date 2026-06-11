<script setup lang="ts">
import type { HTMLAttributes } from 'vue';
import { cn } from '@/lib/utils';

const props = defineProps<{
    class?: HTMLAttributes['class'];
}>();
</script>

<template>
    <th
        data-slot="table-head"
        :class="
            cn(
                'h-10 px-3 text-left align-middle text-xs font-medium tracking-wide text-muted-foreground uppercase [&:has([role=checkbox])]:pr-0',
                props.class,
            )
        "
    >
        <slot />
    </th>
</template>
