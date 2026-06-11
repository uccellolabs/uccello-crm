<?php

namespace App\Http\Requests\Crm;

use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * Editing a task does not move its polymorphic parent — only its own
     * attributes change.
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
        ];
    }
}
