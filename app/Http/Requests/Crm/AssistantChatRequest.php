<?php

namespace App\Http\Requests\Crm;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class AssistantChatRequest extends FormRequest
{
    /**
     * Always answer this XHR endpoint with a JSON 422 on invalid input — the app
     * only auto-renders JSON errors for `api/*` routes, and the assistant is
     * called via fetch from inside the SPA.
     */
    protected function failedValidation(Validator $validator): void
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Requête invalide.',
            'errors' => $validator->errors(),
        ], 422));
    }

    /**
     * Validation rules for an assistant chat turn.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'messages' => ['required', 'array', 'min:1', 'max:20'],
            'messages.*.role' => ['required', Rule::in(['user'])],
            'messages.*.content' => ['required', 'string', 'max:2000'],
        ];
    }

    /**
     * User messages only — assistant turns from the client are rejected at validation.
     *
     * @return list<array{role: string, content: string}>
     */
    public function history(): array
    {
        /** @var array<int, array{role: string, content: string}> $messages */
        $messages = $this->validated()['messages'];

        return array_map(
            static fn (array $message): array => [
                'role' => 'user',
                'content' => trim($message['content']),
            ],
            array_values($messages),
        );
    }
}
