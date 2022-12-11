<?php

namespace Tests\Unit;

use Tests\TestCase;
use Tests\Models\{User, Account};
use Illuminate\Support\Facades\Event;
use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany, MorphTo};
use Rockbuzz\LaraPricing\Models\{Plan, SubscriptionUsage, Subscription};
use Rockbuzz\LaraPricing\Events\{SubscriptionCanceled,
    SubscriptionCancelRecurrence,
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

    public function testFillable()
    {
        $expected = [
            'start_at',
            'finish_at',
            'canceled_at',
            'due_day',
            'subscribable_id',
            'subscribable_type',
            'plan_id',
            'immutable_plan'
        ];

        $this->assertEquals($expected, $this->subscription->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'int',
            'due_day' => 'date',
            'immutable_plan' => 'array'
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
        $Account = $this->create(Account::class);
        $subscription = $this->create(Subscription::class, [
            'subscribable_id' => $Account->id,
            'subscribable_type' => Account::class,
        ]);

        $this->assertInstanceOf(MorphTo::class, $subscription->subscribable());
        $this->assertEquals($Account->id, $subscription->subscribable->id);
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

    public function testSubscriptionMakeRecurrent()
    {
        Event::fake([SubscriptionMakeRecurring::class]);

        $finish =  now()->subMonths(3);

        $subscription = $this->create(Subscription::class, [
            'start_at' => now()->subMonth(),
            'finish_at' => $finish,
            'canceled_at' => null
        ]);

        $this->assertFalse($subscription->isRecurrent());

        $subscription->makeRecurring();

        $this->assertTrue($subscription->isRecurrent());

        Event::assertDispatched(SubscriptionMakeRecurring::class, function ($e) use ($subscription) {
            return $e->subscription->id === $subscription->id;
        });
    }

    public function testSubscriptionCancelRecurrent()
    {
        Event::fake([SubscriptionCancelRecurrence::class]);

        $now = now();
        $finish = clone $now;

        $finish->addMonths(3);

        $subscription = $this->create(Subscription::class, [
            'start_at' => $now,
            'finish_at' => null,
            'canceled_at' => null,
            'plan_id' => $this->create(Plan::class, [
                'interval' => 'month',
                'period' => 3
            ])->id
        ]);

        $this->assertTrue($subscription->isRecurrent());
        
        $subscription->cancelRecurrence();
        
        $this->assertFalse($subscription->isRecurrent());

        $this->assertDatabaseHas('subscriptions', [
            'id' => $subscription->id,
            'finish_at' => $finish->toDateTimeString()
        ]);

        Event::assertDispatched(SubscriptionCancelRecurrence::class, function ($e) use ($subscription) {
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
