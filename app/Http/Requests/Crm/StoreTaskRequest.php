<?php

namespace App\Http\Requests\Crm;

use App\Application\Tasks\Commands\CreateTaskCommand;
use App\Application\Crm\Services\Picklists;
use App\Domain\Shared\Enums\Picklist;
use App\Application\Crm\Services\CrmMorphResolver;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreTaskRequest extends CrmFormRequest
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
            'taskable_type' => ['nullable', 'required_with:taskable_id', Rule::in(CrmMorphResolver::MORPH_TYPES)],
            'taskable_id' => ['nullable', 'required_with:taskable_type', 'integer', 'min:1'],
        ];
    }

    public function toCommand(int $createdBy): CreateTaskCommand
    {
        $data = $this->validated();

        return new CreateTaskCommand(
            title: $data['title'],
            priority: $data['priority'],
            createdBy: $createdBy,
            description: $data['description'] ?? null,
            dueAt: $data['due_at'] ?? null,
            assignedTo: isset($data['assigned_to']) ? (int) $data['assigned_to'] : null,
            taskableType: $data['taskable_type'] ?? null,
            taskableId: isset($data['taskable_id']) ? (int) $data['taskable_id'] : null,
        );
    }
}
