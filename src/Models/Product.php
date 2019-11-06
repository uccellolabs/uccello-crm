<?php

namespace Uccello\Crm\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Uccello\Core\Database\Eloquent\Model;
use Uccello\Core\Support\Traits\UccelloModule;

class Product extends Model
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'products';

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
        'vtiger_id'
    ];

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';
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