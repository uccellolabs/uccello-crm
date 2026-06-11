<?php

namespace App\Http\Requests\Crm;

use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Infrastructure\Services\CrmMorph;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:5000'],
            'due_at' => ['nullable', 'date'],
            'priority' => ['required', Rule::in(app(Picklists::class)->values(Picklist::TaskPriority))],
            'assigned_to' => ['nullable', 'integer', $this->teamMemberRule()],
            'taskable_type' => ['nullable', 'required_with:taskable_id', Rule::in(CrmMorph::TYPES)],
            'taskable_id' => ['nullable', 'required_with:taskable_type', 'integer', 'min:1'],
        ];
    }
}
