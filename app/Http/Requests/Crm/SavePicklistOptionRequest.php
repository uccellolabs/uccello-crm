<?php

namespace App\Http\Requests\Crm;

use App\Application\Picklists\Commands\CreatePicklistOptionCommand;
use App\Application\Picklists\Commands\UpdatePicklistOptionCommand;
use App\Domain\Shared\Enums\Picklist;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SavePicklistOptionRequest extends FormRequest
{
    /**
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'picklist' => ['required', Rule::enum(Picklist::class)],
            'label' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
        ];
    }

    public function toCreateCommand(): CreatePicklistOptionCommand
    {
        $data = $this->validated();

        return CreatePicklistOptionCommand::fromForm(
            picklist: Picklist::from($data['picklist']),
            label: $data['label'],
            color: $data['color'] ?? null,
        );
    }

    public function toUpdateCommand(): UpdatePicklistOptionCommand
    {
        $data = $this->validated();

        return new UpdatePicklistOptionCommand(
            label: $data['label'],
            color: $data['color'] ?? null,
        );
    }
}
