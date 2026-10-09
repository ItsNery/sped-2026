<?php

namespace Database\Seeders\Support;

use PhpOffice\PhpSpreadsheet\IOFactory;
use RuntimeException;

final class Ped3InstitutionalManifest
{
    public static function load(): array
    {
        $filePath = public_path('Relacion nuevis derivados indicadr.ods');

        if (!is_file($filePath)) {
            throw new RuntimeException("No se encontro el manifiesto institucional: {$filePath}");
        }

        $rows = IOFactory::load($filePath)->getActiveSheet()->toArray(null, true, true, true);
        array_shift($rows);

        $programs = [];
        $relations = [];

        foreach ($rows as $row) {
            if (self::normalize((string) ($row['A'] ?? '')) !== 'institucional') {
                continue;
            }

            $sourceName = trim((string) ($row['B'] ?? ''));
            $indicatorName = trim((string) ($row['C'] ?? ''));

            if ($sourceName === '' || $indicatorName === '') {
                continue;
            }

            $programKey = self::programKey($sourceName);
            $indicatorKey = self::normalize($indicatorName);

            $programs[$programKey] ??= [
                'name' => self::buildProgramName($sourceName),
                'source_name' => $sourceName,
                'group' => self::classifyGroup($sourceName),
            ];

            $relationKey = $programKey . ':' . $indicatorKey;
            $relations[$relationKey] = [
                'program_key' => $programKey,
                'program_name' => $programs[$programKey]['name'],
                'indicator_key' => $indicatorKey,
                'indicator_name' => $indicatorName,
            ];
        }

        return [
            'file' => $filePath,
            'programs' => array_values($programs),
            'relations' => array_values($relations),
        ];
    }

    public static function programKey(string $value): string
    {
        $value = self::normalize($value);
        $value = preg_replace('/^programainstitucional(dela|del|de)/', '', $value);

        return $value;
    }

    public static function indicatorKey(string $value): string
    {
        return self::normalize($value);
    }

    private static function normalize(string $value): string
    {
        if (class_exists('Normalizer')) {
            $value = \Normalizer::normalize($value, \Normalizer::FORM_D);
            $value = preg_replace('/\p{Mn}/u', '', $value);
        }

        $value = mb_strtolower($value);

        return preg_replace('/[^a-z0-9]/', '', $value);
    }

    private static function buildProgramName(string $sourceName): string
    {
        $sourceName = trim(preg_replace('/\s+/', ' ', $sourceName));

        if (preg_match('/^programa institucional\b/i', $sourceName)) {
            return $sourceName;
        }

        $firstWord = self::normalize((string) strtok($sourceName, ' '));
        $delPrefixes = ['instituto', 'sistema', 'colegio', 'consejo', 'centro', 'comite', 'fideicomiso', 'fondo'];
        $dePrefixes = ['convenciones', 'carreteras', 'servicios', 'museos', 'capital'];
        $article = in_array($firstWord, $delPrefixes, true)
            ? 'del'
            : (in_array($firstWord, $dePrefixes, true) ? 'de' : 'de la');

        return "Programa Institucional {$article} {$sourceName}";
    }

    private static function classifyGroup(string $sourceName): string
    {
        $name = mb_strtolower(trim($sourceName));

        return str_starts_with($name, 'secretaría ')
            || str_starts_with($name, 'consejería jurídica')
            || str_starts_with($name, 'coordinación general')
            ? 'Secretarías'
            : 'Organismos Auxiliares';
    }
}
