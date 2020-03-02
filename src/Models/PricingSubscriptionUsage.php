<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraPricing\Traits\{Activityable, Uuid};
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class PricingSubscriptionUsage extends Model
{
    use Uuid, SoftDeletes, Activityable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'used',
        'subscription_id',
        'feature_id',
        'metadata'
    ];

    protected $casts = [
        'id' => 'string',
        'metadata' => 'array'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];
}
