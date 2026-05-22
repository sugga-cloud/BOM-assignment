<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaterialAllocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_header_id',
        'item_code',
        'description',
        'allocated_qty',
        'allocated_to',
        'allocated_by'
    ];

    protected $casts = [
        'allocated_qty' => 'decimal:4',
    ];

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class);
    }
}
