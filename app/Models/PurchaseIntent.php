<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PurchaseIntent extends Model
{
    use HasFactory;

    protected $fillable = [
        'batch_id',
        'bom_header_id',
        'item_code',
        'description',
        'specification',
        'required_qty',
        'available_qty',
        'shortfall_qty',
        'priority',
        'status'
    ];

    protected $casts = [
        'required_qty' => 'decimal:4',
        'available_qty' => 'decimal:4',
        'shortfall_qty' => 'decimal:4',
    ];

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class);
    }
}
