<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;

$filePath = "C:\\Users\\nery.pozos\\Desktop\\SPEd Nuevos inst\\Relacion Indic-Institu.xlsx";

try {
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    $spreadsheet = $reader->load($filePath);
    $sheet = $spreadsheet->getSheetByName("Lista repetidos");
    if (!$sheet) {
        // Fallback to active sheet if not found
        $sheet = $spreadsheet->getActiveSheet();
        echo "Sheet 'Lista repetidos' not found, loaded active sheet instead.\n";
    } else {
        echo "Loaded sheet 'Lista repetidos' successfully.\n";
    }
    
    $rows = $sheet->toArray();
    echo "Total rows: " . count($rows) . "\n\n";
    
    // Print first 15 rows
    for ($i = 0; $i < min(15, count($rows)); $i++) {
        $row = $rows[$i];
        echo "Row " . ($i + 1) . ":\n";
        echo "  A: " . ($row[0] ?? '') . "\n";
        echo "  B: " . ($row[1] ?? '') . "\n";
        echo "----------------------------------------\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
