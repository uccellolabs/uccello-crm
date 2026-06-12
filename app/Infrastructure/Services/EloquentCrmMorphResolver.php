<?php

namespace App\Infrastructure\Services;

use App\Application\Crm\Services\CrmMorphResolver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;

class EloquentCrmMorphResolver implements CrmMorphResolver
{
    public function resolve(string $type, int $id): Model
    {
        abort_unless(in_array($type, self::MORPH_TYPES, true), 404);

        /** @var class-string<Model>|null $class */
        $class = Relation::getMorphedModel($type);

        abort_if($class === null || ! class_exists($class), 404);

        return $class::query()->findOrFail($id);
    }
}
