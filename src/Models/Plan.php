<?php

namespace Rockbuzz\LaraPricing\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Plan extends Model
{
    use Uuid, SoftDeletes, HasSlug;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'interval',
        'period',
        'trial_period_days',
        'sort_order'
    ];

    protected $casts = [
        'id' => 'string',
        'price' => 'integer',
        'period' => 'integer',
        'trial_period_days' => 'integer',
        'sort_order' => 'integer'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('pricing.tables.plans'));
    }

    public function features()
    {
        return $this->belongsToMany(
            config('pricing.models.feature'),
            config('pricing.models.feature_plan'),
            'plan_id',
            'feature_id'
        )->withPivot('value');
    }

    public function hasFeature(string $featureSlug)
    {
        return $this->features()->where('slug', $featureSlug)->exists();
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }

    public function scopeMonthly($query)
    {
        return $query->where('interval', 'month')->where('period', 1);
    }

    public function scopeYearly($query)
    {
        return $query->where('interval', 'month')->where('period', 12);
    }
}
