<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RolController extends Controller
{
    private const SYSTEM_PERMISSIONS = [
        'administrar-sistema',
        'proteger-cuenta-sistema',
    ];

    /**
     * Aplica el middleware de permisos a las acciones del controlador.
     * Asegura que solo usuarios con los permisos adecuados puedan gestionar roles.
     */
    function __construct()
    {
        $this->middleware('permission:ver-rol|crear-rol|editar-rol|borrar-rol', ['only' => ['index']]);
        $this->middleware('permission:crear-rol', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-rol', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-rol', ['only' => ['destroy']]);
    }

    /**
     * Muestra una lista de todos los roles.
     * @return \Illuminate\View\View
     */
    public function index()
    {
        $roles = Role::all();
        return view('roles.index', compact('roles'));
    }

    /**
     * Muestra el formulario para crear un nuevo rol.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $permission = $this->availablePermissions();
        return view('roles.form', compact('permission'));
    }

    /**
     * Almacena un nuevo rol en la base de datos y le asigna los permisos seleccionados.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $this->validate(
            $request,
            [
                'name' => 'required|unique:roles,name',
                'permission' => 'required|array',
                'permission.*' => 'exists:permissions,name',
            ],
            [
                'name.required' => 'El campo NO puede estar vacío',
                'name.unique' => 'Este rol ya está registrado',
                'permission.required' => 'Debes seleccionar al menos un permiso'
            ]
        );

        $this->ensureSystemPermissionsAllowed($validated['permission']);
        abort_if($validated['name'] === 'SuperAdministrador' && !auth()->user()->hasRole('SuperAdministrador'), 403, 'Solo SuperAdministrador puede crear este rol.');

        $role = Role::create([
            'name' => $validated['name'],
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($validated['permission']);

        return redirect()->route('panel-roles.index')->with('success', 'Rol creado correctamente');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id

     */
    public function show($id)
    {
        //
    }

    /**
     * Muestra el formulario para editar un rol existente.
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $role = Role::findOrFail($id);
        $this->ensureRoleIsManageable($role);
        $permission = $this->availablePermissions();
        $rolePermissions = $role->permissions->pluck('name')->all();

        return view('roles.form', compact('role', 'permission', 'rolePermissions'));
    }

    /**
     * Actualiza un rol existente y sincroniza su nueva lista de permisos.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        $validated = $this->validate(
            $request,
            [
                'name' => 'required',
                'permission' => 'required|array',
                'permission.*' => 'exists:permissions,name',
            ],
            [
                'name.required' => 'El campo NO puede estar vacío',
                'permission.required' => 'Debes seleccionar al menos un permiso'
            ]
        );

        $role = Role::findOrFail($id);
        $this->ensureRoleIsManageable($role);
        $this->ensureSystemPermissionsAllowed($validated['permission']);
        $role->name = $validated['name'];
        $role->save();

        $role->syncPermissions($validated['permission']);

        return redirect()->route('panel-roles.index')->with('success', 'Rol actualizado correctamente');
    }

    /**
     * Elimina un rol de la base de datos.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        $role = Role::findOrFail($id);
        $this->ensureRoleIsManageable($role);
        abort_if($role->name === 'SuperAdministrador', 403, 'El rol SuperAdministrador no puede eliminarse.');
        $role->delete();
        return redirect()->route('panel-roles.index')->with('success', 'Rol eliminado correctamente');
    }

    private function availablePermissions()
    {
        $permissions = Permission::query()->where('guard_name', 'web');

        if (!auth()->user()->hasRole('SuperAdministrador')) {
            $permissions->whereNotIn('name', self::SYSTEM_PERMISSIONS);
        }

        return $permissions->orderBy('name')->get();
    }

    private function ensureSystemPermissionsAllowed(array $permissions): void
    {
        if (!auth()->user()->hasRole('SuperAdministrador')
            && array_intersect($permissions, self::SYSTEM_PERMISSIONS)) {
            abort(403, 'Solo SuperAdministrador puede asignar permisos de sistema.');
        }
    }

    private function ensureRoleIsManageable(Role $role): void
    {
        if ($role->name === 'SuperAdministrador' && !auth()->user()->hasRole('SuperAdministrador')) {
            abort(403, 'Solo SuperAdministrador puede modificar este rol.');
        }
    }
}
