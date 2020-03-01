<?php

namespace Rockbuzz\LaraPricing\Models;

use Rockbuzz\LaraPricing\Traits\Uuid;
use Illuminate\Database\Eloquent\{Model, SoftDeletes};

class InvoiceItem extends Model
{
    use Uuid, SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'name',
        'description',
        'unit_price',
        'quantity',
        'invoice_id'
    ];

    protected $casts = [
        'id' => 'string',
        'unit_price' => 'integer'
    ];

    protected $dates = [
        'deleted_at',
        'created_at',
        'updated_at'
    ];

    public function invoice()
    {
        return $this->belongsTo(Invoice::class);
    }
}
