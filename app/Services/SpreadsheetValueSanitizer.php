<?php

namespace App\Services;

use PhpOffice\PhpSpreadsheet\Cell\Cell;
use PhpOffice\PhpSpreadsheet\Cell\StringValueBinder;

class SpreadsheetValueSanitizer
{
    public static function bindStrings(): void
    {
        Cell::setValueBinder(new StringValueBinder());
    }

    /**
     * @param array<int|string, mixed> $values
     * @return array<int|string, mixed>
     */
    public static function sanitizeCsvRow(array $values): array
    {
        return array_map(function ($value) {
            if (is_string($value) && preg_match('/^[=+\-@]/', $value)) {
                return "'{$value}";
            }

            return $value;
        }, $values);
    }
}
