<?php

namespace Tests\Models;

use Illuminate\Database\Eloquent\Model;
use Rockbuzz\LaraPricing\Traits\Subscribable;
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;

class Account extends Model implements SubscribableContract
{
    use Subscribable;

    protected $guarded = [];
}
