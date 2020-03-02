<?php

namespace Rockbuzz\LaraPricing\Events;

use Illuminate\Queue\SerializesModels;
use Rockbuzz\LaraPricing\Models\PricingSubscription;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Broadcasting\{Channel, InteractsWithSockets, PresenceChannel, PrivateChannel};

class SubscriptionMakeRecurring
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * @var PricingSubscription
     */
    public $subscription;

    public function __construct(PricingSubscription $subscription)
    {
        $this->subscription = $subscription;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
