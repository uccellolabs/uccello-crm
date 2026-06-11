<?php

namespace App\Http\Requests\Crm;

use Illuminate\Foundation\Http\FormRequest;

class ReorderPipelineStagesRequest extends FormRequest
{
    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'ids' => ['required', 'array'],
            'ids.*' => ['integer'],
        ];
    }

    /** @return list<int> */
    public function orderedIds(): array
    {
        return array_values($this->collect('ids')->map(fn ($id) => (int) $id)->all());
    }
}
