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
$ php artisan vendor:publish --provider="Rockbuzz\LaraRbac\ServiceProvider"
$ php artisan migrate
```

Add Subscribable contract end trait to your model

```php
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;
use Rockbuzz\LaraPricing\Traits\Subscribable;
	
class YourModel implements SubscribableContract
{
    use Subscribable;
    ...
	    
}
```

## Usage

```php
$model->subscriptions();
$model->currentSubscription();
$model->featureEnabled($featureSlug);
$model->featureValue($featureSlug);
$model->incrementUse($featureSlug, 2);
$model->decrementUse($featureSlug, 2);
$model->consumedUse($featureSlug);
$model->remainingUse($featureSlug);
$model->canUse($featureSlug);
```

## License

The Lara Pricing is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).