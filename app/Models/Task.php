<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use Database\Factories\TaskFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property string $title
 * @property string|null $description
 * @property Carbon|null $due_at
 * @property Carbon|null $completed_at
 * @property string $priority
 * @property int|null $assigned_to
 * @property string|null $taskable_type
 * @property int|null $taskable_id
 * @property int|null $created_by
 * @property array<string, mixed>|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read bool $is_completed
 * @property-read Team $team
 * @property-read User|null $assignee
 * @property-read Model|null $taskable
 */
#[Fillable([
    'title',
    'description',
    'due_at',
    'completed_at',
    'priority',
    'assigned_to',
    'taskable_type',
    'taskable_id',
    'created_by',
    'custom_fields',
])]
class Task extends Model
{
    use BelongsToTeam;

    /** @use HasFactory<TaskFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The record this task is attached to (company, contact, deal).
     *
     * @return MorphTo<Model, $this>
     */
    public function taskable(): MorphTo
    {
        return $this->morphTo();
    }

    /**
     * The user the task is assigned to.
     *
     * @return BelongsTo<User, $this>
     */
    public function assignee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    /**
     * Whether the task has been completed.
     */
    public function isCompleted(): bool
    {
        return $this->completed_at !== null;
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'due_at' => 'datetime',
            'completed_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }
}
