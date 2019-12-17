<?php

namespace Uccello\Crm\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Searchable\Searchable;
use Spatie\Searchable\SearchResult;
use Uccello\Core\Database\Eloquent\Model;
use Uccello\Core\Support\Traits\UccelloModule;

class Opportunity extends Model implements Searchable
{
    use SoftDeletes;
    use UccelloModule;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'opportunities';

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
        'account_id',
        'account_name',
        'type',
        'type_other',
        'origin',
        'business_provider_id',
        'phase',
        'step',
        'closing_date',
        'assigned_user_id',
        'amount',
        'description',
        'domain_id',
    ];

   public $searchableType = 'opportunity';

    public $searchableColumns = [
        'name',
        'account_name'
    ];

    public function getSearchResult(): SearchResult
    {
        return new SearchResult(
            $this,
            $this->recordLabel . ' - '.$this->account_name
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
            static::changeOpportunityPhaseAndStep($model);
        });

        static::updating(function ($model) {
            static::changeOpportunityPhaseAndStep($model);
        });
    }

    public static function changeOpportunityPhaseAndStep($model)
    {
        // Change step according to phase
        if ($model->phase === 'phase.5_won' && is_null($model->step)) {
            $model->step = 'step.won';
        } elseif ($model->phase === 'phase.6_lost' && is_null($model->step)) {
            $model->step = 'step.lost';
        } elseif (empty($model->step)) {
            $model->step = 'step.qualification';
        }
        // Change phase according to step
        elseif ($model->step === 'step.won' && $model->phase !== 'phase.5_won') {
            $model->phase = 'phase.5_won';
        }
        elseif ($model->step === 'step.lost' && $model->phase !== 'phase.6_lost') {
            $model->phase = 'phase.6_lost';
        }

        // Add account name
        if ($model->account) {
            $model->account_name = $model->account->name;
        }
    }

    public function account()
    {
        return $this->belongsTo(\Uccello\Crm\Models\Account::class);
    }

    public function product()
    {
        return $this->belongsTo(\Uccello\Crm\Models\Product::class);
    }

    public function assigned_user()
    {
        return $this->belongsTo(\App\User::class);
    }

    public function documents()
    {
        return $this->belongsToMany(Document::class, 'rl_opportunities_documents')->withTimestamps();
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