<?php

namespace App\Http\Requests\Crm;

use App\Application\Pipelines\Commands\UpdatePipelineStageCommand;
use Illuminate\Foundation\Http\FormRequest;

class UpdatePipelineStageRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function toCommand(): UpdatePipelineStageCommand
    {
        $data = $this->validated();

        return new UpdatePipelineStageCommand(
            name: $data['name'],
            color: $data['color'] ?? null,
            probability: isset($data['probability']) ? (int) $data['probability'] : null,
        );
    }
}
