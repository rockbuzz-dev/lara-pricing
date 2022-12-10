<?php

namespace Rockbuzz\LaraPricing\Models;

use Spatie\Sluggable\HasSlug;
use Spatie\Sluggable\SlugOptions;
use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class Feature extends Model
{
    use Uuid, SoftDeletes, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'order_column'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('pricing.tables.features'));
    }

    public function getSlugOptions(): SlugOptions
    {
        return SlugOptions::create()
            ->generateSlugsFrom('name')
            ->saveSlugsTo('slug');
    }
}
