<script setup lang="ts">
import {
    DateFormatter,
    getLocalTimeZone,
    parseDate,
    today,
    type DateValue,
} from '@internationalized/date';
import { CalendarDays, ChevronLeft, ChevronRight } from '@lucide/vue';
import {
    CalendarCell,
    CalendarCellTrigger,
    CalendarGrid,
    CalendarGridBody,
    CalendarGridHead,
    CalendarGridRow,
    CalendarHeadCell,
    CalendarHeader,
    CalendarHeading,
    CalendarNext,
    CalendarPrev,
    CalendarRoot,
} from 'reka-ui';
import { computed, ref } from 'vue';
import type { HTMLAttributes } from 'vue';
import { Button } from '@/components/ui/button';
import {
    Popover,
    PopoverContent,
    PopoverTrigger,
} from '@/components/ui/popover';
import { cn } from '@/lib/utils';

const props = withDefaults(
    defineProps<{
        // Empty string means "no date" so the model plugs straight into useForm.
        modelValue?: string;
        id?: string;
        placeholder?: string;
        clearable?: boolean;
        ariaLabel?: string;
        class?: HTMLAttributes['class'];
    }>(),
    { modelValue: '', placeholder: 'Choisir une date', clearable: true },
);

const emit = defineEmits<{ 'update:modelValue': [string] }>();

defineOptions({ inheritAttrs: false });

const open = ref(false);

const calendarValue = computed<DateValue | undefined>({
    get: () => {
        if (!props.modelValue) {
            return undefined;
        }

        try {
            return parseDate(props.modelValue);
        } catch {
            return undefined;
        }
    },
    set: (value) => {
        emit('update:modelValue', value ? value.toString() : '');
        open.value = false;
    },
});

const formatter = new DateFormatter('fr-FR', { dateStyle: 'medium' });

const label = computed(() =>
    calendarValue.value
        ? formatter.format(calendarValue.value.toDate(getLocalTimeZone()))
        : null,
);

function selectToday() {
    calendarValue.value = today(getLocalTimeZone());
}

function clear() {
    emit('update:modelValue', '');
    open.value = false;
}

const navButtonClass =
    'inline-flex size-7 cursor-pointer items-center justify-center rounded-md text-muted-foreground transition-colors hover:bg-muted hover:text-foreground focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none disabled:pointer-events-none disabled:opacity-40';

const cellTriggerClass =
    'inline-flex size-8 cursor-pointer items-center justify-center rounded-md text-sm font-normal whitespace-nowrap transition-colors hover:bg-muted focus-visible:ring-ring/50 focus-visible:ring-[3px] focus-visible:outline-none data-[disabled]:pointer-events-none data-[disabled]:opacity-40 data-[outside-view]:text-muted-foreground/40 data-[selected]:bg-primary data-[selected]:text-primary-foreground data-[selected]:hover:bg-primary data-[today]:font-semibold data-[today]:text-primary data-[selected]:data-[today]:text-primary-foreground';
</script>

<template>
    <Popover v-model:open="open">
        <PopoverTrigger as-child>
            <button
                :id="id"
                type="button"
                :aria-label="ariaLabel ?? placeholder"
                :class="
                    cn(
                        'border-input dark:bg-input/30 flex h-9 w-full min-w-0 cursor-pointer items-center gap-2 rounded-md border bg-transparent px-3 py-1 text-left text-base shadow-xs transition-[color,box-shadow] outline-none md:text-sm',
                        'focus-visible:border-ring focus-visible:ring-ring/50 focus-visible:ring-[3px]',
                        props.class,
                    )
                "
            >
                <CalendarDays
                    class="text-muted-foreground h-4 w-4 shrink-0"
                />
                <span
                    class="truncate"
                    :class="{ 'text-muted-foreground': !label }"
                >
                    {{ label ?? placeholder }}
                </span>
            </button>
        </PopoverTrigger>
        <PopoverContent class="w-auto p-0" align="start">
            <CalendarRoot
                v-slot="{ weekDays, grid }"
                v-model="calendarValue"
                locale="fr-FR"
                :week-starts-on="1"
                weekday-format="short"
                fixed-weeks
                class="p-3"
            >
                <CalendarHeader class="flex items-center justify-between">
                    <CalendarPrev :class="navButtonClass">
                        <ChevronLeft class="h-4 w-4" />
                    </CalendarPrev>
                    <CalendarHeading
                        class="text-sm font-medium capitalize"
                    />
                    <CalendarNext :class="navButtonClass">
                        <ChevronRight class="h-4 w-4" />
                    </CalendarNext>
                </CalendarHeader>
                <CalendarGrid
                    v-for="month in grid"
                    :key="month.value.toString()"
                    class="mt-3 w-full border-collapse select-none"
                >
                    <CalendarGridHead>
                        <CalendarGridRow class="flex">
                            <CalendarHeadCell
                                v-for="day in weekDays"
                                :key="day"
                                class="text-muted-foreground size-8 text-xs font-normal"
                            >
                                {{ day }}
                            </CalendarHeadCell>
                        </CalendarGridRow>
                    </CalendarGridHead>
                    <CalendarGridBody>
                        <CalendarGridRow
                            v-for="(weekDates, index) in month.rows"
                            :key="`week-${index}`"
                            class="mt-1 flex w-full"
                        >
                            <CalendarCell
                                v-for="weekDate in weekDates"
                                :key="weekDate.toString()"
                                :date="weekDate"
                                class="p-0"
                            >
                                <CalendarCellTrigger
                                    :day="weekDate"
                                    :month="month.value"
                                    :class="cellTriggerClass"
                                />
                            </CalendarCell>
                        </CalendarGridRow>
                    </CalendarGridBody>
                </CalendarGrid>
            </CalendarRoot>
            <div class="flex items-center justify-between gap-2 border-t p-2">
                <Button
                    variant="ghost"
                    size="sm"
                    type="button"
                    @click="selectToday"
                >
                    Aujourd'hui
                </Button>
                <Button
                    v-if="clearable && modelValue"
                    variant="ghost"
                    size="sm"
                    type="button"
                    class="text-muted-foreground"
                    @click="clear"
                >
                    Effacer
                </Button>
            </div>
        </PopoverContent>
    </Popover>
</template>
