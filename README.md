# Lara Pricing

Laravel package to manage plan subscriptions with restrictive and limited functionalities.

<p><img src="https://github.com/rockbuzz/lara-pricing/workflows/Main/badge.svg"/></p>

## Requirements

PHP >=7.3

## Install

```bash
$ composer require rockbuzz/lara-pricing
```

```php
$ php artisan vendor:publish --provider="Rockbuzz\LaraPricing\ServiceProvider" --tag="migrations"
```

```php
$ php artisan migrate
```

Add the `Subscribeable` interface and trait to the template you will have plan susbcriptions.

```php
use Rockbuzz\LaraPricing\Traits\Subscribable;
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;

class Account extends Model implements SubscribableContract
{
    use Subscribable;
}
```

## Usage

### You can subscribe to a plan.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);

$account = Account::create();
$account->subscribe($plan);
```

### You can unsubscribe the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);

$account = Account::create();
$account->subscribe($plan);

$account->unsubscribe();
```

### You can take the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);

$account = Account::create();
$account->subscribe($plan);

dd($account->currentSubscription()->toArray());

# output
[
    "id" => 1
    "uuid" => "66d0ef96-961d-4b55-8328-115ec44e05f2"
    "start_at" => "2022-12-11T14:39:55.000000Z"
    "finish_at" => null
    "canceled_at" => null
    "due_day" => null
    "subscribable_id" => "1"
    "subscribable_type" => "Tests\Models\Account"
    "plan_id" => "1"
    "immutable_plan" => array:10 [
      "name" => "Plan A"
      "price" => 2990
      "interval" => "month"
      "period" => 1
      "uuid" => "6e9a7100-c314-4653-a40f-24ec22aaaa84"
      "slug" => "plan-a"
      "updated_at" => "2022-12-11T14:39:55.000000Z"
      "created_at" => "2022-12-11T14:39:55.000000Z"
      "id" => 1
      "features" => []
    ]
    "created_at" => "2022-12-11T14:39:55.000000Z"
    "updated_at" => "2022-12-11T14:39:55.000000Z"
    "deleted_at" => null
]

```

### You can subscribe to a plan with feature restriction.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;
use Rockbuzz\LaraPricing\Enums\PlanFeatureValue;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan B',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([
    $feature->id => ['value' => PlanFeatureValue::POSITIVE]
]);// Y, OK and TRUE values are accepted

$account = Account::create();
$account->subscribe($plan);

dd($account->currentSubscription()->toArray());

#output
[
  "id" => 1
  "uuid" => "be571da1-f145-4acf-9494-ebcbc84b0a8f"
  "start_at" => "2022-12-11T15:00:54.000000Z"
  "finish_at" => null
  "canceled_at" => null
  "due_day" => null
  "subscribable_id" => "1"
  "subscribable_type" => "Tests\Models\Account"
  "plan_id" => "1"
  "immutable_plan" => array:10 [
    "name" => "Plan B"
    "price" => 2990
    "interval" => "month"
    "period" => 1
    "uuid" => "c703d2fd-76b5-461f-92f2-05b3a2a4e3ab"
    "slug" => "plan-b"
    "updated_at" => "2022-12-11T15:00:54.000000Z"
    "created_at" => "2022-12-11T15:00:54.000000Z"
    "id" => 1
    "features" => array:1 [
      0 => array:9 [
        "id" => 1
        "uuid" => "b1adf248-ae4b-464d-9667-462e51de6d2f"
        "name" => "aut"
        "slug" => "aut"
        "order_column" => "1"
        "created_at" => "2022-12-11T15:00:54.000000Z"
        "updated_at" => "2022-12-11T15:00:54.000000Z"
        "deleted_at" => null
        "pivot" => array:3 [
          "plan_id" => "1"
          "feature_id" => "1"
          "value" => "Y"
        ]
      ]
    ]
  ]
  "created_at" => "2022-12-11T15:00:54.000000Z"
  "updated_at" => "2022-12-11T15:00:54.000000Z"
  "deleted_at" => null
]
```

### You can check if a feature is available in the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => PlanFeatureValue::POSITIVE]]);

$account = Account::create();
$account->subscribe($plan);

dd($account->featureEnabled($feature->slug));

#output

true
```

### You can subscribe to a plan with feature limit.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

dd($account->currentSubscription()->toArray());

#output
[
  "id" => 1
  "uuid" => "e39d1ce4-08c7-422a-b940-b7575aa613e2"
  "start_at" => "2022-12-11T15:08:38.000000Z"
  "finish_at" => null
  "canceled_at" => null
  "due_day" => null
  "subscribable_id" => "1"
  "subscribable_type" => "Tests\Models\Account"
  "plan_id" => "1"
  "immutable_plan" => array:10 [
    "name" => "Plan B"
    "price" => 2990
    "interval" => "month"
    "period" => 1
    "uuid" => "14fdab88-a6d5-4d4d-b2c8-ccb3786778e6"
    "slug" => "plan-b"
    "updated_at" => "2022-12-11T15:08:38.000000Z"
    "created_at" => "2022-12-11T15:08:38.000000Z"
    "id" => 1
    "features" => array:1 [
      0 => array:9 [
        "id" => 1
        "uuid" => "f0935f56-2dd8-49bc-b2e2-52bc5dcf15f0"
        "name" => "eos"
        "slug" => "eos"
        "order_column" => "1"
        "created_at" => "2022-12-11T15:08:38.000000Z"
        "updated_at" => "2022-12-11T15:08:38.000000Z"
        "deleted_at" => null
        "pivot" => array:3 [
          "plan_id" => "1"
          "feature_id" => "1"
          "value" => "10"
        ]
      ]
    ]
  ]
  "created_at" => "2022-12-11T15:08:38.000000Z"
  "updated_at" => "2022-12-11T15:08:38.000000Z"
  "deleted_at" => null
]
```

### You can get the usage amount of a feature in the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

dd($account->featureEnabled($feature->slug));

#output

10
```

### You can check the usage amount of a feature in the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

dd($account->consumedUse($feature->slug));

#output

0
```

### You can check the rest of usage of a feature of the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

dd($account->remainingUse($feature->slug));

#output

10
```

### You can increment the usage of a functionality in the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

$account->incrementUse($feature->slug);
```
> Optionally you can pass a second parameter with integer value to increment, default is 1.<br>
$account->incrementUse($feature->slug, 2);

### You can decrement the usage of a functionality in the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

$account->decrementUse($feature->slug);
```
> Optionally you can pass a second parameter with integer value to decrement, default is 1.<br>
$account->decrementUse($feature->slug, 2);

### You can check if you can use a feature of the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

dd($account->canUse($feature->slug));

#output

true
```

### You can clear the usages of a functionality from the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$feature = Feature::create(['name' => 'Feature Name']);
$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);
$plan->features()->attach([$feature->id => ['value' => '10']]);

$account = Account::create();
$account->subscribe($plan);

$account->cleanUse($feature->slug);
```

### You can manage recurrences of the current subscription.

```php
use Tests\Models\Account;
use Rockbuzz\LaraPricing\Models\Plan;

$plan = Plan::create([
    'name' => 'Plan Name',
    'price' => 2990,// in cents
    'interval' => 'month',
    'period' => 1
]);

$account = Account::create();

# by default when signing the recurrence is on
$account->subscribe($plan);

dump($account->isRecurrent());

# output

true

$account->cancelRecurrence();

dd($account->isRecurrent());

# output

false
```

## License

The Lara Pricing is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).