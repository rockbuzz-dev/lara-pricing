<?php

namespace Tests;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\{BelongsTo, HasMany};
use Rockbuzz\LaraPricing\Models\{Invoice, InvoiceItem, Subscription};

class InvoiceItemTest extends TestCase
{
    protected $invoiceItem;

    public function setUp(): void
    {
        parent::setUp();

        $this->invoiceItem = new InvoiceItem();
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
        $this->assertFalse($this->invoiceItem->incrementing);
    }

    public function testKeyType()
    {
        $this->assertEquals('string', $this->invoiceItem->getKeyType());
    }

    public function testFillable()
    {
        $expected = [
            'name',
            'description',
            'unit_price',
            'quantity',
            'invoice_id'
        ];

        $this->assertEquals($expected, $this->invoiceItem->getFillable());
    }

    public function testCasts()
    {
        $expected = [
            'id' => 'string',
            'unit_price' => 'integer'
        ];

        $this->assertEquals($expected, $this->invoiceItem->getCasts());
    }

    public function testDates()
    {
        $this->assertEquals(
            array_values(['deleted_at', 'created_at', 'updated_at']),
            array_values($this->invoiceItem->getDates())
        );
    }

    public function testInvoiceHasInvoice()
    {
        $invoice = $this->create(Invoice::class);
        $invoiceItem = $this->create(InvoiceItem::class, [
            'invoice_id' => $invoice->id
        ]);

        $this->assertInstanceOf(BelongsTo::class, $invoiceItem->invoice());
        $this->assertEquals($invoice->id, $invoiceItem->invoice->id);
    }
}
