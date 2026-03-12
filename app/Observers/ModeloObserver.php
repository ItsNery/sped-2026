<?php

namespace App\Observers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

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
        $usuario = Auth::check() ? Auth::user()->name : 'Desconocido';
        $tabla = $model->getTable();
        $id = $model->getKey(); // Obtiene el ID del registro

        // 🚀 Caso 1: CREACIÓN → Solo un registro general
        if ($accion === 'creado') {
            DB::table('logs_cambios')->insert([
                'usuario' => $usuario,
                'tabla' => $tabla,
                'columna' => null, // No hay columnas específicas
                'accion' => "Registro ID $id creado por el usuario '$usuario'",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        // 🚀 Caso 2: ELIMINACIÓN → Solo un registro general
        if ($accion === 'eliminado') {
            DB::table('logs_cambios')->insert([
                'usuario' => $usuario,
                'tabla' => $tabla,
                'columna' => null, // No hay columnas específicas
                'accion' => "Registro ID $id eliminado por el usuario '$usuario'",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            return;
        }

        // 🚀 Caso 3: ACTUALIZACIÓN → Solo las columnas que cambiaron
        $cambios = $model->getChanges(); // Obtiene solo los campos modificados
        foreach ($cambios as $columna => $valorNuevo) {
            DB::table('logs_cambios')->insert([
                'usuario' => $usuario,
                'tabla' => $tabla,
                'columna' => $columna,
                'accion' => "El usuario '$usuario' modificó el registro con ID $id: columna '$columna' modificada",
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
