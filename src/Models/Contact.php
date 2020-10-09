<?php

namespace Uccello\Crm\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Uccello\Core\Database\Eloquent\Model;
use Uccello\Core\Support\Traits\UccelloModule;
use Uccello\Country\Models\Country;

class Contact extends Model implements Searchable
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'contacts';

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
        'civility',
        'first_name',
        'last_name',
        'account_id',
        'phone',
        'mobile',
        'email',
        'function',
        'service',
        'address_id',
        'country_id',
        'description',
        'assigned_user_id',
        'domain_id',
    ];

    public $searchableType = 'contact';

    public $searchableColumns = [
        'first_name',
        'last_name'
    ];

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->recordLabel
        );
    }

    protected function initTablePrefix()
    {
        $this->tablePrefix = 'crm_';
    }

    public function assigned_user()
    {
        return $this->belongsTo(\Uccello\Core\Models\User::class);
    }

    public function account()
    {
        return $this->belongsTo(Account::class);
    }
    
    public function address()
    {
        return $this->belongsTo(Address::class);
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'rl_contacts_documents')->withTimestamps();
    }

    /**
    * Returns record label
    *
    * @return string
    */
    public function getRecordLabelAttribute() : string
    {
        return trim($this->first_name.' '.$this->last_name);
    }
}
