<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class BomHeader extends Model
{
    use HasFactory;

    protected $fillable = ['project_id', 'version', 'status', 'uploaded_by'];

    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }

    public function uploadedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function lineItems(): HasMany
    {
        return $this->hasMany(BomLineItem::class);
    }

    public function purchaseIntents(): HasMany
    {
        return $this->hasMany(PurchaseIntent::class);
    }

    public function materialAllocations(): HasMany
    {
        return $this->hasMany(MaterialAllocation::class);
    }
}
