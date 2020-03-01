<?php

namespace Tests;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Rockbuzz\LaraPricing\Models\{Invoice, InvoiceItem, Subscription};

class InvoiceTest extends TestCase
{
    protected $invoice;

    public function setUp(): void
    {
        parent::setUp();

        $this->invoice = new Invoice();
    }

    public function testIfUsesTraits()
    {
        $expected = [
            Uuid::class,
            SoftDeletes::class
        ];

        $this->assertEquals(
            $expected,
            array_values(class_uses(Invoice::class))
        );
    }

    public function testIncrementing()
    {
        $this->assertFalse($this->invoice->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->invoice->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'price',
            'due_date',
            'subscription_id'
        ];

        $this->assertEquals($expected, $this->invoice->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'price' => 'integer',
            'due_date' => 'date'
        ];

        $this->assertEquals($expected, $this->invoice->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->invoice->getDates())
        );
    }

    public function testInvoiceHasFeature()
    {
        $subscription = $this->create(Subscription::class);
        $invoice = $this->create(Invoice::class, [
            'subscription_id' => $subscription->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $invoice->subscription());
        $this->assertEquals($subscription->id, $invoice->subscription->id);
    }

    public function testInvoiceHasInvoiceItems()
    {
        $invoice = $this->create(Invoice::class);
        $item = $this->create(InvoiceItem::class, [
            'invoice_id' => $invoice->id
        ]);

        $this->assertInstanceOf(HasMany::class, $invoice->items());
        $this->assertContains($item->id, $invoice->items->pluck('id'));
    }
}
