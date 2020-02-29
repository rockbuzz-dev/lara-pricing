<?php

namespace Rockbuzz\LaraPricing\Traits;

use Rockbuzz\LaraPricing\Models\PricingActivity;

trait Activityable
{
    public function activities()
    {
        return $this->morphMany(PricingActivity::class, 'activityable');
    }
}
