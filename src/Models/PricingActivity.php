<?php

namespace Rockbuzz\LaraPricing\Models;

use Illuminate\Database\Eloquent\Model;

class PricingActivity extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'description',
        'changes',
        'activityable_id',
        'activityable_type',
        'causeable_id',
        'causeable_type'
    ];

    protected $casts = [
        'changes' => 'array',
        'created_at' => 'datetime'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('pricing.tables.pricing_activities'));
    }

    public function causer()
    {
        return $this->morphTo('causeable');
    }
}
