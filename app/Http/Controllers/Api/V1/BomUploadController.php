<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\BomUploadRequest;
use App\Models\BomHeader;
use App\Models\Project;
use App\Models\Inventory;
use App\Models\MaterialAllocation;
use App\Models\PurchaseIntent;
use App\Models\AuditTrail;
use App\Services\BomImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Throwable;

class BomUploadController extends Controller
{
    private BomImportService $importService;

    public function __construct(BomImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Handle the BOM spreadsheet upload.
     */
    public function upload(BomUploadRequest $request): JsonResponse
    {
        // Resiliently resolve authenticated user (fallback to ID 1 for testing)
        $userId = Auth::id() ?? 1;

        try {
            $bomHeader = $this->importService->import(
                $request->file('file'),
                (int) $request->project_id,
                $request->version,
                $userId
            );

            return response()->json([
                'success' => true,
                'message' => 'BOM file ingested successfully. Analysis is now processing in the background.',
                'data' => [
                    'bom_header_id' => $bomHeader->id,
                    'project_id' => $bomHeader->project_id,
                    'version' => $bomHeader->version,
                    'status' => $bomHeader->status,
                    'status_url' => route('api.bom.status', $bomHeader->id),
                ]
            ], 202);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Poll the queue processing state of a BOM header.
     */
    public function status(int $id): JsonResponse
    {
        $bomHeader = BomHeader::with(['project', 'lineItems', 'materialAllocations', 'purchaseIntents'])->find($id);

        if (!$bomHeader) {
            return response()->json([
                'success' => false,
                'message' => 'BOM Header record not found.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $bomHeader->id,
                'project_name' => $bomHeader->project->name,
                'version' => $bomHeader->version,
                'status' => $bomHeader->status,
                'line_items_count' => $bomHeader->lineItems->count(),
                'allocated_count' => $bomHeader->materialAllocations->count(),
                'intent_count' => $bomHeader->purchaseIntents->count(),
                'allocations' => $bomHeader->materialAllocations,
                'shortfalls' => $bomHeader->purchaseIntents,
                'created_at' => $bomHeader->created_at->toIso8601String(),
                'updated_at' => $bomHeader->updated_at->toIso8601String(),
            ]
        ]);
    }

    /**
     * Fetch complete dashboard metrics to power real-time stats and visual widgets.
     */
    public function metrics(): JsonResponse
    {
        $projectsCount = Project::count();
        $inventoryCount = Inventory::count();
        
        $totalAllocations = MaterialAllocation::sum('allocated_qty');
        $totalShortfalls = PurchaseIntent::sum('shortfall_qty');
        
        $recentHeaders = BomHeader::with('project')
            ->orderBy('id', 'desc')
            ->take(5)
            ->get()
            ->map(function ($h) {
                return [
                    'id' => $h->id,
                    'project_name' => $h->project->name,
                    'version' => $h->version,
                    'status' => $h->status,
                    'created_at' => $h->created_at->diffForHumans(),
                ];
            });

        $recentAudits = AuditTrail::orderBy('id', 'desc')
            ->take(10)
            ->get()
            ->map(function ($a) {
                return [
                    'id' => $a->id,
                    'action' => $a->action,
                    'description' => $a->description,
                    'payload' => $a->payload,
                    'created_at' => $a->created_at->format('H:i:s'),
                ];
            });

        // Group allocations by target roles for graphical badges
        $allocationsByRole = MaterialAllocation::select('allocated_to', \DB::raw('SUM(allocated_qty) as total'))
            ->groupBy('allocated_to')
            ->get();

        // Get active shortfalls grouped by priorities
        $shortfallsByPriority = PurchaseIntent::select('priority', \DB::raw('count(*) as count'))
            ->groupBy('priority')
            ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'projects_count' => $projectsCount,
                'inventory_count' => $inventoryCount,
                'total_allocations' => round($totalAllocations, 2),
                'total_shortfalls' => round($totalShortfalls, 2),
                'recent_uploads' => $recentHeaders,
                'recent_audits' => $recentAudits,
                'allocations_by_role' => $allocationsByRole,
                'shortfalls_by_priority' => $shortfallsByPriority,
            ]
        ]);
    }
}
