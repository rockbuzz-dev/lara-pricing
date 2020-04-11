<?php

namespace Rockbuzz\LaraPricing\Contracts;

use LogicException;
use Rockbuzz\LaraPricing\Models\{Plan, Subscription};
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface Subscribable
{
    /**
     * @return MorphMany
     */
    public function subscriptions(): MorphMany;

    /**
     * @return Subscription
     * @throws ModelNotFoundException
     */
    public function currentSubscription(): Subscription;

    /**
     * @return Plan
     * @throws ModelNotFoundException
     */
    public function currentPlan(): Plan;

    /**
     * @param string $featureSlug
     * @return bool
     */
    public function featureEnabled(string $featureSlug): bool;

    /**
     * @param string $featureSlug
     * @return string
     */
    public function featureValue(string $featureSlug): string;

    /**
     * @param string $featureSlug
     * @param int $uses
     * @throws ModelNotFoundException Subscription creation date must be greater than or equal to the functionality
     * @throws LogicException if the subscription is inactive;
     */
    public function incrementUse(string $featureSlug, int $uses = 1): void;

    /**
     * @param string $featureSlug
     * @param int $uses
     * @throws ModelNotFoundException if subscription, feature or usage does not exist
     * @throws LogicException if the subscription is inactive;
     */
    public function decrementUse(string $featureSlug, int $uses = 1): void;

    /**
     * @param string $featureSlug
     * @return int
     * @throws LogicException if the subscription is inactive;
     */
    public function consumedUse(string $featureSlug): int;

    /**
     * @param string $featureSlug
     * @return int
     * @throws LogicException if the subscription is inactive;
     */
    public function remainingUse(string $featureSlug): int;

    /**
     * @param string $featureSlug
     * @return bool
     * @throws LogicException if the subscription is inactive;
     */
    public function canUse(string $featureSlug): bool;
}
