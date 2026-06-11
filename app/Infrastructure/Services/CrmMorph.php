<?php

namespace App\Infrastructure\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

/**
 * Resolves the polymorphic parent of a task/activity from a morph alias and id.
 *
 * Resolution goes through the model's query builder, so the `BelongsToTeam`
 * global scope guarantees a record from another team is never returned
 * (it 404s instead) — the morph attach is tenant-safe by construction.
 */
class CrmMorph
{
    /**
     * Morph aliases that can carry tasks and activities.
     *
     * @var list<string>
     */
    public const TYPES = ['company', 'contact', 'deal'];

    /**
     * Resolve a CRM record from its morph alias and id, scoped to the team.
     */
    public static function resolve(string $type, int $id): Model
    {
        abort_unless(in_array($type, self::TYPES, true), 404);

        /** @var class-string<Model>|null $class */
        $class = Relation::getMorphedModel($type);

        // Guard against an alias that is mapped but whose model is not yet
        // shipped — never let it fatal, return a clean 404 instead.
        abort_if($class === null || ! class_exists($class), 404);

        return $class::query()->findOrFail($id);
    }
}
