<?php

namespace App\Http\Requests\Crm;

use App\Application\Pipelines\Commands\CreatePipelineStageCommand;
use Illuminate\Foundation\Http\FormRequest;

class StorePipelineStageRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'pipeline_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:255'],
            'color' => ['nullable', 'string', 'regex:/^#[0-9a-fA-F]{6}$/'],
            'probability' => ['nullable', 'integer', 'min:0', 'max:100'],
        ];
    }

    public function toCommand(): CreatePipelineStageCommand
    {
        $data = $this->validated();

        return new CreatePipelineStageCommand(
            pipelineId: (int) $data['pipeline_id'],
            name: $data['name'],
            color: $data['color'] ?? null,
            probability: isset($data['probability']) ? (int) $data['probability'] : null,
        );
    }
}
