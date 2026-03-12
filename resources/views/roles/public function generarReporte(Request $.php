public function generarReporte(Request $request)
    {
        function agruparPorCategoria($resultados)
        {
            $grupos = array();

            foreach ($resultados as $fila) {
                $categoria = $fila['categorizacion'];

                if (!array_key_exists($categoria, $grupos)) {
                    $grupos[$categoria] = array();
                }

                $grupos[$categoria][] = $fila;
            }

            return $grupos;
        }

        $persona = Personal::findOrFail($request->id_personal);
        $inventario = Inventario::findOrFail($request->id_inventario);

        $rutaPlantilla = public_path('docs/plantilla.xls');

        $spreadsheet = IOFactory::load($rutaPlantilla);
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('B7', $persona->nombre);
        $sheet->setCellValue('B8', $persona->puesto);
        $sheet->setCellValue('B9', 'Secretaría de Planeación y Finanzas');
        $direccion = "direccion";

        if ($persona->area == 'DEI')
            $direccion = 'Dirección de Estadistica e Información';
        elseif ($persona->area == 'DPD')
            $direccion = 'Dirección de Planeación y Desarrollo';
        elseif ($persona->area == 'DEV')
            $direccion = 'Dirección de Evalución';
        elseif ($persona->area == 'Staff')
            $direccion = 'Staff';


        $sheet->setCellValue('B10', $direccion);
        $sheet->setCellValue('B11', $persona->area_oficial);
        $sheet->setCellValue('B12', $persona->expediente);
        $sheet->setCellValue('B13', now()->format('Y-m-d')); // Fecha actual

        $filaActual = 17;
        $columnaDescripcion = 'A';
        $columnaSerie = 'B';
        $columnaMarca = 'C';
        $columnaCodigo = 'D';
        $columnaColor = 'E';
        $columnaEstado = 'F';
        $columnaPG = 'G';
        $columnaPE = 'H';
        $columnaObservaciones = 'I';


        $recursos = Inventario::select(
            'recurso_materials.serie',
            'recurso_materials.descripcion',
            'recurso_materials.codigo',
            'recurso_materials.marca',
            'recurso_materials.estado',
            'recurso_materials.color',
            'recurso_materials.observaciones',
            'recurso_materials.partida',
            'recurso_materials.categorizacion'
        )->join('inventario_recursos', 'inventario_recursos.id_inventario', '=', 'inventarios.id')
            ->join('recurso_materials', 'recurso_materials.id', '=', 'inventario_recursos.id_recursos')
            ->where('inventario_recursos.estatus', 1)
            ->orderBy('recurso_materials.categorizacion', 'asc')
            ->get()
            ->toArray();

        $grupos_recursos = agruparPorCategoria($recursos);

        foreach ($grupos_recursos as $index => $recurso) {

            $sheet->setCellValue("A{$filaActual}", $index);
            $filaActual = $filaActual + 3;
            $Numrecursos = count($recurso);
            $sheet->insertNewRowBefore($filaActual, $Numrecursos);

            foreach ($recurso as $recurso_int) {
                $sheet->setCellValue("{$columnaSerie}{$filaActual}", $recurso_int['serie']);
                $sheet->setCellValue("{$columnaDescripcion}{$filaActual}", $recurso_int['descripcion']);
                $sheet->setCellValue("{$columnaMarca}{$filaActual}", $recurso_int['marca']);
                $sheet->setCellValue("{$columnaCodigo}{$filaActual}", $recurso_int['codigo']);
                $sheet->setCellValue("{$columnaColor}{$filaActual}", $recurso_int['color']);
                $sheet->setCellValue("{$columnaEstado}{$filaActual}", $recurso_int['estado']);
                $sheet->setCellValue("{$columnaPG}{$filaActual}", '');
                $sheet->setCellValue("{$columnaPE}{$filaActual}", $recurso_int['partida']);
                $sheet->setCellValue("{$columnaObservaciones}{$filaActual}", $recurso_int['observaciones']);
                $filaActual=$filaActual+1;
            }
            $filaActual=$filaActual+1;
        }

        $nombreArchivo = "reporte_{$persona->id}.xlsx";
        $rutaSalida = public_path("docs/$nombreArchivo");

        $writer = new Xlsx($spreadsheet);
        $writer->save($rutaSalida);

        return response()->download($rutaSalida)->deleteFileAfterSend(true);
    }