<?php

namespace App\Http\Requests\Crm;

use App\Application\Tasks\Commands\UpdateTaskCommand;
use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateTaskRequest extends CrmFormRequest
{
    /**
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

    public function toCommand(): UpdateTaskCommand
    {
        $data = $this->validated();

        return new UpdateTaskCommand(
            title: $data['title'],
            priority: $data['priority'],
            description: $data['description'] ?? null,
            dueAt: $data['due_at'] ?? null,
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
        );
    }
}
