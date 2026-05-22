<?php

namespace App\Services;

use App\Models\BomHeader;
use App\Models\BomLineItem;
use App\Models\Inventory;
use App\Models\MaterialAllocation;
use App\Models\PurchaseIntent;
use Illuminate\Support\Str;
class InventoryManagementService
{
    /**
     * Process stock math and updates for a single BOM header line item inside an active transaction.
     */
    public function processLineItem(BomHeader $bomHeader, BomLineItem $lineItem, string $batchId): void
    {
        $itemCode = $lineItem->part_code;
        $requiredQty = (float) $lineItem->required_qty;
        $materialItemCode = $itemCode ?: $lineItem->description ?: 'UNKNOWN';

        if ($requiredQty <= 0.0) {
            $lineItem->status = 'in_stock';
            $lineItem->save();
            return;
        }
        
        $inventory = $itemCode
            ? Inventory::where('item_code', $itemCode)->lockForUpdate()->first()
            : null;
        // If the inventory row doesn't exist, we treat available quantity as 0
        $availableQty = $inventory ? (float) $inventory->available_qty : 0.0;
        
        $allocatedTo = $lineItem->allocated_to_role ?? 'Unassigned';

        if ($availableQty >= $requiredQty) {
            // Condition A: Full Available Stock
            if ($inventory) {
                $inventory->available_qty = $availableQty - $requiredQty;
                $inventory->save();
            }

            // Create Material Allocation Record
            MaterialAllocation::create([
                'bom_header_id' => $bomHeader->id,
                'item_code' => $materialItemCode,
                'description' => $lineItem->description,
                'allocated_qty' => $requiredQty,
                'allocated_to' => $allocatedTo,
                'allocated_by' => 'System - Auto',
            ]);

            // Update Line Item Status
            $lineItem->status = 'in_stock';
            $lineItem->save();

        } elseif ($availableQty > 0 && $availableQty < $requiredQty) {
            // Condition B: Partial Available Stock
            $shortfallQty = $requiredQty - $availableQty;

            // Exhaust available stock
            if ($inventory) {
                $inventory->available_qty = 0.0;
                $inventory->save();
            }

            // Allocate whatever was available
            MaterialAllocation::create([
                'bom_header_id' => $bomHeader->id,
                'item_code' => $materialItemCode,
                'description' => $lineItem->description,
                'allocated_qty' => $availableQty,
                'allocated_to' => $allocatedTo,
                'allocated_by' => 'System - Auto',
            ]);

            // Generate Purchase Intent for the shortfall
            PurchaseIntent::create([
                'batch_id' => $batchId,
                'bom_header_id' => $bomHeader->id,
                'item_code' => $materialItemCode,
                'description' => $lineItem->description,
                'specification' => $lineItem->specification,
                'required_qty' => $requiredQty,
                'available_qty' => $availableQty,
                'shortfall_qty' => $shortfallQty,
                'priority' => $this->determinePriority($shortfallQty, $allocatedTo),
                'status' => 'pending',
            ]);

            // Update Line Item Status
            $lineItem->status = 'partial_stock';
            $lineItem->save();

        } else {
            // Condition C: Out of Stock
            $shortfallQty = $requiredQty;

            // Generate Purchase Intent for full target quantity
            PurchaseIntent::create([
                'batch_id' => $batchId,
                'bom_header_id' => $bomHeader->id,
                'item_code' => $materialItemCode,
                'description' => $lineItem->description,
                'specification' => $lineItem->specification,
                'required_qty' => $requiredQty,
                'available_qty' => 0.0,
                'shortfall_qty' => $shortfallQty,
                'priority' => $this->determinePriority($shortfallQty, $allocatedTo),
                'status' => 'pending',
            ]);

            // Update Line Item Status
            $lineItem->status = 'out_of_stock';
            $lineItem->save();
        }
    }

    /**
     * Smartly determine procurement priority based on role criticality and shortfall quantity.
     */
    private function determinePriority(float $shortfall, ?string $role): string
    {
        $roleLower = strtolower($role ?? '');
        
        // Critical departments get high priority immediately
        if (Str::contains($roleLower, ['flight', 'propulsion', 'aerospace', 'engine', 'defense', 'safety', 'executive'])) {
            return 'high';
        }

        // Quantity limits
        if ($shortfall >= 100.0) {
            return 'high';
        } elseif ($shortfall < 5.0) {
            return 'low';
        }

        return 'medium';
    }
}
