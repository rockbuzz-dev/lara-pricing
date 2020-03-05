<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;

class PricingActivity extends Model
{
    use Uuid;

    public $incrementing = false;

    public $timestamps = false;

    protected $keyType = 'string';

    protected $fillable = [
        'description',
        'changes',
        'activityable_id',
        'activityable_type',
        'causeable_id',
        'causeable_type'
    ];

    protected $casts = [
        'id' => 'string',
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
