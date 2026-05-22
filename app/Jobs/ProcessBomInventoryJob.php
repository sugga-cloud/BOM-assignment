<?php

namespace App\Jobs;

use App\Models\BomHeader;
use App\Services\InventoryManagementService;
use App\Services\AuditService;
use App\Notifications\PurchaseDeptNotification;
use App\Notifications\MaterialAllocatedNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Throwable;

class ProcessBomInventoryJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private int $bomHeaderId;
    private string $batchId;

    /**
     * Create a new job instance.
     */
    public function __construct(int $bomHeaderId, string $batchId)
    {
        $this->bomHeaderId = $bomHeaderId;
        $this->batchId = $batchId;
    }

    /**
     * Execute the job.
     */
    public function handle(InventoryManagementService $inventoryService): void
    {
        $bomHeader = BomHeader::findOrFail($this->bomHeaderId);

        try {
            DB::beginTransaction();

            // Loop and process each pending line item sequentially
            foreach ($bomHeader->lineItems as $lineItem) {
                // Skip assembly section headers — they carry no quantity or inventory data
                if ($lineItem->is_assembly_header) {
                    continue;
                }
                $inventoryService->processLineItem($bomHeader, $lineItem, $this->batchId);
            }

            // Mark BOM processing as complete
            $bomHeader->status = 'completed';
            $bomHeader->save();

            // Audit Log Ingress Success
            AuditService::log(
                'BOM_INVENTORY_MATCHING_SUCCESS',
                "BOM version '{$bomHeader->version}' for project ID {$bomHeader->project_id} successfully parsed and stock-allocated.",
                [
                    'bom_header_id' => $bomHeader->id,
                    'batch_id' => $this->batchId,
                    'line_items_count' => $bomHeader->lineItems()->count(),
                    'allocated_items' => $bomHeader->materialAllocations()->count(),
                    'purchase_intents' => $bomHeader->purchaseIntents()->count(),
                ],
                $bomHeader->uploaded_by
            );

            DB::commit();

            // Dispatch notification pipelines (Log-based mailers, fully secure and resilient)
            try {
                $purchases = $bomHeader->purchaseIntents;
                if ($purchases->isNotEmpty()) {
                    // Send notification to procurement team
                    $bomHeader->uploadedBy->notify(new PurchaseDeptNotification($bomHeader, $this->batchId));
                }

                $allocations = $bomHeader->materialAllocations;
                if ($allocations->isNotEmpty()) {
                    // Send notification to allocating departments
                    $bomHeader->uploadedBy->notify(new MaterialAllocatedNotification($bomHeader));
                }
            } catch (Throwable $notifEx) {
                // Keep job from failing if notifications fail
                Log::warning("Notification dispatch failed: " . $notifEx->getMessage());
            }

        } catch (Throwable $e) {
            DB::rollBack();

            // Set BOM processing as failed
            $bomHeader->status = 'failed';
            $bomHeader->save();

            // Audit Log Ingress Failure
            AuditService::log(
                'BOM_INVENTORY_MATCHING_FAILED',
                "Critical transaction rollback: BOM processing failed for version '{$bomHeader->version}' of project ID {$bomHeader->project_id}. Error: " . $e->getMessage(),
                [
                    'bom_header_id' => $bomHeader->id,
                    'batch_id' => $this->batchId,
                    'error_trace' => $e->getTraceAsString(),
                ],
                $bomHeader->uploaded_by
            );

            Log::error("BOM Ingress processing exception: " . $e->getMessage());
            throw $e;
        }
    }
}
