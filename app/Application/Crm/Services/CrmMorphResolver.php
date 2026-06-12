<?php

namespace App\Application\Crm\Services;

use Illuminate\Database\Eloquent\Model;

/**
 * Resolves the polymorphic parent of a task/activity from a morph alias and id.
 */
interface CrmMorphResolver
{
    /** @var list<string> */
    public const MORPH_TYPES = ['company', 'contact', 'deal'];

    public function resolve(string $type, int $id): Model;
}
