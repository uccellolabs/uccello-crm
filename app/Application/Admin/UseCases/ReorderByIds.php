<?php

namespace App\Application\Admin\UseCases;

use Illuminate\Database\Eloquent\Model;

class ReorderByIds
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  list<int>  $ids
     */
    public function handle(string $modelClass, array $ids): void
    {
        $modelClass::query()
            ->whereIn('id', $ids)
            ->get()
            ->each(fn (Model $model) => $model->update([
                'position' => array_search($model->getKey(), $ids, true),
            ]));
    }
}
