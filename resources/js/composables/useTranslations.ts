import { usePage } from '@inertiajs/vue3';
import { computed } from 'vue';

type Replacements = Record<string, string | number>;

function interpolate(message: string, replacements?: Replacements): string {
    if (!replacements) {
        return message;
    }

    return Object.keys(replacements).reduce(
        (acc, key) => acc.replaceAll(`:${key}`, String(replacements[key])),
        message,
    );
}

/**
 * Lightweight i18n for Inertia: the active locale + its dictionary are shared
 * as page props by HandleInertiaRequests. `t()` looks a key up (falling back to
 * the key itself — the English source string), with `:name` interpolation.
 * `tChoice()` handles Laravel-style `singular|plural` strings.
 */
export function useTranslations() {
    const page = usePage();

    const messages = computed<Record<string, string>>(
        () => (page.props.translations as Record<string, string> | undefined) ?? {},
    );

    const locale = computed<'fr' | 'en'>(
        () => (page.props.locale as 'fr' | 'en' | undefined) ?? 'fr',
    );

    function t(key: string, replacements?: Replacements): string {
        return interpolate(messages.value[key] ?? key, replacements);
    }

    function tChoice(
        key: string,
        count: number,
        replacements?: Replacements,
    ): string {
        const parts = (messages.value[key] ?? key).split('|');
        const chosen = count === 1 ? parts[0] : (parts[1] ?? parts[0]);

        return interpolate(chosen, { count, ...replacements });
    }

    /** The Intl BCP-47 tag for the active locale (dates, numbers). */
    const localeTag = computed(() => (locale.value === 'fr' ? 'fr-FR' : 'en-US'));

    return { t, tChoice, locale, localeTag };
}
