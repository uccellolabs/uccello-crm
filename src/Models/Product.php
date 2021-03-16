<?php

namespace Uccello\Crm\Models;

use App\Models\UccelloModel;
use Illuminate\Database\Eloquent\SoftDeletes;
use Uccello\Core\Support\Traits\UccelloModule;
use Uccello\EloquentTree\Contracts\Tree;
use Uccello\EloquentTree\Traits\IsTree;

class Product extends UccelloModel implements Tree
{
    use SoftDeletes;
    use UccelloModule;
    use IsTree;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'crm_products';

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
        'brand',
        'serial_number',
        'family_id',
        'parent_id',
        'vendor_id',
        'vendor_reference',
        'selling_price',
        'purchase_price',
        'margin',
        'delivery_costs',
        'seller_commission',
        'stock_quantity',
        'unit',
        'domain_id',
    ];

    public static function boot()
    {
        parent::boot();

        // Calculate margin
        static::creating(function ($model) {
            $model->margin = $model->selling_price - $model->purchase_price;
        });

        // Linck to parent record
        static::created(function ($model) {
            static::linkToParentRecord($model);
        });

        // Calculate margin
        static::updating(function ($model) {
            $model->margin = $model->selling_price - $model->purchase_price;
        });

        static::updated(function ($model) {
            static::linkToParentRecord($model);
        });
    }

    public static function linkToParentRecord($model)
    {
        // Set parent record
        $parentRecord = Product::find(request('parent'));
        if (!is_null($parentRecord)) {
            with($model)->setChildOf($parentRecord);
        } else {
            // Remove parent domain
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

    public function family()
    {
        return $this->belongsTo(\Uccello\Crm\Models\ProductFamily::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\Uccello\Crm\Models\Account::class);
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'rl_products_documents')->withTimestamps();
    }

    /**
    * Returns record label
    *
    * @return string
    */
    public function getRecordLabelAttribute() : string
    {
        $brand = $this->brand;

        return !empty($brand) ? $this->name.' ('.$brand.')' : $this->name;
    }
}
