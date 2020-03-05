<?php

namespace Tests;

use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraPricing\Models\{PricingActivity, SubscriptionUsage};

class ActivityableTest extends TestCase
{
    public function testSubscriptionUsageCanHaveActivities()
    {
        $subscriptionUsage = $this->create(SubscriptionUsage::class);

        $activity = $this->create(PricingActivity::class, [
            'activityable_id' => $subscriptionUsage->id,
            'activityable_type' => SubscriptionUsage::class
        ]);

        $this->assertInstanceOf(MorphMany::class, $subscriptionUsage->activities());
        $this->assertContains($activity->id, $subscriptionUsage->activities->pluck('id'));
    }
}
