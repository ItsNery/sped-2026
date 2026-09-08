<?php
// local
namespace App\Http\Controllers;

use App\Models\Institucion;
use Illuminate\Http\Request;

class InstitucionController extends Controller
{
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            abort_unless(auth()->user()?->isAdministrator(), 403);

            return $next($request);
        });
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $instituciones = Institucion::with('sectorizadora')->orderBy('nombre')->get();
        $institucionesSectorizadoras = Institucion::whereNull('institucion_sectorizadora_id')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);

        return view('panel-instituciones.index', compact('instituciones', 'institucionesSectorizadoras'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        // Not used, using modal in index
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $validated = $request->validate($this->rules());

        Institucion::create($validated);

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución creada exitosamente.');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function show(Institucion $institucion)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function edit(Institucion $institucion)
    {
        // Not used, using modal in index
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Institucion $institucion)
    {
        $validated = $request->validate($this->rules($institucion));

        $institucion->update($validated);

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución actualizada exitosamente.');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Institucion  $institucion
     * @return \Illuminate\Http\Response
     */
    public function destroy(Institucion $institucion)
    {
        // 1. Verificamos si tiene registros relacionados
        // Usamos exists() que es más rápido que contar todos los registros
        $tieneDependencias = $institucion->indicadores()->exists() ||
            $institucion->usuario()->exists() ||
            $institucion->usuarios()->exists() ||
            $institucion->sectorizadas()->exists();

        if ($tieneDependencias) {
            // 2. Si tiene relaciones, regresamos con un error
            return redirect()->route('panel-cat-instituciones.index')
                ->with('error', 'No se puede eliminar la institución porque tiene indicadores, usuarios o instituciones sectorizadas asociadas.');
        }

        // 3. Si está limpio, procedemos a borrar
        $institucion->delete();

        return redirect()->route('panel-cat-instituciones.index')
            ->with('success', 'Institución eliminada exitosamente.');
    }

    private function rules(?Institucion $institucion = null): array
    {
        return [
            'nombre' => ['required', 'string', 'max:255'],
            'titular' => ['required', 'string', 'max:255'],
            'institucion_sectorizadora_id' => [
                'nullable',
                'integer',
                'exists:instituciones,id',
                function (string $attribute, mixed $value, \Closure $fail) use ($institucion) {
                    if ($value === null) {
                        return;
                    }

                    if ($institucion && (int) $value === (int) $institucion->id) {
                        $fail('Una institución no puede sectorizarse a sí misma.');
                        return;
                    }

                    if (Institucion::whereKey($value)->whereNotNull('institucion_sectorizadora_id')->exists()) {
                        $fail('La institución sectorizadora seleccionada ya depende de otra institución.');
                    }

                    if ($institucion?->sectorizadas()->exists()) {
                        $fail('Una institución que ya tiene sectorizadas no puede depender de otra institución.');
                    }
                },
            ],
        ];
    }
}
