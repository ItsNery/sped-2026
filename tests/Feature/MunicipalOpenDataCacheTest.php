<?php

namespace Tests\Feature;

use App\Models\CatMunicipio;
use App\Models\MunicipioConvenio;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class MunicipalOpenDataCacheTest extends TestCase
{
    public function test_municipal_open_data_renders_a_serialized_cached_collection(): void
    {
        config()->set('cache.stores.array.serialize', true);
        Cache::purge('array');

        $municipality = new CatMunicipio(['nombre' => 'Municipio de prueba']);
        $municipality->id = 1;
        $agreement = new MunicipioConvenio();
        $agreement->id = 1;
        $agreement->id_municipio = 1;
        $agreement->setRelation('municipio', $municipality);
        Cache::put('datos_abiertos:municipios', (new MunicipioConvenio())->newCollection([$agreement]), 600);

        $this->get('/datos-abiertos-mun')
            ->assertOk()
            ->assertSee('Municipio de prueba')
            ->assertSee('municipio-de-prueba-tab');
    }
}
