<?php

namespace Uccello\Crm\Models;

use Gzero\EloquentTree\Model\Tree;
use Illuminate\Database\Eloquent\SoftDeletes;
use Uccello\Core\Support\Traits\UccelloModule;

class ProductFamily extends Tree
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_product_families';

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['deleted_at'];

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'parent_id',
        'color',
        'domain_id'
    ];

    public static function boot()
    {
        parent::boot();

        // Linck to parent record
        static::created(function ($model) {
            static::linkToParentRecord($model);
        });

        static::updated(function ($model) {
            static::linkToParentRecord($model);
        });
    }

    public static function linkToParentRecord($model)
    {
        // Set parent record
        $parentRecord = ProductFamily::find(request('parent'));
        if (!is_null($parentRecord)) {
            with($model)->setChildOf($parentRecord);
        }
        // Remove parent domain
        else {
            with($model)->setAsRoot();
        }
    }

    /**
     * Check if node is root
     * This function check foreign key field
     *
     * @return bool
     */
    public function isRoot()
    {
        // return (empty($this->{$this->getTreeColumn('parent')})) ? true : false;
        return $this->{$this->getTreeColumn('path')} === $this->getKey() . '/'
                && $this->{$this->getTreeColumn('level')} === 0;
    }

    /**
    * Returns record label
    *
    * @return string
    */
    public function getRecordLabelAttribute() : string
    {
        return $this->name;
    }
}