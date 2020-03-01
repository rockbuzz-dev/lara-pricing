<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Models\{User, Workspace};
use Illuminate\Support\Facades\Event;
use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphTo};
use Rockbuzz\LaraPricing\Models\{Plan, SubscriptionUsage, Subscription};
use Rockbuzz\LaraPricing\Events\{SubscriptionCanceled,
    SubscriptionFinished,
    SubscriptionStarted,
    SubscriptionMakeRecurring};

class SubscriptionTest extends TestCase
{
    protected $subscription;

    public function setUp(): void
    {
        parent::setUp();

        $this->subscription = new Subscription();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Subscription::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->subscription->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->subscription->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'name',
            'start_at',
            'finish_at',
            'canceled_at',
            'due_date',
            'subscribable_id',
            'subscribable_type',
            'plan_id'
        ];

        $this->assertEquals($expected, $this->subscription->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'due_date' => 'date'
        ];

        $this->assertEquals($expected, $this->subscription->getCasts());
    }

    public function testDates()
    {
        $expected = array_values([
            'deleted_at',
            'created_at',
            'updated_at',
            'start_at',
            'finish_at',
            'canceled_at'
        ]);

        $this->assertEquals(
            $expected,
            array_values($this->subscription->getDates())
        );
    }

    public function testSubscriptionHasSubscribable()
    {
        $workspace = $this->create(Workspace::class);
        $subscription = $this->create(Subscription::class, [
            'subscribable_id' => $workspace->id,
            'subscribable_type' => Workspace::class,
        ]);

        $this->assertInstanceOf(MorphTo::class, $subscription->subscribable());
        $this->assertEquals($workspace->id, $subscription->subscribable->id);
    }

    public function testSubscriptionHasPlan()
    {
        $plan = $this->create(Plan::class);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $subscription->plan());
        $this->assertEquals($plan->id, $subscription->plan->id);
    }

    public function testSubscriptionCanHaveUsages()
    {
        $subscription = $this->create(Subscription::class);

        $usage = $this->create(SubscriptionUsage::class, [
            'subscription_id' => $subscription->id
        ]);

        $this->assertInstanceOf(HasMany::class, $subscription->usages());
        $this->assertContains($usage->id, $subscription->usages->pluck('id'));
    }

    public function testSubscriptionStart()
    {
        Event::fake([SubscriptionStarted::class]);

        $subscription = $this->create(Subscription::class, [
            'start_at' => now()->addMinute()
        ]);

        $this->assertFalse($subscription->isStarted());

        $subscription->start();

        $this->assertTrue($subscription->isStarted());

        Event::assertDispatched(SubscriptionStarted::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });

        $subscription->update(['start_at' => now()]);

        $this->expectException(\LogicException::class);

        $subscription->start();
    }

    public function testSubscriptionFinish()
    {
        Event::fake([SubscriptionFinished::class]);

        $subscription = $this->create(Subscription::class, [
            'finish_at' => null
        ]);

        $this->assertFalse($subscription->isFinished());

        $subscription->update(['finish_at' => now()->addSecond()]);

        $this->assertFalse($subscription->isFinished());

        $subscription->finish();

        $this->assertTrue($subscription->isFinished());

        Event::assertDispatched(SubscriptionFinished::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testSubscriptionCancel()
    {
        Event::fake([SubscriptionCanceled::class]);

        $subscription = $this->create(Subscription::class, [
            'canceled_at' => null
        ]);

        $this->assertFalse($subscription->isCanceled());

        $user = $this->create(User::class);
        $this->signIn([], $user);

        $subscription->cancel();

        $this->assertTrue($subscription->isCanceled());

        Event::assertDispatched(SubscriptionCanceled::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testSubscriptionRecurrent()
    {
        Event::fake([SubscriptionMakeRecurring::class]);

        $subscription = $this->create(Subscription::class, [
            'start_at' => now()->subMonth(),
            'finish_at' => now()->subMonth(),
            'canceled_at' => null
        ]);

        $this->assertFalse($subscription->isRecurrent());

        $subscription->makeRecurring();

        $this->assertTrue($subscription->isRecurrent());

        Event::assertDispatched(SubscriptionMakeRecurring::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testSubscriptionHasTrial()
    {
        $plan = $this->create(Plan::class, ['trial_period_days' => 0]);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id
        ]);

        $this->assertFalse($subscription->hasTrial());

        $plan->update(['trial_period_days' => 1]);

        $this->assertTrue($subscription->hasTrial());
    }

    public function testSubscriptionTrial()
    {
        $plan = $this->create(Plan::class, ['trial_period_days' => 15]);
        $subscription = $this->create(Subscription::class, [
            'plan_id' => $plan->id
        ]);

        $this->assertEquals(15, $subscription->trial());
    }

    public function testSubscriptionActive()
    {
        $subscription = $this->create(Subscription::class, [
            'start_at' => now()->addDay(),
            'finish_at' => now()->subMinute(),
            'canceled_at' => now()->subMinute()
        ]);

        $this->assertFalse($subscription->isActive());
        $this->assertTrue($subscription->isInactive());

        $subscription->update(['start_at' => now()->subMinute()]);

        $this->assertFalse($subscription->isActive());
        $this->assertTrue($subscription->isInactive());

        $subscription->update(['finish_at' => now()->addDay()]);

        $this->assertFalse($subscription->isActive());
        $this->assertTrue($subscription->isInactive());

        $subscription->update(['canceled_at' => null]);

        $this->assertTrue($subscription->isActive());
        $this->assertFalse($subscription->isInactive());
    }
}
