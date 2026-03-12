<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class RolController extends Controller
{

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
        $permission = Permission::get();
        return view('roles.form', compact('permission'));
    }

    /**
     * Almacena un nuevo rol en la base de datos y le asigna los permisos seleccionados.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $this->validate(
            $request,
            [
                'name' => 'required|unique:roles,name',
                'permission' => 'required',
            ],
            [
                'name.required' => 'El campo NO puede estar vacío',
                'name.unique' => 'Este rol ya está registrado',
                'permission.required' => 'Debes seleccionar al menos un permiso'
            ]
        );

        // $role = Role::create(['name' => $request->input('name')]);
        $role = Role::create([
            'name' => $request->input('name'),
            'guard_name' => 'web',
        ]);
        $role->syncPermissions($request->input('permission'));

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
        $role = Role::find($id);
        $permission = Permission::get();
        $rolePermissions = DB::table("role_has_permissions")->where("role_has_permissions.role_id", $id)
            ->pluck('role_has_permissions.permission_id', 'role_has_permissions.permission_id')
            ->all();

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
        $this->validate(
            $request,
            [
                'name' => 'required',
                'permission' => 'required',
            ],
            [
                'name.required' => 'El campo NO puede estar vacío',
                'permission.required' => 'Debes seleccionar al menos un permiso'
            ]
        );

        $role = Role::find($id);
        $role->name = $request->input('name');
        $role->save();

        $role->syncPermissions($request->input('permission'));

        return redirect()->route('panel-roles.index')->with('success', 'Rol actualizado correctamente');
    }

    /**
     * Elimina un rol de la base de datos.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        DB::table("roles")->where('id', $id)->delete();
        return redirect()->route('panel-roles.index')->with('success', 'Rol eliminado correctamente');
    }
}
