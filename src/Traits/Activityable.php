<?php

namespace Rockbuzz\LaraPricing\Traits;

trait Activityable
{
    public function activities()
    {
        return $this->morphMany(config('pricing.models.activity'), 'activityable');
    }
}
