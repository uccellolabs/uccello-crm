<?php

namespace App\Concerns;

use App\Application\Crm\DTOs\SelectOptionData;
use App\Application\Crm\Services\CrmFormOptions;
use Illuminate\Http\Request;

/**
 * Shared serialization helpers for CRM record controllers (companies,
 * contacts, deals): the activity timeline, the attached task list, and the
 * team-member option list used by owner/assignee selects.
 */
trait InteractsWithCrmRecords
{
    /**
     * The current team's members as select options.
     *
     * @return list<array{value: int|string, label: string}>
     */
    protected function teamMembers(Request $request): array
    {
        return array_map(
            fn (SelectOptionData $option) => $option->toArray(),
            app(CrmFormOptions::class)->owners($request->user()),
        );
    }
}
