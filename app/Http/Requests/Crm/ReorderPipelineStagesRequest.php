<?php

namespace App\Http\Requests\Crm;

use App\Application\Shared\Commands\ReorderIdsCommand;
use Illuminate\Foundation\Http\FormRequest;

class ReorderPipelineStagesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array', 'max:100'],
            'ids.*' => ['integer'],
        ];
    }

    public function toCommand(): ReorderIdsCommand
    {
        return new ReorderIdsCommand(
            ids: array_values($this->collect('ids')->map(fn ($id) => (int) $id)->all()),
        );
    }
}
