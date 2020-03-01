<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Plan extends Model
{
    use Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
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

    public function features()
    {
        return $this->belongsToMany(Feature::class)
            ->withPivot('value');
    }

    public function hasFeature(string $featureSlug)
    {
        return $this->features()->where('slug', $featureSlug)->exists();
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
