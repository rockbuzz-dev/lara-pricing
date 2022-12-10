<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Rockbuzz\LaraPricing\Traits\Activityable;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class SubscriptionUsage extends Model
{
    use Uuid, SoftDeletes, Activityable;

    protected $fillable = [
        'used',
        'subscription_id',
        'feature_id',
        'metadata'
    ];

    protected $casts = [
        'metadata' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('pricing.tables.pricing_subscription_usages'));
    }
}
