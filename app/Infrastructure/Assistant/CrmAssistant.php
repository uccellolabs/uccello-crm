<?php

namespace App\Infrastructure\Assistant;

use App\Application\Assistant\AssistantUnavailableException;
use App\Application\Assistant\Assistant;
use App\Infrastructure\Assistant\Tools\AggregateRecordsTool;
use App\Infrastructure\Assistant\Tools\GetRecordTool;
use App\Infrastructure\Assistant\Tools\SearchRecordsTool;
use App\Models\User;
use Illuminate\Support\Carbon;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Messages\AssistantMessage;
use Laravel\Ai\Messages\UserMessage;
use Throwable;

use function Laravel\Ai\agent;

/**
 * Drives the CRM assistant via the Laravel AI SDK: it builds the system prompt
 * from the live team schema and a provider-agnostic agent with the CRM tools,
 * then lets the SDK run the tool-calling loop. The default provider is OpenAI
 * (config `ai.default`); swapping providers is a config change, not a code one.
 */
class CrmAssistant implements Assistant
{
    public function __construct(private readonly AssistantTools $tools) {}

    /**
     * Answer the latest user message given the prior conversation.
     *
     * @param  list<array{role: string, content: string}>  $history
     * @return array{reply: string, trace: list<array{name: string, summary: string}>}
     */
    public function ask(array $history, User $user, string $teamName): array
    {
        $this->ensureConfigured();

        $context = $history === [] ? [] : array_slice($history, 0, count($history) - 1);
        $prompt = $history === [] ? '' : $history[count($history) - 1]['content'];

        $messages = array_map(
            static fn (array $turn): UserMessage|AssistantMessage => $turn['role'] === 'assistant'
                ? new AssistantMessage($turn['content'])
                : new UserMessage($turn['content']),
            $context,
        );

        try {
            $response = agent($this->systemPrompt($user, $teamName), $messages, $this->toolset())
                ->prompt($prompt);
        } catch (Throwable $e) {
            throw new AssistantUnavailableException(
                'L\'assistant est momentanément indisponible. Réessayez dans un instant.',
                previous: $e,
            );
        }

        return ['reply' => $response->text, 'trace' => $this->tools->takeActivity()];
    }

    /**
     * The CRM tools the agent may call.
     *
     * @return list<Tool>
     */
    private function toolset(): array
    {
        return [
            new SearchRecordsTool($this->tools),
            new GetRecordTool($this->tools),
            new AggregateRecordsTool($this->tools),
        ];
    }

    /**
     * Fail fast with a friendly message when the active provider has no API key.
     */
    private function ensureConfigured(): void
    {
        $provider = (string) config('ai.default', 'openai');

        if (blank(config("ai.providers.{$provider}.key"))) {
            throw new AssistantUnavailableException(
                'L\'assistant IA n\'est pas encore configuré. Ajoutez une clé '.strtoupper($provider).'_API_KEY pour l\'activer.',
            );
        }
    }

    private function systemPrompt(User $user, string $teamName): string
    {
        $today = Carbon::now()->format('d/m/Y');

        return <<<PROMPT
            Tu es l'assistant IA intégré au CRM Uccello. Tu aides l'utilisateur **{$user->name}** de l'équipe **{$teamName}** à exploiter les données de son CRM.

            Nous sommes le {$today} (format jj/mm/aaaa).

            ## Règles
            - Réponds toujours en français, de façon claire et concise.
            - Fonde TES RÉPONSES UNIQUEMENT sur les données obtenues via les outils. N'invente jamais d'enregistrement, de chiffre ou de champ.
            - Prends toujours en compte les **champs personnalisés** (custom fields) listés ci-dessous : tu peux les utiliser pour filtrer, regrouper et les citer dans tes réponses (utilise leur clé pour filtrer, leur libellé pour répondre).
            - Pour filtrer, passe l'argument `filters` sous forme de JSON, par ex. `{"city":"Paris"}` ou `{"segment":"ent"}`.
            - Appelle plusieurs outils si nécessaire avant de répondre. Pour une question chiffrée (totaux, comptes), privilégie `aggregate_records`.
            - Si une recherche ne renvoie rien, dis-le simplement et propose une piste (autre orthographe, autre module).
            - Mets en forme les listes et les montants de manière lisible (Markdown léger autorisé : listes, gras). Cite les identifiants quand c'est utile.
            - Toutes les données sont déjà limitées à l'équipe de l'utilisateur ; ne mentionne pas d'autres équipes.

            {$this->tools->schema()}
            PROMPT;
    }
}
