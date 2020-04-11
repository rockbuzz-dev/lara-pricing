# Lara Pricing

Pricing management

[![Build Status](https://travis-ci.org/rockbuzz/lara-pricing.svg?branch=master)](https://travis-ci.org/rockbuzz/lara-pricing)

## Requirements

PHP: >=7.3

## Install

```bash
$ composer require rockbuzz/lara-pricing
```

Publish migration and config files

```
$ php artisan vendor:publish --provider="Rockbuzz\LaraPricing\ServiceProvider"
$ php artisan migrate
```

Add Subscribable contract end trait to your model

```php
use Rockbuzz\LaraPricing\Traits\Subscribable;
use Rockbuzz\LaraPricing\DTOs\ChangePlanOptions;
use Rockbuzz\LaraPricing\Models\{Plan, Subscription}
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;
	
class YourModel implements SubscribableContract
{
    use Subscribable;
    ...
	    
}
```

## Usage

```php
$yourModel->subscriptions(): MorphMany
$yourModel->currentSubscription(): Subscription
$yourModel->currentPlan(): Plan
$yourModel->changePlan(Plan $newPlan, ChangePlanOptions $options = null): bool
$yourModel->featureEnabled(string $featureSlug): bool
$yourModel->featureValue(string $featureSlug): string
$yourModel->incrementUse(string $featureSlug, int $uses = 1): void
$yourModel->decrementUse(string $featureSlug, int $uses = 1): void
$yourModel->consumedUse(string $featureSlug): int
$yourModel->remainingUse(string $featureSlug): int
$yourModel->canUse(string $featureSlug): bool
```

## Events

```php
Rockbuzz\LaraPricing\Events\ChangePlan::class
Rockbuzz\LaraPricing\Events\SubscriptionCanceled::class
Rockbuzz\LaraPricing\Events\SubscriptionFinished::class
Rockbuzz\LaraPricing\Events\SubscriptionMakeRecurring::class
Rockbuzz\LaraPricing\Events\SubscriptionStarted::class
```

## License

The Lara Pricing is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).