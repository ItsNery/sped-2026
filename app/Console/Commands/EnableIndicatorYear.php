<?php

namespace App\Console\Commands;

use App\Models\DatoAnual;
use App\Models\Indicador;
use App\Services\ActivePlanResolver;
use Illuminate\Console\Command;

class EnableIndicatorYear extends Command
{
    protected $signature = 'sped:enable-indicator-year
        {year : Año que se habilitará para los indicadores del PED activo}
        {--execute : Crear los registros; sin esta opción solamente se muestra el alcance}';

    protected $description = 'Habilita un año de captura para los indicadores del PED activo sin sobrescribir datos existentes.';

    public function handle(ActivePlanResolver $activePlan): int
    {
        $year = (int) $this->argument('year');
        $planId = $activePlan->id();
        $indicatorIds = Indicador::forPlan($planId)
            ->whereDoesntHave('datosAnuales', fn ($query) => $query->where('anio', $year))
            ->pluck('id');

        $this->line('Plan activo: ' . $planId);
        $this->line('Año: ' . $year);
        $this->line('Indicadores sin registro: ' . $indicatorIds->count());

        if (!$this->option('execute')) {
            $this->warn('Modo DRY-RUN: no se crearon registros anuales.');

            return self::SUCCESS;
        }

        $created = 0;
        $now = now();

        foreach ($indicatorIds->chunk(500) as $ids) {
            $created += DatoAnual::query()->insertOrIgnore(
                $ids->map(fn (int $indicatorId) => [
                    'id_indicador' => $indicatorId,
                    'anio' => $year,
                    'validado' => false,
                    'modificado' => false,
                    'created_at' => $now,
                    'updated_at' => $now,
                ])->all()
            );
        }

        $this->info('Registros anuales creados: ' . $created);

        return self::SUCCESS;
    }
}
