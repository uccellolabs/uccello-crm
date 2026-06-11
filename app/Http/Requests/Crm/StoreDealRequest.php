<?php

namespace App\Http\Requests\Crm;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class StoreDealRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'amount' => ['nullable', 'numeric', 'min:0', 'max:999999999999'],
            'currency' => ['nullable', 'string', 'size:3'],
            'pipeline_id' => ['required', 'integer', $this->teamRecordRule('pipelines')],
            'pipeline_stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')
                    ->where('team_id', $this->user()?->current_team_id)
                    ->where('pipeline_id', $this->input('pipeline_id')),
            ],
            'company_id' => ['nullable', 'integer', $this->teamRecordRule('companies')],
            'contact_id' => ['nullable', 'integer', $this->teamRecordRule('contacts')],
            'expected_close_date' => ['nullable', 'date'],
            'owner_id' => ['nullable', 'integer', $this->teamMemberRule()],
            ...$this->customFieldRules(),
        ];
    }

    protected function customFieldEntity(): ?string
    {
        return 'deal';
    }
}
