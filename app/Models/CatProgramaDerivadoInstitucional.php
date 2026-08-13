<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CatProgramaDerivadoInstitucional extends Model
{
    use HasFactory;
    protected $table = 'cat_programas_derivados_institucionales';

    protected $fillable = [
        'nombre',
        'grupo',
        'siglas',
        'imagen',
        'descripcion',
        'color',
        'icono',
        'plan_estatal',
        'documento',
    ];

    /**
     * Relación con el Plan Estatal de Desarrollo.
     * Un programa derivado especial pertenece a un plan estatal.
     */
    public function catPlanEstatalDesarrollo()
    {
        return $this->belongsTo(CatPlanEstatalDesarrollo::class, 'plan_estatal');
    }

    /**
     * Relación muchos a muchos: Un programa institucional puede tener varios indicadores.
     */
    public function indicadores()
    {
        return $this->belongsToMany(
            Indicador::class,
            'programa_institucional_indicador',
            'programa_institucional_id',
            'indicador_id'
        )->withTimestamps();
    }

    /**
     * Accesor para obtener las siglas oficiales o autogenerarlas a partir del nombre.
     */
    public function getSiglasAttribute($value)
    {
        if (!empty($value)) {
            return $value;
        }

        // Generar siglas automáticas descartando palabras comunes
        $nombre = preg_replace('/^Programa Institucional (del|de la|de|al servicio de los poderes del estado de puebla)/i', '', $this->nombre);
        $nombre = trim($nombre);

        $stopwords = ['de', 'la', 'el', 'y', 'los', 'del', 'para', 'al', 'en', 'con', 'por', 'sobre', 'servicio', 'poderes', 'estado', 'puebla'];
        $words = explode(' ', $nombre);
        $siglas = '';

        foreach ($words as $word) {
            $wordLimpia = preg_replace('/[^a-zA-ZáéíóúÁÉÍÓÚ]/', '', $word);
            if (empty($wordLimpia) || in_array(strtolower($wordLimpia), $stopwords)) continue;
            
            if (ctype_upper($wordLimpia[0]) || strlen($wordLimpia) > 3) {
                $siglas .= mb_substr($wordLimpia, 0, 1);
            }
        }

        return empty($siglas) ? mb_strtoupper(mb_substr($nombre, 0, 3)) : mb_strtoupper($siglas);
    }
}
