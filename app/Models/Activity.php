<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use Database\Factories\ActivityFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property string $type
 * @property string|null $subject
 * @property string|null $body
 * @property Carbon $occurred_at
 * @property string $subjectable_type
 * @property int $subjectable_id
 * @property int|null $user_id
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property-read Team $team
 * @property-read User|null $user
 * @property-read Model $subjectable
 */
#[Fillable([
    'type',
    'subject',
    'body',
    'occurred_at',
    'subjectable_type',
    'subjectable_id',
    'user_id',
])]
class Activity extends Model
{
    use BelongsToTeam;

    /** @use HasFactory<ActivityFactory> */
    use HasFactory;

    /**
     * The record this activity relates to (company, contact, deal).
     *
     * @return MorphTo<Model, $this>
     */
    public function subjectable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user who logged the activity.
     *
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'occurred_at' => 'datetime',
        ];
    }
}
