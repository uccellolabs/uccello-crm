<?php

namespace App\Infrastructure\Services;

use App\Application\Crm\DTOs\SelectOptionData;
use App\Application\Crm\Services\CrmFormOptions;
use App\Models\Company;
use App\Models\Contact;
use App\Models\Deal;
use App\Models\Pipeline;
use App\Models\PipelineStage;
use App\Models\User;

/**
 * Eloquent-backed {@see CrmFormOptions}: the team-scoped select-option lists
 * used to populate CRM form dropdowns.
 */
class EloquentCrmFormOptions implements CrmFormOptions
{
    public function owners(User $user): array
    {
        return array_values($user->currentTeam->members()->get()
            ->map(fn (User $member) => new SelectOptionData($member->id, $member->name))
            ->all());
    }

    public function companies(): array
    {
        return array_values(Company::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Company $company) => new SelectOptionData($company->id, $company->name))
            ->all());
    }

    public function contacts(): array
    {
        return array_values(Contact::query()->orderBy('last_name')->get(['id', 'first_name', 'last_name'])
            ->map(fn (Contact $contact) => new SelectOptionData($contact->id, $contact->full_name))
            ->all());
    }

    public function deals(): array
    {
        return array_values(Deal::query()->orderBy('name')->get(['id', 'name'])
            ->map(fn (Deal $deal) => new SelectOptionData($deal->id, $deal->name))
            ->all());
    }

    public function pipelinesWithStages(): array
    {
        return array_values(Pipeline::query()
            ->with('stages:id,pipeline_id,name')
            ->orderBy('position')
            ->get(['id', 'name'])
            ->map(fn (Pipeline $pipeline) => [
                'id' => $pipeline->id,
                'name' => $pipeline->name,
                'stages' => $pipeline->stages->map(fn (PipelineStage $stage) => [
                    'value' => $stage->id,
                    'label' => $stage->name,
                ])->values(),
            ])
            ->all());
    }
}
