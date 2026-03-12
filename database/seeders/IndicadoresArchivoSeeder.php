<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class IndicadoresArchivoSeeder extends Seeder
{
    public function run()
    {
        // Desactivar restricciones de clave foránea temporalmente
        Schema::disableForeignKeyConstraints();

        // Copiar los datos de indicadors a 2019_2024_indicadors manteniendo los IDs
        $indicadors = DB::table('indicadors')->get()->map(function ($indicador) {
            return (array) $indicador; // Conserva los IDs
        })->toArray();

        // Dividir los datos en lotes de 1000 e insertar
        foreach (array_chunk($indicadors, 1000) as $chunk) {
            DB::table('2019_2024_indicadors')->insert($chunk);
        }

        // Obtener los IDs de los indicadores copiados
        $indicadorIds = DB::table('2019_2024_indicadors')->pluck('id')->toArray();

        // Copiar los datos de datos_anuales_indicadores a 2019_2024_datos_anuales_indicadores manteniendo los IDs
        $datosAnuales = DB::table('datos_anuales_indicadores')
            ->whereIn('id_indicador', $indicadorIds)
            ->get()
            ->map(function ($dato) {
                $dato = (array) $dato; // Convertir a array

                // Revisar si alguna fecha tiene '0000-00-00' y convertirla a NULL
                foreach ($dato as $key => $value) {
                    if (strpos($key, 'fecha_actualizacion_') === 0 && $value === '0000-00-00') {
                        $dato[$key] = null;
                    }
                }

                return $dato;
            })
            ->toArray();

        // Dividir los datos en lotes de 1000 e insertar
        foreach (array_chunk($datosAnuales, 1000) as $chunk) {
            DB::table('2019_2024_datos_anuales_indicadores')->insert($chunk);
        }

        // Copiar los datos de indicadors_ods a 2019_2024_indicadors_ods manteniendo los IDs
        $indicadorsOds = DB::table('indicador_ods')->get()->map(function ($ods) {
            return (array) $ods;
        })->toArray();

        // Dividir los datos en lotes de 1000 e insertar
        foreach (array_chunk($indicadorsOds, 1000) as $chunk) {
            DB::table('2019_2024_indicadors_ods')->insert($chunk);
        }

        // Vaciar las tablas originales después de la copia
        DB::table('carrusel_indicadors')->truncate(); // Asegúrate de que esta tabla esté vacía
        DB::table('indicadors')->truncate();
        DB::table('datos_anuales_indicadores')->truncate();
        DB::table('indicador_ods')->truncate();

        // Reactivar restricciones de clave foránea
        Schema::enableForeignKeyConstraints();
    }
}
