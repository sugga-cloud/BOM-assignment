<?php

namespace App\Services;

use App\Imports\BomImport;
use App\Models\BomHeader;
use App\Models\BomLineItem;
use App\Jobs\ProcessBomInventoryJob;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Log;

class BomImportService
{
    /**
     * Parse the uploaded BOM file, validate its structure, bulk-insert records,
     * and dispatch the background inventory queue worker.
     *
     * Supports the real BOM REV.1.xls format where:
     *   - Rows 1-7 are document metadata
     *   - Row 8 is the column header row
     *   - Rows 9+ are actual BOM data rows
     */
    public function import(UploadedFile $file, int $projectId, string $version, int $userId): BomHeader
    {
        // 1. Read the raw sheet (BomImport skips the first 8 header/metadata rows)
        $import = new BomImport();
        Excel::import($import, $file);

        $rawRows = $import->getRows();

        if ($rawRows->isEmpty()) {
            throw new Exception("The uploaded spreadsheet is empty or contains no data rows after the header block.");
        }

        // 2. Parse every raw row through BomImport::parseRow()
        $parsedRows = $rawRows
            ->map(fn($row) => BomImport::parseRow(is_array($row) ? $row : $row->toArray()))
            ->filter() // remove nulls (completely blank rows)
            ->values();

        if ($parsedRows->isEmpty()) {
            throw new Exception("No valid BOM line items could be extracted from the uploaded file.");
        }

        // 3. Structural validation: ensure we have at least one row with a part number
        $hasValidRows = $parsedRows->contains(fn($r) => !empty($r['part_no']));
        if (!$hasValidRows) {
            throw new Exception("Structural Validation Failure: The uploaded file does not appear to contain valid BOM rows. Ensure it follows the BOM REV.1 format with PART NO., PART DISCRIPTION, QTY., and UNIT columns.");
        }

        // Generate tracking batch UUID
        $batchId = (string) Str::uuid();

        // 4. Persist structures inside an atomic database transaction
        return DB::transaction(function () use ($projectId, $version, $userId, $parsedRows, $batchId) {
            // Create the immutable BOM Header
            $bomHeader = BomHeader::create([
                'project_id'  => $projectId,
                'version'     => $version,
                'status'      => 'processing',
                'uploaded_by' => $userId,
            ]);

            // Map parsed rows to DB insert arrays
            $lineItemsData = [];
            $now = now();
           
            foreach ($parsedRows as $row) {
                $lineItemsData[] = [
                    'bom_header_id'         => $bomHeader->id,
                    'part_no'               => $row['part_no'],
                    'part_code'             => $row['part_code'],
                    'item_code'             => $row['item_code'],
                    'description'           => $row['description'],
                    'specification'         => $row['specification'],
                    'size_of_material'      => $row['size_of_material'],
                    'required_qty'          => $row['required_qty'],
                    'uom'                   => $row['uom'],
                    'purchase_tech_spec_no' => $row['purchase_tech_spec_no'],
                    'stock_verification'    => $row['stock_verification'],
                    'remarks'               => $row['remarks'],
                    'allocated_to_role'     => $row['allocated_to_role'],
                    'is_critical'           => $row['is_critical'] ? 1 : 0,
                    'is_assembly_header'    => $row['is_assembly_header'] ? 1 : 0,
                    'status'                => $row['status'],
                    'created_at'            => $now,
                    'updated_at'            => $now,
                ];
            }

            // Bulk insert all line items in a single query
            BomLineItem::insert($lineItemsData);

            // Audit log the ingestion event
            AuditService::log(
                'BOM_UPLOAD_INITIALIZED',
                "BOM version '{$version}' uploaded for project ID {$projectId}. {$parsedRows->count()} rows parsed.",
                [
                    'bom_header_id'      => $bomHeader->id,
                    'batch_id'           => $batchId,
                    'total_rows'         => $parsedRows->count(),
                    'assembly_headers'   => $parsedRows->where('is_assembly_header', true)->count(),
                    'critical_items'     => $parsedRows->where('is_critical', true)->count(),
                    'procurement_items'  => $parsedRows->where('is_assembly_header', false)->count(),
                ],
                $userId
            );

            // Dispatch async inventory processing job
            ProcessBomInventoryJob::dispatch($bomHeader->id, $batchId);

            return $bomHeader;
        });
    }
}
