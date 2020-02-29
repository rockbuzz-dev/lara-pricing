<?php

namespace Rockbuzz\LaraPricing\Contracts;

use Illuminate\Database\Eloquent\Relations\MorphMany;

interface Subscribable
{
    public function subscriptions(): MorphMany;

    public function featureEnabled(string $featureSlug): bool;

    public function featureValue(string $featureSlug): string;

    public function incrementUse(string $featureSlug, int $uses = 1): void;

    public function decrementUse(string $featureSlug, int $uses = 1): void;

    public function consumedUse(string $featureSlug): int;

    public function remainingUse(string $featureSlug): int;

    public function canUse(string $featureSlug): bool;
}
