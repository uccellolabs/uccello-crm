<?php

namespace App\Http\Requests\Crm;

use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Infrastructure\Services\CrmMorph;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreActivityRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'type' => ['required', Rule::in(app(Picklists::class)->values(Picklist::ActivityType))],
            'subject' => ['nullable', 'string', 'max:255'],
            'body' => ['nullable', 'string', 'max:5000'],
            'occurred_at' => ['nullable', 'date'],
            'subjectable_type' => ['required', Rule::in(CrmMorph::TYPES)],
            'subjectable_id' => ['required', 'integer', 'min:1'],
        ];
    }
}
