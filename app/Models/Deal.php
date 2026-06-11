<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use App\Contracts\HasCrmTimeline;
use App\Domain\Shared\Enums\DealStatus;
use Carbon\CarbonImmutable;
use Database\Factories\DealFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property int $pipeline_id
 * @property int $pipeline_stage_id
 * @property int|null $company_id
 * @property int|null $contact_id
 * @property string $name
 * @property string|null $amount
 * @property string $currency
 * @property DealStatus $status
 * @property Carbon|null $expected_close_date
 * @property CarbonImmutable|null $closed_at
 * @property int $position
 * @property int|null $owner_id
 * @property array<string, mixed>|null $custom_fields
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 * @property-read Pipeline $pipeline
 * @property-read PipelineStage $stage
 * @property-read Company|null $company
 * @property-read Contact|null $contact
 * @property-read User|null $owner
 */
#[Fillable([
    'pipeline_id',
    'pipeline_stage_id',
    'company_id',
    'contact_id',
    'name',
    'amount',
    'currency',
    'status',
    'expected_close_date',
    'closed_at',
    'position',
    'owner_id',
    'custom_fields',
])]
class Deal extends Model implements HasCrmTimeline
{
    use BelongsToTeam;

    /** @use HasFactory<DealFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * The pipeline this deal belongs to.
     *
     * @return BelongsTo<Pipeline, $this>
     */
    public function pipeline(): BelongsTo
    {
        return $this->belongsTo(Pipeline::class);
    }

    /**
     * The stage this deal currently sits in.
     *
     * @return BelongsTo<PipelineStage, $this>
     */
    public function stage(): BelongsTo
    {
        return $this->belongsTo(PipelineStage::class, 'pipeline_stage_id');
    }

    /**
     * The company linked to the deal.
     *
     * @return BelongsTo<Company, $this>
     */
    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
    }

    /**
     * The primary contact linked to the deal.
     *
     * @return BelongsTo<Contact, $this>
     */
    public function contact(): BelongsTo
    {
        return $this->belongsTo(Contact::class);
    }

    /**
     * The user who owns the deal.
     *
     * @return BelongsTo<User, $this>
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Tasks attached to the deal.
     *
     * @return MorphMany<Task, $this>
     */
    public function tasks(): MorphMany
    {
        return $this->morphMany(Task::class, 'taskable');
    }

    /**
     * Activities logged against the deal.
     *
     * @return MorphMany<Activity, $this>
     */
    public function activities(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subjectable');
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'status' => DealStatus::class,
            'expected_close_date' => 'date',
            'closed_at' => 'datetime',
            'custom_fields' => 'array',
        ];
    }
}
