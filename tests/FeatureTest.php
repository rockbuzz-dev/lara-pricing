<?php

namespace Tests\Models;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Tests\TestCase;
use Rockbuzz\LaraPricing\Models\Feature;
use Illuminate\Database\Eloquent\SoftDeletes;

class FeatureTest extends TestCase
{
    protected $feature;

    public function setUp(): void
    {
        parent::setUp();

        $this->feature = new Feature();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Feature::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->feature->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->feature->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'name',
            'slug',
            'sort_order'
        ];

        $this->assertEquals($expected, $this->feature->getFillable());
    }

    public function testCasts()
    {
        $expected = ['id' => 'string'];

        $this->assertEquals($expected, $this->feature->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->feature->getDates())
        );
    }

    public function testFeatureSetNameAttribute()
    {
        $feature = Feature::create(['name' => 'Max Users']);

        $this->assertEquals('max-users', $feature->slug);
    }
}
