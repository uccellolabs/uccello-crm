<?php

namespace App\Http\Controllers\Crm;

use App\Application\Assistant\Assistant;
use App\Http\Controllers\Controller;
use App\Http\Requests\Crm\AssistantChatRequest;
use App\Infrastructure\Assistant\AssistantUnavailableException;
use App\Models\Assistant as AssistantResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Gate;

class AssistantController extends Controller
{
    /**
     * Answer a CRM question with the AI assistant.
     */
    public function chat(AssistantChatRequest $request, Assistant $assistant): JsonResponse
    {
        Gate::authorize('chat', AssistantResource::class);

        $user = $request->user();
        $team = $user->currentTeam;

        try {
            $result = $assistant->ask($request->history(), $user, $team->name);
        } catch (AssistantUnavailableException $e) {
            return response()->json([
                'reply' => $e->getMessage(),
                'trace' => [],
                'unavailable' => true,
            ]);
        }

        return response()->json([
            'reply' => $result['reply'],
            'trace' => $result['trace'],
            'unavailable' => false,
        ]);
    }
}
