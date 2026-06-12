<?php

namespace App\Http\Requests\Crm;

use App\Application\Deals\Commands\UpdateDealCommand;
use App\Domain\Shared\Enums\DealStatus;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class UpdateDealRequest extends CrmFormRequest
{
    /**
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

    public function toCommand(): UpdateDealCommand
    {
        $data = $this->validatedWithCustomFields();

        return new UpdateDealCommand(
            name: $data['name'],
            pipelineId: (int) $data['pipeline_id'],
            pipelineStageId: (int) $data['pipeline_stage_id'],
            status: DealStatus::Open,
            closedAt: null,
            amount: isset($data['amount']) ? (float) $data['amount'] : null,
            currency: $data['currency'] ?? null,
            companyId: isset($data['company_id']) ? (int) $data['company_id'] : null,
            contactId: isset($data['contact_id']) ? (int) $data['contact_id'] : null,
            expectedCloseDate: $data['expected_close_date'] ?? null,
            ownerId: isset($data['owner_id']) ? (int) $data['owner_id'] : null,
            customFields: $data['custom_fields'] ?? null,
        );
    }
}
