<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class AuditLogger
{
    public function recordUpdate(
        Model $model,
        string $column,
        mixed $oldValue,
        mixed $newValue,
        string $action,
        ?string $reason = null
    ): void {
        $user = Auth::user();
        $request = app()->runningInConsole() ? null : request();

        DB::table('logs_cambios')->insert([
            'usuario_id' => $user?->getAuthIdentifier(),
            'usuario' => $user?->name ?? 'Sistema',
            'tabla' => $model->getTable(),
            'registro_id' => $model->getKey(),
            'columna' => $column,
            'accion' => $action,
            'valor_anterior' => $this->serialize($oldValue),
            'valor_nuevo' => $this->serialize($newValue),
            'ip' => $request?->ip(),
            'user_agent' => $request?->userAgent(),
            'request_id' => $request?->header('X-Request-ID') ?: (string) Str::uuid(),
            'motivo' => $reason,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if (in_array($model->getTable(), ['indicadors', 'datos_anuales', 'programa_institucional_indicador'], true)) {
            Cache::forever('ped_metrics_version', (int) Cache::get('ped_metrics_version', 1) + 1);
        }
    }

    private function serialize(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        if (is_bool($value)) {
            return $value ? '1' : '0';
        }

        if (is_scalar($value)) {
            return (string) $value;
        }

        return json_encode($value, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
    }
}
