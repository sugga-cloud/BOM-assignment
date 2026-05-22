<?php

require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Maatwebsite\Excel\Facades\Excel;

try {
    $filePath = __DIR__ . '/BOM REV.1.xls';

    $data = Excel::toArray(new class implements \Maatwebsite\Excel\Concerns\ToArray {
        public function array(array $array) { return $array; }
    }, $filePath);

    echo "Total Sheets: " . count($data) . "\n\n";

    foreach ($data as $sheetIndex => $sheet) {
        echo "=== SHEET $sheetIndex (Total rows: " . count($sheet) . ") ===\n";
        foreach ($sheet as $rowIdx => $row) {
            // Only show non-empty rows
            $filtered = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (!empty($filtered)) {
                echo "  Row " . ($rowIdx + 1) . ": " . json_encode($row, JSON_UNESCAPED_UNICODE) . "\n";
            }
        }
        echo "\n";
    }

} catch (\Throwable $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo $e->getTraceAsString() . "\n";
}
