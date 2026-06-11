<?php

namespace App\Models;

use App\Concerns\BelongsToTeam;
use App\Domain\Shared\Enums\CustomFieldType;
use Database\Factories\CustomFieldDefinitionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property int $team_id
 * @property string $entity_type
 * @property string $key
 * @property string $label
 * @property CustomFieldType $type
 * @property array<string, mixed>|null $options
 * @property bool $is_required
 * @property bool $is_filterable
 * @property int $position
 * @property string|null $help_text
 * @property Carbon|null $created_at
 * @property Carbon|null $updated_at
 * @property Carbon|null $deleted_at
 */
#[Fillable([
    'entity_type',
    'key',
    'label',
    'type',
    'options',
    'is_required',
    'is_filterable',
    'position',
    'help_text',
])]
class CustomFieldDefinition extends Model
{
    use BelongsToTeam;

    /** @use HasFactory<CustomFieldDefinitionFactory> */
    use HasFactory;

    use SoftDeletes;

    /**
     * Scope the query to a given CRM entity type.
     *
     * @param  Builder<CustomFieldDefinition>  $query
     */
    public function scopeForEntity(Builder $query, string $entityType): void
    {
        $query->where('entity_type', $entityType)->orderBy('position');
    }

    /**
     * The choices for a select/multiselect field.
     *
     * @return array<int, array{value: string, label: string}>
     */
    public function choices(): array
    {
        return $this->options['choices'] ?? [];
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'type' => CustomFieldType::class,
            'options' => 'array',
            'is_required' => 'boolean',
            'is_filterable' => 'boolean',
        ];
    }
}
