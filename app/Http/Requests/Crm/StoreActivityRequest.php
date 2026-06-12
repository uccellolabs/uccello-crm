<?php

namespace App\Http\Requests\Crm;

use App\Application\Activities\Commands\CreateActivityCommand;
use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Application\Crm\Services\CrmMorphResolver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends CrmFormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(app(Picklists::class)->values(Picklist::ActivityType))],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
            'subjectable_type' => ['required', Rule::in(CrmMorphResolver::MORPH_TYPES)],
            'subjectable_id' => ['required', 'integer', 'min:1'],
        ];
    }

    public function toCommand(int $userId): CreateActivityCommand
    {
        $data = $this->validated();

        return new CreateActivityCommand(
            type: $data['type'],
            subjectableType: $data['subjectable_type'],
            subjectableId: (int) $data['subjectable_id'],
            userId: $userId,
            subject: $data['subject'] ?? null,
            body: $data['body'] ?? null,
            occurredAt: $data['occurred_at'] ?? null,
        );
    }
}
