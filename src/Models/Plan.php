<?php

namespace Rockbuzz\LaraPricing\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Plan extends Model
{
    use Uuid, SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'interval',
        'period',
        'trial_period_days',
        'order_column'
    ];

    protected $casts = [
        'price' => 'integer',
        'period' => 'integer',
        'trial_period_days' => 'integer',
        'order_column' => 'integer'
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
