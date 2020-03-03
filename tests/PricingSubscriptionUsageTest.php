<?php

namespace Tests\Unit;

use Tests\TestCase;
use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraPricing\Traits\Activityable;
use Illuminate\Database\Eloquent\Relations\MorphMany;
use Rockbuzz\LaraPricing\Models\{PricingSubscriptionUsage, PricingActivity};

class PricingSubscriptionUsageTest extends TestCase
{
    protected $signature;

    public function setUp(): void
    {
        parent::setUp();

        $this->signature = new PricingSubscriptionUsage();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class,
            Activityable::class,
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(PricingSubscriptionUsage::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->signature->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->signature->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'used',
            'subscription_id',
            'feature_id',
            'metadata'
        ];

        $this->assertEquals($expected, $this->signature->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'metadata' => 'array'
        ];

        $this->assertEquals($expected, $this->signature->getCasts());
    }

    public function testDates()
    {
        $expected = array_values([
            'deleted_at',
            'created_at',
            'updated_at'
        ]);

        $this->assertEquals(
            $expected,
            array_values($this->signature->getDates())
        );
    }

    public function testSubscriptionCanHaveActivities()
    {
        $subscriptionUsage = $this->create(PricingSubscriptionUsage::class);

        $activity = $this->create(PricingActivity::class, [
            'activityable_id' => $subscriptionUsage->id,
            'activityable_type' => PricingSubscriptionUsage::class,
        ]);

        $this->assertInstanceOf(MorphMany::class, $subscriptionUsage->activities());
        $this->assertContains($activity->id, $subscriptionUsage->activities->pluck('id'));
    }
}
