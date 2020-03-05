<?php

use Rockbuzz\LaraPricing\Models\{PricingPlan,
    PricingFeature,
    PricingSubscription,
    PricingSubscriptionUsage,
    PricingActivity};

return [
    'tables' => [
        'pricing_plans' => 'pricing_plans',
        'pricing_features' => 'pricing_features',
        'pricing_feature_plan' => 'pricing_feature_plan',
        'pricing_subscriptions' => 'pricing_subscriptions',
        'pricing_subscription_usages' => 'pricing_subscription_usages',
        'pricing_activities' => 'pricing_activities'
    ],
    'models' => [
        'plan' => PricingPlan::class,
        'feature' => PricingFeature::class,
        'subscription' => PricingSubscription::class,
        'subscription_usage' => PricingSubscriptionUsage::class,
        'activity' => PricingActivity::class
    ],
    'positive_values' => ['Y', 'OK', 'TRUE']
];
