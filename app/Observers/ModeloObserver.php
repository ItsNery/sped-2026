<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;

class ModeloObserver
{
    public function created(Model $model)
    {
        $this->registrarCambio($model, 'creado');
    }

    public function updated(Model $model)
    {
        $this->registrarCambio($model, 'actualizado');
    }

    public function deleted(Model $model)
    {
        $this->registrarCambio($model, 'eliminado');
    }

    private function registrarCambio(Model $model, string $accion)
    {
        $usuarioModel = Auth::user();
        $usuario = $usuarioModel?->name ?? 'Sistema';
        $tabla = $model->getTable();
        $id = $model->getKey();
        $contexto = app()->runningInConsole() ? null : request();
        $base = [
            'usuario_id' => $usuarioModel?->getAuthIdentifier(),
            'usuario' => $usuario,
            'tabla' => $tabla,
            'registro_id' => $id,
            'ip' => $contexto?->ip(),
            'user_agent' => $contexto?->userAgent(),
            'request_id' => $contexto?->header('X-Request-ID') ?: (string) Str::uuid(),
            'created_at' => now(),
            'updated_at' => now(),
        ];

        if (in_array($tabla, ['indicadors', 'datos_anuales', 'programa_institucional_indicador'], true)) {
            Cache::forever('ped_metrics_version', (int) Cache::get('ped_metrics_version', 1) + 1);
        }

        if ($accion === 'creado') {
            DB::table('logs_cambios')->insert($base + [
                'columna' => null,
                'accion' => 'creado',
                'valor_nuevo' => $this->serializar($model->getAttributes()),
            ]);
            return;
        }

        if ($accion === 'eliminado') {
            DB::table('logs_cambios')->insert($base + [
                'columna' => null,
                'accion' => 'eliminado',
                'valor_anterior' => $this->serializar($model->getAttributes()),
            ]);
            return;
        }

        $cambios = $model->getChanges();
        $ignoradas = ['updated_at', 'created_at'];
        foreach ($cambios as $columna => $valorNuevo) {
            if (in_array($columna, $ignoradas, true)) {
                continue;
            }

            DB::table('logs_cambios')->insert($base + [
                'columna' => $columna,
                'accion' => 'actualizado',
                'valor_anterior' => $this->serializar($model->getOriginal($columna)),
                'valor_nuevo' => $this->serializar($valorNuevo),
            ]);
        }
    }

    private function serializar(mixed $valor): ?string
    {
        if ($valor === null) {
            return null;
        }

        if (is_bool($valor)) {
            return $valor ? '1' : '0';
        }

        if (is_scalar($valor)) {
            return (string) $valor;
        }

        return json_encode($valor, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
