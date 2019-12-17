<?php

namespace Uccello\Crm\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Uccello\Core\Database\Eloquent\Model;
use Uccello\Core\Support\Traits\UccelloModule;

class Address extends Model implements Searchable
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'addresses';

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
        'label',
        'account_id',
        'type',
        'address_1',
        'address_2',
        'address_3',
        'postal_code',
        'city',
        'country_id',
        'gln_code',
        'domain_id',
    ];

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';
    }

    public $searchableType = 'address';

    public $searchableColumns = [
        'address_1',
        'address_2',
        'address_3',
    ];

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->recordLabel
        );
    }

    public function country()
    {
        return $this->belongsTo(\Uccello\Country\Models\Country::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }

    /**
    * Returns record label
    *
    * @return string
    */
    public function getRecordLabelAttribute() : string
    {
        return $this->label;
    }
}