<?php

namespace App\Application\Crm\Services;

use App\Models\CustomFieldDefinition;
use Illuminate\Support\Collection;

/**
 * Port for team-defined custom fields: loads definitions for a CRM entity,
 * derives validation rules, normalizes submitted values, and serializes
 * definitions for the frontend renderer. Implemented in the infrastructure
 * layer (Eloquent).
 */
interface CustomFields
{
    /**
     * The team's custom field definitions for an entity, ordered by position.
     *
     * @return Collection<int, CustomFieldDefinition>
     */
    public function definitions(string $entityType): Collection;

    /**
     * Validation rules for the `custom_fields` payload of an entity.
     *
     * @return array<string, array<int, mixed>>
     */
    public function rules(string $entityType): array;

    /**
     * Normalize submitted values by type, keeping only defined keys.
     *
     * @param  array<string, mixed>|null  $input
     * @return array<string, mixed>
     */
    public function normalize(string $entityType, ?array $input): array;

    /**
     * Serialize the definitions of an entity for the frontend renderer.
     *
     * @return list<array<string, mixed>>
     */
    public function forFrontend(string $entityType): array;
}
