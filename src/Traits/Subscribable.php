<?php

namespace Rockbuzz\LaraPricing\Traits;

use LogicException;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraPricing\Models\{Feature, Subscription};

trait Subscribable
{
    public function subscriptions(): MorphMany
    {
        return $this->morphMany(config('pricing.models.subscription'), 'subscribable');
    }

    public function currentSubscription()
    {
        return $this->subscriptions()->latest()->firstOrFail();
    }

    public function currentPlan()
    {
        return $this->currentSubscription()->plan;
    }

    /**
     * @inheritDoc
     */
    public function featureEnabled(string $featureSlug): bool
    {
        $subscription = $this->currentSubscription();

        if (!$subscription->plan->hasFeature($featureSlug)) {
            return false;
        }

        return in_array(strtoupper($this->featureValue($featureSlug)), config('pricing.positive_values'));
    }

    /**
     * @inheritDoc
     */
    public function featureValue(string $featureSlug): string
    {
        $plan = $this->currentSubscription()->plan;

        if (!$plan->relationLoaded('features')) {
            $plan->features()->getEager();
        }

        foreach ($plan->features as $feature) {
            if ($featureSlug === $feature->slug) {
                return $feature->pivot->value;
            }
        }

        return '0';
    }

    /**
     * @inheritDoc
     */
    public function incrementUse(string $featureSlug, int $uses = 1): void
    {
        $subscription = $this->currentSubscription();

        $this->ifActiveSubscriptionOrLogicException($subscription);

        $feature = Feature::whereSlug($featureSlug)->firstOrFail();

        $subscription->plan->features()->where('feature_id', $feature->id)->firstOrFail();

        $usage = $subscription->usages()->where('feature_id', $feature->id)->first();

        if (!$usage) {
            $this->isANewFeatureOrLogicException($subscription, $feature);
            $usage = $subscription->usages()->create([
                'used' => $uses,
                'feature_id' => $feature->id
            ]);
            $before = '0';
        } else {
            $before = $usage->used;
            $usage->update(['used' => $usage->used + $uses]);
        }

        $usage->activities()->create([
            'description' => "incremented {$uses} {$feature->name}",
            'changes' => [
                'before' => $before,
                'after' => $usage->fresh()->used
            ],
            'causeable_id' => auth()->user()->id,
            'causeable_type' => get_class(auth()->user())
        ]);
    }

    /**
     * @inheritDoc
     */
    public function decrementUse(string $featureSlug, int $uses = 1): void
    {
        $subscription = $this->currentSubscription();

        $this->ifActiveSubscriptionOrLogicException($subscription);

        $feature = Feature::whereSlug($featureSlug)->firstOrFail();

        $subscription->plan->features()->where('feature_id', $feature->id)->firstOrFail();

        $usage = $subscription->usages()->where('feature_id', $feature->id)->firstOrFail();

        $newUsed = $usage->used - $uses;

        $used = (int)($newUsed) < 0 ? '1' : $newUsed;

        $before = $usage->used;

        $usage->update(['used' => $used]);

        $usage->activities()->create([
            'description' => "decremented {$uses} {$feature->name}",
            'changes' => [
                'before' => $before,
                'after' => $usage->fresh()->used
            ],
            'causeable_id' => auth()->user()->id,
            'causeable_type' => get_class(auth()->user())
        ]);
    }

    /**
     * @inheritDoc
     */
    public function consumedUse(string $featureSlug): int
    {
        $feature = Feature::whereSlug($featureSlug)->first();

        if ($feature) {
            $subscription = $this->currentSubscription();

            $this->ifActiveSubscriptionOrLogicException($subscription);

            if (!$subscription->relationLoaded('usages')) {
                $subscription->usages()->getEager();
            }

            foreach ($subscription->usages as $usage) {
                if ($usage->feature_id === $feature->id) {
                    return (int) $usage->used;
                }
            }
        }

        return 0;
    }

    /**
     * @inheritDoc
     */
    public function remainingUse(string $featureSlug): int
    {
        return (int)$this->featureValue($featureSlug) - $this->consumedUse($featureSlug);
    }

    /**
     * @inheritDoc
     */
    public function canUse(string $featureSlug): bool
    {
        $feature = Feature::whereSlug($featureSlug)->first();

        if ($feature) {
            if ($this->featureEnabled($feature->slug)) {
                return true;
            }

            $featureValue = $this->featureValue($feature->slug);

            if ($featureValue === '0') {
                return false;
            }

            return $this->remainingUse($feature->slug) > 0;
        }

        return false;
    }

    /**
     * @inheritDoc
     */
    public function removeUse(string $featureSlug)
    {
        $feature = Feature::whereSlug($featureSlug)->first();

        if ($feature) {
            $subscription = $this->currentSubscription();

            $this->ifActiveSubscriptionOrLogicException($subscription);

            $subscription->usages()->where('feature_id', $feature->id)->delete();
        }
    }

    /**
     * @param Subscription $subscription
     * @param Feature $feature
     * @throws LogicException
     */
    protected function isANewFeatureOrLogicException(
        Subscription $subscription,
        Feature $feature
    ): void
    {
        //if subscription creation > or = to feature
        if ($subscription->created_at->gte($feature->created_at)) {
            throw new LogicException(
                'Subscription creation date must be greater than or equal to the functionality'
            );
        }
    }

    /**
     * @param Subscription $subscription
     * @throws LogicException
     */
    protected function ifActiveSubscriptionOrLogicException(Subscription $subscription): void
    {
        if ($subscription->isInactive()) {
            throw new \LogicException('You cannot perform this action with an inactive subscription');
        }
    }
}
