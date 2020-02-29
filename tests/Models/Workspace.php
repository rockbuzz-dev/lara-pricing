<?php

namespace Tests\Models;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\Model;
use Rockbuzz\LaraPricing\Traits\Subscribable;
use Rockbuzz\LaraPricing\Contracts\Subscribable as SubscribableContract;

class Workspace extends Model implements SubscribableContract
{
    use Uuid, Subscribable;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $guarded = [];

    protected $casts = [
        'id' => 'string',
    ];
}
