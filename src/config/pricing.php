<?php

use Rockbuzz\LaraPricing\Models\{Plan, Feature, Subscription, SubscriptionUsage, PricingActivity};

return [
    'tables' => [
        'plans' => 'plans',
        'features' => 'features',
        'feature_plan' => 'feature_plan',
        'subscriptions' => 'subscriptions',
        'subscription_usages' => 'subscription_usages',
        'pricing_activities' => 'pricing_activities'
    ],
    'models' => [
        'plan' => Plan::class,
        'feature' => Feature::class,
        'subscription' => Subscription::class,
        'subscription_usage' => SubscriptionUsage::class,
        'activity' => PricingActivity::class
    ],
    'positive_values' => ['Y', 'OK', 'TRUE']
];
