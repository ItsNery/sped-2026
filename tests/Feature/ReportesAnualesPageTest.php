<?php

namespace Tests\Feature;

use Tests\TestCase;

class ReportesAnualesPageTest extends TestCase
{
    public function test_renders_annual_reports_on_its_dedicated_page(): void
    {
        $response = $this->get(route('reportes-anuales'));

        $response->assertSeeText('Reportes anuales del SPED');
        $response->assertSeeText('Reporte Ejecutivo 2025');
        $response->assertSeeText('Reporte Ejecutivo 2024');
    }
}
