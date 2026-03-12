<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Indicador;
use App\Models\DatoAnual;

class SyncBaselineToDataAnual extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'indicador:sync-baseline {--dry-run : Simulate the process without saving changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sincroniza el dato de línea base de los indicadores a la tabla de datos anuales si no existe.';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $dryRun = $this->option('dry-run');

        if ($dryRun) {
            $this->warn('--- MODO SIMULACIÓN (DRY RUN) ---');
        }

        $indicadores = Indicador::whereNotNull('linea_base')
            ->whereNotNull('dato_linea_base')
            ->get();

        $this->info("Procesando " . $indicadores->count() . " indicadores...");

        $creados = 0;
        $existentes = 0;
        $errores = 0;

        foreach ($indicadores as $indicador) {
            // Limpiar el valor de la línea base (por seguridad)
            $valorLB = $indicador->dato_linea_base;
            $anioLB = (int)$indicador->linea_base;

            // Verificar si ya existe un registro para ese año e indicador
            $existe = DatoAnual::where('id_indicador', $indicador->id)
                ->where('anio', $anioLB)
                ->exists();

            if ($existe) {
                $this->line("- [Omitido] {$indicador->nombre}: El dato para el año {$anioLB} ya existe.");
                $existentes++;
                continue;
            }

            $this->info("- [A crear] {$indicador->nombre}: Registrando valor {$valorLB} para el año {$anioLB}.");

            if (!$dryRun) {
                try {
                    DatoAnual::create([
                        'id_indicador' => $indicador->id,
                        'anio' => $anioLB,
                        'valor_dato' => $valorLB,
                        'validado' => true,
                        'modificado' => false,
                    ]);
                    $creados++;
                } catch (\Exception $e) {
                    $this->error("  Error al crear registro para ID {$indicador->id}: " . $e->getMessage());
                    $errores++;
                }
            } else {
                $creados++; // Contabilizar como "simulado creado"
            }
        }

        $this->newLine();
        $this->info("Resumen:");
        $this->line("- Registros " . ($dryRun ? 'por crear' : 'creados') . ": $creados");
        $this->line("- Registros ya existentes: $existentes");
        if ($errores > 0) {
            $this->error("- Errores encontrados: $errores");
        }

        return 0;
    }
}
