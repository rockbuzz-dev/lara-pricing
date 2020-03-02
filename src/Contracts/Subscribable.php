<?php

namespace Rockbuzz\LaraPricing\Contracts;

use LogicException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;

interface Subscribable
{
    public function subscriptions(): MorphMany;

    public function featureEnabled(string $featureSlug): bool;

    public function featureValue(string $featureSlug): string;

    /**
     * @param string $featureSlug
     * @param int $uses
     * @throws LogicException Subscription creation date must be greater than or equal to the functionality
     */
    public function incrementUse(string $featureSlug, int $uses = 1): void;

    /**
     * @param string $featureSlug
     * @param int $uses
     * @throws ModelNotFoundException if subscription, feature or usage does not exist
     */
    public function decrementUse(string $featureSlug, int $uses = 1): void;

    public function consumedUse(string $featureSlug): int;

    public function remainingUse(string $featureSlug): int;

    public function canUse(string $featureSlug): bool;
}
