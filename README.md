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
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;
use Rockbuzz\LaraPricing\Traits\Subscribable;
use Rockbuzz\LaraPricing\Models\{Plan, Subscription}
use Illuminate\Database\Eloquent\Relations\MorphMany;
	
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
$yourModel->featureEnabled(string $featureSlug): bool
$yourModel->featureValue(string $featureSlug): string
$yourModel->incrementUse(string $featureSlug, int $uses = 1): void
$yourModel->decrementUse(string $featureSlug, int $uses = 1): void
$yourModel->consumedUse(string $featureSlug): int
$yourModel->remainingUse(string $featureSlug): int
$yourModel->canUse(string $featureSlug): bool
```

## License

The Lara Pricing is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).