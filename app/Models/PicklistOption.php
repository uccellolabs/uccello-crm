<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property string $picklist
 * @property string $value
 * @property string $label
 * @property string|null $color
 * @property int $position
 * @property bool $is_system
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 */
#[Fillable(['picklist', 'value', 'label', 'color', 'position', 'is_system'])]
class PicklistOption extends Model
{
    use BelongsToTeam;

    /**
     * Scope the query to a given picklist, ordered by position.
     *
     * @param  Builder<PicklistOption>  $query
     */
    public function scopeForList(Builder $query, string $picklist): void
    {
        $query->where('picklist', $picklist)->orderBy('position');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
        ];
    }
}
