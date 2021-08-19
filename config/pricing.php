<?php

use Rockbuzz\LaraPricing\Models\{Plan, Feature, Subscription, SubscriptionUsage, PricingActivity};

return [
    'models' => [
        'plan' => Plan::class,
        'feature' => Feature::class,
        'subscription' => Subscription::class,
        'subscription_usage' => SubscriptionUsage::class,
        'activity' => PricingActivity::class
    ],
    'positive_values' => ['Y', 'OK', 'TRUE']
];
