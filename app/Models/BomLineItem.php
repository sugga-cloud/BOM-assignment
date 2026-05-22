<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BomLineItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'bom_header_id',
        'part_no',
        'part_code',
        'item_code',
        'description',
        'specification',
        'size_of_material',
        'required_qty',
        'uom',
        'purchase_tech_spec_no',
        'stock_verification',
        'remarks',
        'allocated_to_role',
        'is_critical',
        'is_assembly_header',
        'status',
    ];

    protected $casts = [
        'required_qty'       => 'decimal:4',
        'is_critical'        => 'boolean',
        'is_assembly_header' => 'boolean',
    ];

    public function bomHeader(): BelongsTo
    {
        return $this->belongsTo(BomHeader::class);
    }
}
