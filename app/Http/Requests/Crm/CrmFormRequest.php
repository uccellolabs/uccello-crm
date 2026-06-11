<?php

namespace App\Http\Requests\Crm;

use App\Application\Crm\Services\CustomFields;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Exists;

/**
 * Base request for CRM modules. Provides validation helpers that are shared
 * across every module (companies, contacts, deals, tasks) so tenant-scoping
 * rules are defined once and cannot diverge between modules.
 */
abstract class CrmFormRequest extends FormRequest
{
    /**
     * The CRM entity (morph alias) this request persists, or null when the
     * module has no custom fields. Override in modules that support them.
     */
    protected function customFieldEntity(): ?string
    {
        return null;
    }

    /**
     * Dynamic validation rules for the entity's custom fields.
     *
     * @return array<string, array<int, mixed>>
     */
    protected function customFieldRules(): array
    {
        $entity = $this->customFieldEntity();

        return $entity === null
            ? []
            : app(CustomFields::class)->rules($entity);
    }

    /**
     * Validated static attributes plus normalized custom field values, ready
     * to mass-assign. Custom values are coerced by type and unknown keys are
     * stripped before they reach the jsonb column.
     *
     * @return array<string, mixed>
     */
    public function validatedWithCustomFields(): array
    {
        $entity = $this->customFieldEntity();

        $data = $this->safe()->except('custom_fields');

        if ($entity !== null) {
            $data['custom_fields'] = app(CustomFields::class)
                ->normalize($entity, $this->validated('custom_fields'));
        }

        return $data;
    }

    /**
     * Existence rule constraining a user id to the current team's members,
     * preventing assignment of a record owner from another tenant.
     */
    protected function teamMemberRule(): Exists
    {
        return Rule::exists('team_members', 'user_id')
            ->where('team_id', $this->user()?->current_team_id);
    }

    /**
     * Existence rule constraining a foreign id to a row of the given CRM
     * table that belongs to the current team and is not soft-deleted.
     */
    protected function teamRecordRule(string $table): Exists
    {
        return Rule::exists($table, 'id')
            ->where('team_id', $this->user()?->current_team_id)
            ->whereNull('deleted_at');
    }
}
