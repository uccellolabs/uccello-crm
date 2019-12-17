<?php

namespace Uccello\Crm\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Uccello\Core\Database\Eloquent\Model;
use Uccello\Core\Support\Traits\UccelloModule;
use Uccello\Country\Models\Country;

class Account extends Model implements Searchable
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'accounts';

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
        'title',
        'name',
        'code',
        'type',
        'lead_status',
        'email',
        'phone',
        'fax',
        'website',
        'origin',
        'origin_other',
        'business_sector',
        'naf_code',
        'siret',
        'vat_intra',
        'bic',
        'iban',
        'payment_mode',
        'payment_validity',
        'employees',
        'description',
        'assigned_user_id',
        'domain_id',
    ];

    public $searchableType = 'account';

    public $searchableColumns = [
        'name'
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

    public static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            static::changeLeadStatus($model);
        });

        static::updating(function ($model) {
            static::changeLeadStatus($model);
        });
    }

    public static function changeLeadStatus($model)
    {
        // Change lead_status according to type
        if ($model->type === 'type.prospect' && is_null($model->lead_status)) {
            $model->lead_status = 'status.new';
        }
        elseif ($model->type === 'type.lead' && is_null($model->lead_status)) {
            $model->lead_status = 'status.contacted';
        }
        // Change type according to lead_status
        elseif ($model->lead_status === 'status.new' && $model->type === 'type.lead') {
            $model->type = 'type.prospect';
        }
        elseif ($model->lead_status !== 'status.new' && $model->type === 'type.prospect') {
            $model->type = 'type.lead';
        }
    }

    public function contacts()
    {
        return $this->hasMany(Contact::class);
    }

    public function opportunities()
    {
        return $this->hasMany(Opportunity::class);
    }

    public function addresses()
    {
        return $this->hasMany(Address::class);
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'rl_accounts_documents')->withTimestamps();
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