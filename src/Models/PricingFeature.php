<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class PricingFeature extends Model
{
    use Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'slug',
        'sort_order'
    ];

    protected $casts = [
        'id' => 'string'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function setNameAttribute(string $name): void
    {
        $this->attributes['name'] = $name;
        $this->attributes['slug'] = \Str::slug($name);
    }
}
