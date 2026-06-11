<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int $pipeline_id
 * @property string $name
 * @property string $key
 * @property string|null $color
 * @property int $position
 * @property bool $is_won
 * @property bool $is_lost
 * @property int|null $probability
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Pipeline $pipeline
 */
#[Fillable([
    'pipeline_id',
    'name',
    'key',
    'color',
    'position',
    'is_won',
    'is_lost',
    'probability',
])]
class PipelineStage extends Model
{
    use BelongsToTeam;

    /**
     * The pipeline this stage belongs to.
     *
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * The deals currently in this stage.
     *
     * @return HasMany<Deal, $this>
     */
    public function deals(): HasMany
    {
        return $this->hasMany(Deal::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_won' => 'boolean',
            'is_lost' => 'boolean',
        ];
    }
}
