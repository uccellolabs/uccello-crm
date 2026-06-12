<?php

namespace App\Http\Requests\Crm;

use App\Application\Deals\Commands\MoveDealCommand;
use App\Models\Deal;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Validation\Rule;

class MoveDealRequest extends CrmFormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        /** @var Deal $deal */
        $deal = $this->route('deal');

        return [
            // The target stage must belong to the same pipeline as the deal,
            // within the current team.
            'stage_id' => [
                'required',
                'integer',
                Rule::exists('pipeline_stages', 'id')
                    ->where('team_id', $this->user()?->current_team_id)
                    ->where('pipeline_id', $deal->pipeline_id),
            ],
            'position' => ['required', 'integer', 'min:0'],
        ];
    }

    public function toCommand(): MoveDealCommand
    {
        return new MoveDealCommand(
            stageId: (int) $this->validated('stage_id'),
            position: (int) $this->validated('position'),
        );
    }
}
