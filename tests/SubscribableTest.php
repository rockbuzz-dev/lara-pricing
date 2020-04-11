<?php

namespace Tests;

use Illuminate\Support\Facades\DB;
use Tests\Models\{User, Workspace};
use Illuminate\Support\Facades\Event;
use Rockbuzz\LaraPricing\Events\ChangePlan;
use Rockbuzz\LaraPricing\Enums\PlanFeatureValue;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Rockbuzz\LaraPricing\Models\{Feature, Plan, Subscription, SubscriptionUsage};

class SubscribableTest extends TestCase
{
    public function testSubscribableHasSubscriptions()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);

        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertInstanceOf(MorphMany::class, $subscribable->subscriptions());
        $this->assertContains($subscription->id, $subscribable->subscriptions->pluck('id'));
    }

    public function testSubscribableHasCurrentSubscription()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);

        $this->create(Subscription::class, [
            'created_at' => now()->subSecond(),
            'start_at' => now()->subSecond(),
            'finish_at' => null,
            'canceled_at' => null,
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $subscription = $this->create(Subscription::class, [
            'created_at' => now(),
            'start_at' => now()->subSecond(),
            'finish_at' => now()->subSecond(),
            'canceled_at' => now()->subSecond(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertEquals($subscription->id, $subscribable->currentSubscription()->id);

        Subscription::all()->each(function ($subscription) {
            $subscription->delete();
        });

        $this->expectException(ModelNotFoundException::class);

        $subscribable->currentSubscription();
    }

    public function testSubscribableHasCurrentPlan()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);

        $this->create(Subscription::class, [
            'created_at' => now()->subSecond(),
            'start_at' => now()->subSecond(),
            'finish_at' => null,
            'canceled_at' => null,
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertEquals($plan->id, $subscribable->currentPlan()->id);

        Subscription::all()->each(function ($subscription) {
            $subscription->delete();
        });

        $this->expectException(ModelNotFoundException::class);

        $subscribable->currentPlan();
    }

    public function testSubscribableHasChangePlan()
    {
        Event::fake([ChangePlan::class]);
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);

        $this->create(Subscription::class, [
            'created_at' => now()->subSecond(),
            'start_at' => now()->subSecond(),
            'finish_at' => null,
            'canceled_at' => null,
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $newPlan = $this->create(Plan::class);

        $this->assertTrue($subscribable->changePlan($newPlan));

        $currentSubscription = $subscribable->currentSubscription();

        Event::assertDispatched(ChangePlan::class, function ($e) use ($currentSubscription) {
            return $e->subscription->id === $currentSubscription->id;
        });

        $this->assertNotEquals($plan->id, $subscribable->currentPlan()->id);
        $this->assertEquals($newPlan->id, $subscribable->currentPlan()->id);
    }

    public function testSubscribableFeatureEnabled()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Trails', 'slug' => 'trails']);
        $plan->features()->attach([$feature->id => ['value' => PlanFeatureValue::POSITIVE]]);

        $this->assertTrue($subscribable->featureEnabled($feature->slug));

        $plan->features()->detach($feature->id);

        $this->assertFalse($subscribable->featureEnabled($feature->slug));
    }

    public function testSubscribableFeatureValue()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $feature = $this->create(Feature::class);

        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertEquals('0', $subscribable->featureValue($feature->slug));

        DB::table(config('pricing.tables.feature_plan'))->insert([
            'feature_id' => $feature->id,
            'plan_id' => $plan->id,
            'value' => '10'
        ]);

        $this->assertEquals('10', $subscribable->featureValue($feature->slug));
    }

    public function testSubscribableIncrementUseWithInactiveSubscription()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);

        $this->create(Subscription::class, [
            'canceled_at' => now()->subSecond(),
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->incrementUse('not-exists');
    }

    public function testSubscribableIncrementUseWithoutFeature()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);

        $this->expectException(ModelNotFoundException::class);

        $subscribable->incrementUse('not-exists');
    }

    public function testSubscribableIncrementUseWithoutFeatureInPlan()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);

        $this->expectException(ModelNotFoundException::class);

        $subscribable->incrementUse('users');
    }

    public function testSubscribableIncrementUseMustThrowExceptionWhenWithoutFeatureUsage()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $createdSubscription = now();
        $this->create(Subscription::class, [
            'created_at' => $createdSubscription,
            'start_at' => now()->subDay(),
            'canceled_at' => null,
            'finish_at' => now()->addMonth(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, [
            'name' => 'Users',
            'slug' => 'users',
            'created_at' => $createdSubscription->subSecond()
        ]);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->expectExceptionMessage(
            'Subscription creation date must be greater than or equal to the functionality'
        );
        $this->expectException(\LogicException::class);

        $subscribable->incrementUse($feature->slug);
    }

    public function testSubscribableIncrementUseWithoutFeatureUsage()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $createdSubscription = now();
        $subscription = $this->create(Subscription::class, [
            'created_at' => $createdSubscription,
            'start_at' => now()->subDay(),
            'canceled_at' => null,
            'finish_at' => now()->addMonth(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, [
            'name' => 'Users',
            'slug' => 'users',
            'created_at' => $createdSubscription->addSecond()
        ]);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $user = $this->create(User::class);
        $this->signIn([], $user);

        $subscribable->incrementUse($feature->slug);

        $this->assertDatabaseHas('subscription_usages', [
            'used' => '1',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id
        ]);

        $usage = $subscription->usages()->where('feature_id', $feature->id)->first();

        $this->assertCount(1, $usage->activities);
        $this->assertDatabaseHas('pricing_activities', [
            'description' => "incremented 1 Users",
            'changes' => json_encode([
                'before' => '0',
                'after' => '1'
            ]),
            'causeable_id' => $user->id,
            'causeable_type' => User::class
        ]);
    }

    public function testSubscribableIncrementUseWithFeatureUsage()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'start_at' => now()->subDay(),
            'canceled_at' => null,
            'finish_at' => now()->addMonth(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $usageId = \Ramsey\Uuid\Uuid::uuid4();
        DB::table(config('pricing.tables.subscription_usages'))->insert([
            'id' => $usageId,
            'used' => '5',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id,
        ]);

        $user = $this->create(User::class);
        $this->signIn([], $user);

        $subscribable->incrementUse($feature->slug);

        $this->assertDatabaseHas('subscription_usages', [
            'used' => '6',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id
        ]);

        $usage = SubscriptionUsage::findOrFail($usageId);

        $this->assertCount(1, $usage->activities);
        $this->assertDatabaseHas('pricing_activities', [
            'description' => "incremented 1 Users",
            'changes' => json_encode([
                'before' => '5',
                'after' => '6'
            ]),
            'causeable_id' => $user->id,
            'causeable_type' => User::class
        ]);
    }

    public function testSubscribableDecrementUseWithInactiveSubscription()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);
        $this->create(Subscription::class, [
            'canceled_at' => now()->subSecond(),
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->decrementUse('users');
    }

    public function testSubscribableDecrementUseWithoutFeature()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);

        $this->expectException(ModelNotFoundException::class);

        $subscribable->decrementUse('not-exists');
    }

    public function testSubscribableDecrementUseWithoutFeatureInPlan()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);

        $this->expectException(ModelNotFoundException::class);

        $subscribable->decrementUse('users');
    }

    public function testSubscribableDecrementUseWithoutFeatureUsage()
    {
        /**@var \Rockbuzz\LaraPricing\Contracts\Subscribable $subscribable **/
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->expectException(ModelNotFoundException::class);

        $subscribable->decrementUse('users');
    }

    public function testSubscribableDecrementUse()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $usageId = \Ramsey\Uuid\Uuid::uuid4();
        DB::table(config('pricing.tables.subscription_usages'))->insert([
            'id' => $usageId,
            'used' => '5',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id,
        ]);

        $user = $this->create(User::class);
        $this->signIn([], $user);

        $subscribable->decrementUse($feature->slug);

        $this->assertDatabaseHas('subscription_usages', [
            'used' => '4',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id
        ]);

        $subscribable->decrementUse($feature->slug, 5);

        $this->assertDatabaseHas('subscription_usages', [
            'used' => '1',
            'feature_id' => $feature->id,
            'subscription_id' => $subscription->id
        ]);

        $usage = SubscriptionUsage::findOrFail($usageId);

        $this->assertCount(2, $usage->activities);
        $this->assertDatabaseHas('pricing_activities', [
            'description' => "decremented 1 Users",
            'changes' => json_encode([
                'before' => '5',
                'after' => '4'
            ]),
            'causeable_id' => $user->id,
            'causeable_type' => User::class
        ]);
        $this->assertDatabaseHas('pricing_activities', [
            'description' => "decremented 5 Users",
            'changes' => json_encode([
                'before' => '4',
                'after' => '1'
            ]),
            'causeable_id' => $user->id,
            'causeable_type' => User::class
        ]);
    }

    public function testSubscribableConsumedUseWithInactiveSubscription()
    {
        $subscribable = $this->create(Workspace::class);
        $this->create(Subscription::class, [
            'finish_at' => now()->subSecond(),
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->consumedUse($feature->slug);
    }

    public function testSubscribableConsumedUse()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->assertEquals(0, $subscribable->consumedUse($feature->slug));

        $subscription->usages()->create([
            'feature_id' => $feature->id,
            'used' => '9'
        ]);

        $this->assertEquals(9, $subscribable->consumedUse($feature->slug));

        $feature->delete();

        $this->assertEquals(0, $subscribable->consumedUse($feature->slug));
    }

    public function testSubscribableRemainingUseWithInactiveSubscription()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'finish_at' => now()->subSecond(),
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->remainingUse($feature->slug);
    }

    public function testSubscribableRemainingUse()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $subscription->usages()->create([
            'used' => '3',
            'feature_id' => $feature->id
        ]);

        $this->assertEquals(7, $subscribable->remainingUse($feature->slug));
    }

    public function testSubscribableCanUseWithoutFeatureInPlan()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertFalse($subscribable->canUse('trails'));
    }

    public function testSubscribableCanUseWithFeatureEnabled()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);
        $feature = $this->create(Feature::class, ['name' => 'Trails', 'slug' => 'trails']);

        $plan->features()->attach([$feature->id => ['value' => 'not-positive']]);

        $this->assertFalse($subscribable->canUse('trails'));

        $plan->features()->sync([$feature->id => ['value' => PlanFeatureValue::POSITIVE]]);

        $this->assertTrue($subscribable->canUse('trails'));
    }

    public function testSubscribableCanUseWithFeatureValueZero()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);

        $plan->features()->attach([$feature->id => ['value' => '0']]);

        $this->assertFalse($subscribable->canUse('users'));
    }

    public function testSubscribableCanUseWithFeatureConsumed()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $usage = $subscription->usages()->create([
            'feature_id' => $feature->id,
            'used' => '10'
        ]);

        $this->assertFalse($subscribable->canUse('users'));

        $usage->update(['used' => '9']);

        $this->assertTrue($subscribable->canUse('users'));
    }

    public function testSubscribableCanUseWithInactiveSubscription()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $this->create(Subscription::class, [
            'finish_at' => now()->subSecond(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->canUse($feature->slug);
    }

    public function testSubscribableRemoveUse()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $feature = $this->create(Feature::class);

        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        DB::table(config('pricing.tables.feature_plan'))->insert([
            'feature_id' => $feature->id,
            'plan_id' => $plan->id,
            'value' => '10'
        ]);

        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->assertCount(0, $subscription->usages);

        $subscription->usages()->create([
            'feature_id' => $feature->id,
            'used' => '10'
        ]);

        $this->assertCount(1, $subscription->fresh()->usages);

        $subscribable->removeUse($feature->slug);

        $this->assertCount(0, $subscription->fresh()->usages);
    }

    public function testSubscribableRemoveUseWithInactiveSubscription()
    {
        $subscribable = $this->create(Workspace::class);
        $plan = $this->create(Plan::class);
        $feature = $this->create(Feature::class);

        $subscription = $this->create(Subscription::class, [
            'finish_at' => now()->subSecond(),
            'plan_id' => $plan->id,
            'subscribable_id' => $subscribable->id,
            'subscribable_type' => Workspace::class,
        ]);

        DB::table(config('pricing.tables.feature_plan'))->insert([
            'feature_id' => $feature->id,
            'plan_id' => $plan->id,
            'value' => '10'
        ]);

        $feature = $this->create(Feature::class, ['name' => 'Users', 'slug' => 'users']);
        $plan->features()->attach([$feature->id => ['value' => '10']]);

        $this->expectException(\LogicException::class);
        $this->expectExceptionMessage('You cannot perform this action with an inactive subscription');

        $subscribable->removeUse($feature->slug);
    }
}
