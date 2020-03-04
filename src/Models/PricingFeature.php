<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};
use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;

class PricingFeature extends Model
{
    use Uuid, SoftDeletes, HasSlug;

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

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
