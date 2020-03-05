<?php

namespace Tests;

use Rockbuzz\LaraUuid\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Rockbuzz\LaraPricing\Enums\PlanFeatureValue;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Rockbuzz\LaraPricing\Models\{Plan, Feature};
use Spatie\Sluggable\HasSlug;

class PlanTest extends TestCase
{
    protected $plan;

    public function setUp(): void
    {
        parent::setUp();

        $this->plan = new Plan();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class,
            HasSlug::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Plan::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->plan->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->plan->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'name',
            'slug',
            'description',
            'price',
            'interval',
            'period',
            'trial_period_days',
            'sort_order'
        ];

        $this->assertEquals($expected, $this->plan->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'price' => 'integer',
            'period' => 'integer',
            'trial_period_days' => 'integer',
            'sort_order' => 'integer'
        ];

        $this->assertEquals($expected, $this->plan->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->plan->getDates())
        );
    }

    public function testPlanCanHaveFeatures()
    {
        $plan = $this->create(Plan::class);
        $feature = $this->create(Feature::class);

        \DB::table(config('pricing.tables.feature_plan'))->insert([
            'feature_id' => $feature->id,
            'plan_id' => $plan->id,
            'value' => PlanFeatureValue::POSITIVE
        ]);

        $this->assertInstanceOf(BelongsToMany::class, $plan->features());
        $this->assertContains($feature->id, $plan->features->pluck('id'));
    }

    public function testPlanHasFeature()
    {
        $plan = $this->create(Plan::class);
        $feature = $this->create(Feature::class);

        $this->assertFalse($plan->hasFeature($feature->slug));

        \DB::table(config('pricing.tables.feature_plan'))->insert([
            'feature_id' => $feature->id,
            'plan_id' => $plan->id,
            'value' => PlanFeatureValue::POSITIVE
        ]);

        $this->assertTrue($plan->hasFeature($feature->slug));
    }

    public function testPricingPlanMustHaveSlug()
    {
        $plan = $this->create(Plan::class, [
            'name' => 'Plan 1',
            'slug' => null
        ]);

        $this->assertEquals('plan-1', $plan->slug);
    }

    public function testPlanScopeMonthly()
    {
        $plan = $this->create(Plan::class, ['interval' => 'month', 'period' => 3]);
        $monthlyPlan = $this->create(Plan::class, ['interval' => 'month', 'period' => 1]);

        $this->assertNotContains($plan->id, Plan::monthly()->get()->pluck('id'));
        $this->assertContains($monthlyPlan->id, Plan::monthly()->get()->pluck('id'));
    }

    public function testPlanScopeYearly()
    {
        $plan = $this->create(Plan::class, ['interval' => 'month', 'period' => 3]);
        $yearlyPlan = $this->create(Plan::class, ['interval' => 'month', 'period' => 12]);

        $this->assertNotContains($plan->id, Plan::yearly()->get()->pluck('id'));
        $this->assertContains($yearlyPlan->id, Plan::yearly()->get()->pluck('id'));
    }
}
