<?php

namespace App\Imports;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;

/**
 * BomImport — Reads raw rows from any BOM XLS/XLSX/CSV.
 *
 * The real BOM REV.1.xls layout:
 *   Rows 1-7  : Document metadata (title, customer, work-order, dates, etc.)
 *   Row 8     : Column headers → PART NO. | PART DISCRIPTION | PART CODE |
 *                                MATERIAL SPECIFICATION | SIZE OF MATERIAL |
 *                                QTY. | UNIT | PURCHASE TECHNICAL SPECIFICATION No. |
 *                                STOCK VERIFICATION YES/NO | REMARKS
 *   Rows 9+   : Actual BOM line data
 *
 * We implement ToCollection (NOT WithHeadingRow) so we control row parsing fully.
 */
use Maatwebsite\Excel\Concerns\WithCalculatedFormulas;

class BomImport implements ToCollection, WithCalculatedFormulas
{
    /**
     * Number of leading rows to skip (metadata rows before the real header row).
     * Row 8 is the header, so we skip rows 1-8 (indices 0-7) = 8 rows total.
     */
    private const SKIP_ROWS = 8;

    private Collection $rows;

    public function __construct()
    {
        $this->rows = collect();
    }

    /**
     * Receive the full raw sheet as a collection of arrays and slice off the header block.
     */
    public function collection(Collection $rows): void
    {
        // Skip the first SKIP_ROWS rows (metadata + header row)
        $this->rows = $rows->slice(self::SKIP_ROWS)->values();
    }

    /**
     * Get the parsed data rows (after the header block).
     */
    public function getRows(): Collection
    {
        return $this->rows;
    }

    /**
     * Parse a single raw row array into a structured associative array.
     * Returns null if the row is completely empty (should be skipped).
     *
     * Column indices (0-based):
     *   0  → PART NO.
     *   1  → PART DISCRIPTION
     *   2  → PART CODE
     *   3  → MATERIAL SPECIFICATION
     *   4  → SIZE OF MATERIAL
     *   5  → QTY.
     *   6  → UNIT
     *   7  → PURCHASE TECHNICAL SPECIFICATION No.
     *   8  → STOCK VERIFICATION YES/NO
     *   9  → REMARKS
     */
    public static function parseRow(array $row): ?array
    {
        // Pad row to at least 10 columns to avoid index errors
        $row = array_pad($row, 10, null);
    Log::info('Line Item Object', [
            'line_item' => $row
        ]);
        $rawPartNo  = isset($row[0]) ? trim((string) $row[0]) : '';
        $description = isset($row[1]) ? trim((string) $row[1]) : '';

        // Skip completely blank rows
        if ($rawPartNo === '' && $description === '' && !$row[5]) {
            return null;
        }

        // Detect critical/sensitive flag (part_no prefixed with *)
        $isCritical = str_starts_with($rawPartNo, '*');
        $partNo     = ltrim($rawPartNo, '*');

        $partCode     = isset($row[2]) ? trim((string) $row[2]) : null;
        $specification = isset($row[3]) ? trim((string) $row[3]) : null;
        $sizeMaterial = isset($row[4]) ? trim((string) $row[4]) : null;

        $rawQty = $row[5];
        $qty    = ($rawQty !== null && $rawQty !== '') ? (float) $rawQty : null;

        $unit           = isset($row[6]) ? trim((string) $row[6]) : null;
        $ptSpecNo       = isset($row[7]) ? trim((string) $row[7]) : null;
        $stockVerify    = isset($row[8]) ? trim((string) $row[8]) : null;
        $remarks        = isset($row[9]) ? trim((string) $row[9]) : null;

        // Assembly header rows have no quantity (they are section parents like "SHELL ASSEMBLY")
        $isAssemblyHeader = ($qty === null);

        // item_code for inventory matching: prefer part_code, fall back to part_no
        $itemCode = (!empty($partCode) && $partCode !== '') ? $partCode : $partNo;

        return [
            'part_no'              => $partNo ?: null,
            'part_code'            => (!empty($partCode)) ? $partCode : null,
            'item_code'            => (!empty($itemCode)) ? $itemCode : null,
            'description'          => (!empty($description)) ? $description : null,
            'specification'        => (!empty($specification)) ? $specification : null,
            'size_of_material'     => (!empty($sizeMaterial)) ? $sizeMaterial : null,
            'required_qty'         => $qty,
            'uom'                  => (!empty($unit)) ? $unit : null,
            'purchase_tech_spec_no'=> (!empty($ptSpecNo)) ? $ptSpecNo : null,
            'stock_verification'   => (!empty($stockVerify)) ? $stockVerify : null,
            'remarks'              => (!empty($remarks)) ? $remarks : null,
            'allocated_to_role'    => null,
            'is_critical'          => $isCritical,
            'is_assembly_header'   => $isAssemblyHeader,
            'status'               => $isAssemblyHeader ? 'assembly_header' : 'pending',
        ];
    }
}
