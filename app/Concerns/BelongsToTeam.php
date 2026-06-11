<?php

namespace App\Concerns;

use App\Models\Team;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

/**
 * Scopes a model to the authenticated user's current team.
 *
 * Tenancy backbone of the CRM:
 *  - When a user is authenticated, EVERY query is constrained to that user's
 *    current team — and it FAILS CLOSED: if the user somehow has no current
 *    team, the scope constrains to an impossible id (0) so no cross-tenant
 *    rows can ever leak. An authenticated user can therefore never read or
 *    bind another team's records (implicit route-model binding 404s them).
 *  - When there is NO authenticated user (console, seeders, queue workers,
 *    tinker), the scope steps aside so those contexts can manage data across
 *    teams. Such callers MUST set `team_id` explicitly (or use
 *    `withoutGlobalScope('team')`); there is no CRM HTTP route reachable without auth.
 *
 * `team_id` is auto-filled from the current team on creation for authed users.
 */
trait BelongsToTeam
{
    public static function bootBelongsToTeam(): void
    {
        static::creating(function ($model): void {
            if (empty($model->team_id) && ($teamId = Auth::user()?->current_team_id) !== null) {
                $model->team_id = $teamId;
            }
        });

        static::addGlobalScope('team', function (Builder $builder): void {
            $user = Auth::user();

            // No authenticated user → console/seeder/queue context; leave
            // unscoped so those callers can operate across teams explicitly.
            if ($user === null) {
                return;
            }

            // Authenticated → fail closed to the current team (impossible id
            // when none is set, never "all teams").
            $builder->where(
                $builder->getModel()->getTable().'.team_id',
                $user->current_team_id ?? 0,
            );
        });
    }

    /**
     * The team that owns this record.
     *
     * @return BelongsTo<Team, $this>
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
