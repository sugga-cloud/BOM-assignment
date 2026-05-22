<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    use HasFactory;

    protected $table = 'inventory';

    protected $fillable = [
        'item_code',
        'description',
        'available_qty'
    ];

    protected $casts = [
        'available_qty' => 'decimal:4',
    ];
}
