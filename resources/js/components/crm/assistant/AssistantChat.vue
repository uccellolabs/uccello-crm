<script setup lang="ts">
import { usePage } from '@inertiajs/vue3';
import { ArrowUp, Database, Loader2, RotateCcw, Sparkles, X } from '@lucide/vue';
import { computed, nextTick, ref, watch } from 'vue';
import { useTranslations } from '@/composables/useTranslations';

const { t } = useTranslations();

type TraceItem = { name: string; summary: string };

type ChatMessage = {
    id: number;
    role: 'user' | 'assistant';
    content: string;
    pending?: boolean;
    error?: boolean;
    trace?: TraceItem[];
};

const SUGGESTIONS = [
    t('How many open opportunities and for what amount?'),
    t('Which companies in Paris?'),
    t('My overdue tasks'),
    t('Total amount won this quarter'),
];

const page = usePage();
const teamSlug = computed(() => page.props.currentTeam?.slug ?? null);

const open = ref(false);
const loading = ref(false);
const draft = ref('');
const messages = ref<ChatMessage[]>([]);
const thread = ref<HTMLElement | null>(null);
const field = ref<HTMLTextAreaElement | null>(null);
let counter = 0;

const canSend = computed(() => draft.value.trim().length > 0 && !loading.value);
const hasConversation = computed(() => messages.value.length > 0);

function toggle(): void {
    open.value = !open.value;

    if (open.value) {
        nextTick(() => field.value?.focus());
    }
}

function close(): void {
    open.value = false;
}

function resetConversation(): void {
    if (loading.value) {
        return;
    }

    messages.value = [];
    draft.value = '';
    counter = 0;
    nextTick(() => field.value?.focus());
}

function useSuggestion(text: string): void {
    draft.value = text;
    field.value?.focus();
}

function scrollToEnd(): void {
    nextTick(() => {
        if (thread.value) {
            thread.value.scrollTop = thread.value.scrollHeight;
        }
    });
}

function readXsrfToken(): string {
    const match = document.cookie.match(/(?:^|;\s*)XSRF-TOKEN=([^;]+)/);

    return match ? decodeURIComponent(match[1]) : '';
}

async function send(): Promise<void> {
    const text = draft.value.trim();

    if (text === '' || loading.value || !teamSlug.value) {
        return;
    }

    messages.value.push({ id: ++counter, role: 'user', content: text });
    draft.value = '';

    const placeholder: ChatMessage = { id: ++counter, role: 'assistant', content: '', pending: true };
    messages.value.push(placeholder);

    loading.value = true;
    scrollToEnd();

    const payload = messages.value
        .filter((message) => message.role === 'user' && !message.pending && !message.error && message.content !== '')
        .map((message) => ({ role: 'user' as const, content: message.content }));

    try {
        const response = await fetch(`/${teamSlug.value}/assistant/chat`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'Content-Type': 'application/json',
                Accept: 'application/json',
                'X-XSRF-TOKEN': readXsrfToken(),
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ messages: payload }),
        });

        if (!response.ok) {
            throw new Error(`HTTP ${response.status}`);
        }

        const data = (await response.json()) as { reply: string; trace: TraceItem[] };
        placeholder.content = data.reply;
        placeholder.trace = data.trace ?? [];
        placeholder.pending = false;
    } catch {
        placeholder.content = t('Connection to the assistant failed. Check your network and try again.');
        placeholder.pending = false;
        placeholder.error = true;
    } finally {
        loading.value = false;
        scrollToEnd();
        nextTick(() => field.value?.focus());
    }
}

function onKeydown(event: KeyboardEvent): void {
    if (event.key === 'Enter' && !event.shiftKey) {
        event.preventDefault();
        void send();
    }
}

function escapeHtml(value: string): string {
    return value
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;');
}

function renderInline(value: string): string {
    return escapeHtml(value)
        .replace(/\*\*([^*]+)\*\*/g, '<strong>$1</strong>')
        .replace(/`([^`]+)`/g, '<code class="rounded bg-black/5 px-1 py-0.5 text-[0.85em] dark:bg-white/10">$1</code>');
}

/** Minimal, XSS-safe Markdown: input is escaped first, then a small allow-list. */
function renderMarkdown(value: string): string {
    const lines = value.split('\n');
    const html: string[] = [];
    let list: string[] = [];

    const flush = (): void => {
        if (list.length > 0) {
            html.push(`<ul class="my-1 ml-4 list-disc space-y-0.5">${list.join('')}</ul>`);
            list = [];
        }
    };

    for (const line of lines) {
        const bullet = line.match(/^\s*[-*]\s+(.*)$/);

        if (bullet) {
            list.push(`<li>${renderInline(bullet[1])}</li>`);
            continue;
        }

        flush();

        if (line.trim() === '') {
            continue;
        }

        html.push(`<p class="my-1">${renderInline(line)}</p>`);
    }

    flush();

    return html.join('');
}

watch(
    () => messages.value.length,
    () => scrollToEnd(),
);
</script>

<template>
    <div v-if="teamSlug" class="pointer-events-none fixed inset-0 z-50 flex items-end justify-end p-4 sm:p-6">
        <!-- Panel -->
        <Transition
            enter-active-class="motion-safe:transition motion-safe:duration-200 motion-safe:ease-out"
            enter-from-class="opacity-0 translate-y-3 motion-safe:scale-95"
            enter-to-class="opacity-100 translate-y-0 scale-100"
            leave-active-class="motion-safe:transition motion-safe:duration-150 motion-safe:ease-in"
            leave-from-class="opacity-100 scale-100"
            leave-to-class="opacity-0 translate-y-3 motion-safe:scale-95"
        >
            <section
                v-if="open"
                class="pointer-events-auto absolute right-4 bottom-4 flex h-[min(80vh,640px)] w-[min(calc(100vw-2rem),420px)] origin-bottom-right flex-col overflow-hidden rounded-2xl border border-border bg-card shadow-card backdrop-blur-xl supports-[backdrop-filter]:bg-card/95"
                role="dialog"
                aria-modal="false"
                :aria-label="t('CRM AI assistant')"
            >
                <!-- Header -->
                <header class="bg-mesh relative flex items-center gap-3 border-b border-border px-4 py-3.5">
                    <span class="bg-brand-gradient flex size-9 items-center justify-center rounded-xl text-white shadow-glow-primary">
                        <Sparkles class="size-5" />
                    </span>
                    <div class="min-w-0 flex-1">
                        <p class="truncate text-sm font-semibold text-foreground">{{ t('AI assistant') }}</p>
                        <p class="truncate text-xs text-muted-foreground">{{ t('Query your CRM data') }}</p>
                    </div>
                    <button
                        v-if="hasConversation"
                        type="button"
                        class="flex size-9 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition-colors duration-200 hover:bg-foreground/5 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                        :aria-label="t('Reset conversation')"
                        :disabled="loading"
                        @click="resetConversation"
                    >
                        <RotateCcw class="size-4" />
                    </button>
                    <button
                        type="button"
                        class="flex size-9 cursor-pointer items-center justify-center rounded-lg text-muted-foreground transition-colors duration-200 hover:bg-foreground/5 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                        :aria-label="t('Close assistant')"
                        @click="close"
                    >
                        <X class="size-5" />
                    </button>
                </header>

                <!-- Thread -->
                <div ref="thread" class="flex-1 space-y-4 overflow-y-auto px-4 py-4">
                    <!-- Empty state -->
                    <div v-if="messages.length === 0" class="flex h-full flex-col justify-center gap-4 py-6 text-center">
                        <div class="mx-auto flex size-14 items-center justify-center rounded-2xl bg-primary/10">
                            <Sparkles class="size-7 text-primary" />
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-foreground">{{ t('Ask a question about your CRM') }}</p>
                            <p class="mx-auto mt-1 max-w-[18rem] text-xs text-muted-foreground">
                                {{ t('Companies, contacts, opportunities, tasks, activities — custom fields included.') }}
                            </p>
                        </div>
                        <div class="flex flex-wrap justify-center gap-2">
                            <button
                                v-for="suggestion in SUGGESTIONS"
                                :key="suggestion"
                                type="button"
                                class="card-hover cursor-pointer rounded-full border border-border bg-card/60 px-3 py-1.5 text-xs text-foreground/80 transition-colors duration-200 hover:border-primary/40 hover:text-foreground focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none"
                                @click="useSuggestion(suggestion)"
                            >
                                {{ suggestion }}
                            </button>
                        </div>
                    </div>

                    <!-- Messages -->
                    <div
                        v-for="message in messages"
                        :key="message.id"
                        class="flex"
                        :class="message.role === 'user' ? 'justify-end' : 'justify-start'"
                    >
                        <div class="max-w-[85%] space-y-1.5">
                            <!-- Tool activity chips -->
                            <div v-if="message.trace && message.trace.length" class="flex flex-wrap gap-1.5">
                                <span
                                    v-for="(item, index) in message.trace"
                                    :key="index"
                                    class="inline-flex items-center gap-1 rounded-full border border-border bg-card/60 px-2 py-0.5 text-[11px] font-medium text-muted-foreground"
                                >
                                    <Database class="size-3 text-primary" />
                                    {{ item.summary }}
                                </span>
                            </div>

                            <div
                                class="rounded-2xl px-3.5 py-2.5 text-sm leading-relaxed shadow-sm"
                                :class="[
                                    message.role === 'user'
                                        ? 'rounded-br-md bg-primary text-primary-foreground'
                                        : 'rounded-bl-md border border-border bg-card text-foreground',
                                    message.error ? 'border-destructive/40 text-destructive' : '',
                                ]"
                            >
                                <!-- Typing indicator -->
                                <span v-if="message.pending" class="flex items-center gap-1.5 py-0.5" :aria-label="t('The assistant is thinking')">
                                    <span class="size-1.5 animate-bounce rounded-full bg-current opacity-60 [animation-delay:-0.3s]" />
                                    <span class="size-1.5 animate-bounce rounded-full bg-current opacity-60 [animation-delay:-0.15s]" />
                                    <span class="size-1.5 animate-bounce rounded-full bg-current opacity-60" />
                                </span>
                                <!-- eslint-disable-next-line vue/no-v-html -->
                                <div v-else class="assistant-prose" v-html="message.role === 'assistant' ? renderMarkdown(message.content) : escapeHtml(message.content)" />
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Composer -->
                <div class="border-t border-border bg-card/50 p-3">
                    <div class="flex items-center gap-2 rounded-xl border border-border bg-background px-3 py-1.5 focus-within:border-primary/50 focus-within:ring-2 focus-within:ring-ring/40">
                        <textarea
                            ref="field"
                            v-model="draft"
                            rows="1"
                            :placeholder="t('Type your question…')"
                            class="max-h-28 min-h-9 flex-1 resize-none border-0 bg-transparent py-1.5 text-sm leading-normal text-foreground placeholder:text-muted-foreground focus:outline-none"
                            :aria-label="t('Your question')"
                            @keydown="onKeydown"
                        />
                        <button
                            type="button"
                            class="flex size-9 shrink-0 cursor-pointer items-center justify-center rounded-lg bg-primary text-primary-foreground transition-opacity duration-200 hover:opacity-90 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none disabled:cursor-not-allowed disabled:opacity-40"
                            :disabled="!canSend"
                            :aria-label="t('Send')"
                            @click="send"
                        >
                            <Loader2 v-if="loading" class="size-5 animate-spin" />
                            <ArrowUp v-else class="size-5" />
                        </button>
                    </div>
                    <p class="mt-1.5 px-1 text-[11px] text-muted-foreground">{{ t('The assistant only reads your team’s data.') }}</p>
                </div>
            </section>
        </Transition>

        <!-- Launcher -->
        <button
            v-if="!open"
            type="button"
            class="bg-brand-gradient pointer-events-auto group relative flex size-14 cursor-pointer items-center justify-center rounded-2xl text-white shadow-glow-primary transition-transform duration-200 hover:-translate-y-0.5 focus-visible:ring-2 focus-visible:ring-ring focus-visible:outline-none motion-reduce:transform-none"
            :aria-label="t('Open AI assistant')"
            @click="toggle"
        >
            <span class="absolute inset-0 rounded-2xl bg-primary/40 opacity-0 motion-safe:animate-ping motion-safe:opacity-40" aria-hidden="true" />
            <Sparkles class="relative size-7" />
        </button>
    </div>
</template>

<style scoped>
.assistant-prose :deep(p:first-child) {
    margin-top: 0;
}
.assistant-prose :deep(p:last-child) {
    margin-bottom: 0;
}
</style>
