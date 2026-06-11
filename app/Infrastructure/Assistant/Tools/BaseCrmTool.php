<?php

namespace App\Infrastructure\Assistant\Tools;

use App\Infrastructure\Assistant\AssistantTools;
use Laravel\Ai\Contracts\CanActAsTool;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;

/**
 * Base for the CRM agent tools. Each concrete tool delegates to
 * {@see AssistantTools} (the team-scoped data layer) and returns its result as
 * JSON for the model. `filters` arrives as a JSON string so the free-form filter
 * map works identically across providers.
 */
abstract class BaseCrmTool implements CanActAsTool, Tool
{
    public function __construct(protected readonly AssistantTools $tools) {}

    /**
     * Run the named CRM tool with the request arguments and JSON-encode the data.
     */
    protected function run(string $name, Request $request): string
    {
        $input = $request->toArray();

        if (isset($input['filters']) && is_string($input['filters'])) {
            $decoded = json_decode($input['filters'], true);
            $input['filters'] = is_array($decoded) ? $decoded : [];
        }

        $result = $this->tools->execute($name, $input);

        return json_encode($result['data'], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) ?: '{}';
    }
}
