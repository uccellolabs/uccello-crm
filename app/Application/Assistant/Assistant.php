<?php

namespace App\Application\Assistant;

use App\Models\User;

/**
 * Port for the CRM AI assistant: answers a user's question about their team's
 * CRM data. Implemented in the infrastructure layer (Laravel AI SDK adapter).
 */
interface Assistant
{
    /**
     * Answer the latest user message given the prior conversation.
     *
     * @param  list<array{role: string, content: string}>  $history
     * @return array{reply: string, trace: list<array{name: string, summary: string}>}
     */
    public function ask(array $history, User $user, string $teamName): array;
}
