<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Arr;
use App\Models\Institucion;
use App\Models\CatMunicipio;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class UserController extends Controller
{
    /**
     * Aplica el middleware de permisos para proteger las acciones del controlador.
     */
    function __construct()
    {
        $this->middleware('permission:ver-usuario', ['only' => ['index']]);
        $this->middleware('permission:crear-usuario', ['only' => ['create', 'store']]);
        $this->middleware('permission:editar-usuario', ['only' => ['edit', 'update']]);
        $this->middleware('permission:borrar-usuario', ['only' => ['destroy']]);
        $this->middleware('permission:des-activar-usuario', ['only' => ['deactivate', 'activate']]);
    }

    /**
     * Muestra una lista de todos los usuarios del sistema.
     * @return \Illuminate\View\View
     */
    public function index(Request $request)
    {

        $users = User::all();
        return view('users.index', compact('users'));
    }

    /**
     * Muestra el formulario para crear un nuevo usuario.
     * @return \Illuminate\View\View
     */
    public function create()
    {
        $municipios = CatMunicipio::all();
        $roles = Role::all();
        $instituciones = Institucion::orderBy('nombre')->get();
        return view('users.form', compact('roles', 'instituciones', 'municipios'));
    }

    /**
     * Almacena un nuevo usuario en la base de datos.
     * La lógica de validación y asignación de relaciones cambia dinámicamente
     * según el tipo de usuario (Institución, Municipio, Enlace) y el rol seleccionado.
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        Log::debug('UserController@store: Iniciando creación de usuario.', $request->all());

        $rules = [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|confirmed|min:6',
            'password_confirmation' => 'required',
            'roles' => 'required|string|exists:roles,name',
            'instituciones' => 'required_if:roles,Enlace|array|min:1',
        ];

        $messages = [
            'name.required' => 'El campo No puede estar vacío',
            'name.string' => 'Ingresa un nombre válido',

            'email.required' => 'El campo No puede estar vacío',
            'email.email' => 'Debes ingresar un correo válido',
            'email.unique' => 'Este correo ya está registrado',

            'password.required' => 'El campo No puede estar vacío',
            'password.confirmed' => 'La contraseña y la confirmación de contraseña deben de coincidir',
            'password.min' => 'La contraseña debe de tener al menos 6 caractéres',
            'password_confirmation' => 'Es campo NO puede estar vacío',

            'roles.required' => 'Debes de seleccionar alguna opción',

            'id_institucion.required_unless' => 'Debes de seleccionar una institución si no es Enlace',
            'id_institucion.exists' => 'La institución seleccionada no es válida',

            'instituciones.required_if' => 'Debes de seleccionar al menos una institución si es Enlace',
            'instituciones.array' => 'Selecciona instituciones válidas',
        ];

        // Validación condicional para id_institucion / nueva_institucion / instituciones (múltiple) / id_municipio
        $rolSeleccionado = $request->input('roles');
        $tipoUsuario = $request->input('tipo_usuario'); // 'municipio' o 'institucion' (o null si es 'institucion')

        if ($rolSeleccionado === 'Enlace') {
            $rules['instituciones'] = 'required|array|min:1';
            $rules['instituciones.*'] = 'exists:instituciones,id';
            $messages['instituciones.required'] = 'Debe seleccionar al menos una institución para el rol Enlace.';
            $messages['instituciones.min'] = 'Debe seleccionar al menos una institución para el rol Enlace.';
            $messages['instituciones.*.exists'] = 'Una de las instituciones seleccionadas no es válida.';
        } else if ($tipoUsuario === 'municipio') {
            $rules['id_municipio'] = 'required|exists:cat_municipios,id';
            $messages['id_municipio.required'] = 'Debe seleccionar un municipio.';
            $messages['id_municipio.exists'] = 'El municipio seleccionado no es válido.';
        } else { // Es tipo 'institucion' y no es 'Enlace'
            if ($request->input('id_institucion') === 'nueva_institucion') {
                $rules['nueva_institucion_nombre'] = 'required|string|max:255|unique:instituciones,nombre';
                $rules['nueva_institucion_titular'] = 'required|string|max:255';
                $messages['nueva_institucion_nombre.required'] = 'El nombre de la nueva institución es obligatorio.';
                $messages['nueva_institucion_nombre.unique'] = 'Ya existe una institución con este nombre.';
                $messages['nueva_institucion_titular.required'] = 'El titular de la nueva institución es obligatorio.';
            } else {
                $rules['id_institucion'] = 'required|exists:instituciones,id';
                $messages['id_institucion.required'] = 'Debe seleccionar una institución.';
                $messages['id_institucion.exists'] = 'La institución seleccionada no es válida.';
            }
        }

        $validator = Validator::make($request->all(), $rules, $messages);
        if ($validator->fails()) {
            Log::warning('UserController@store: Falló la validación.', $validator->errors()->toArray());
            return back()->withErrors($validator)->withInput();
        }
        $validatedData = $validator->validated();
        Log::debug('UserController@store: Datos validados.', $validatedData);


        DB::beginTransaction();
        try {
            $institucionIdParaUsuario = null;
            $municipioIdParaUsuario = null;

            // Crear nueva institución si es necesario
            if ($rolSeleccionado !== 'Enlace' && $tipoUsuario !== 'municipio' && $request->input('id_institucion') === 'nueva_institucion') {
                Log::info('UserController@store: Creando nueva institución.');
                $nuevaInstitucion = Institucion::create([
                    'nombre' => $validatedData['nueva_institucion_nombre'],
                    'titular' => $validatedData['nueva_institucion_titular'],
                    // 'plan_estatal' => 0, // O cualquier valor por defecto que necesites
                ]);
                $institucionIdParaUsuario = $nuevaInstitucion->id;
                Log::info("UserController@store: Nueva institución creada con ID: {$institucionIdParaUsuario}");
            } elseif ($rolSeleccionado !== 'Enlace' && $tipoUsuario !== 'municipio') {
                $institucionIdParaUsuario = $validatedData['id_institucion'];
            }

            if ($tipoUsuario === 'municipio') {
                $municipioIdParaUsuario = $validatedData['id_municipio'];
            }

            // Preparar datos del usuario
            $userData = [
                'name' => $validatedData['name'],
                'email' => $validatedData['email'],
                'password' => Hash::make($validatedData['password']),
                'id_institucion' => $institucionIdParaUsuario, // Será null si es Enlace o Municipio
                'id_municipio' => $municipioIdParaUsuario,     // Será null si es Institución
                // 'finalizado' => $request->input('finalizado', 0), // Asegúrate de manejar 'finalizado'
            ];
            if ($request->has('finalizado')) { // Manejar 'finalizado'
                $userData['finalizado'] = $request->input('finalizado');
            }

            Log::debug('UserController@store: Creando usuario con datos:', $userData);
            $user = User::create($userData);
            Log::info("UserController@store: Usuario creado con ID: {$user->id}");

            // Asignar rol
            $user->assignRole($validatedData['roles']);
            Log::info("UserController@store: Rol '{$validatedData['roles']}' asignado al usuario ID: {$user->id}");

            // Si el rol es Enlace, sincronizar instituciones
            if ($validatedData['roles'] === 'Enlace' && !empty($validatedData['instituciones'])) {
                $user->instituciones()->sync($validatedData['instituciones']);
                Log::info("UserController@store: Instituciones sincronizadas para Enlace ID: {$user->id}");
            }

            DB::commit();
            return redirect()->route('panel-usuarios.index')->with('success', 'Usuario creado correctamente.');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("UserController@store: Excepción atrapada.", ['message' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Error al crear el usuario: ' . $e->getMessage())->withInput();
        }
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
     * Muestra el formulario para editar un usuario existente.
     * @param  int  $id
     * @return \Illuminate\View\View
     */
    public function edit($id)
    {
        $municipios = CatMunicipio::all();
        $user = User::find($id);
        $roles = Role::all();
        $instituciones = Institucion::orderBy('nombre')->get();

        $userRole = $user->roles->first()->name ?? ''; // Nombre del primer rol, o vacío si no tiene roles

        // Si el usuario es Enlace, obtenemos sus instituciones asociadas
        $userInstituciones = $user->instituciones->pluck('id')->toArray(); // Obtener las instituciones asociadas en un array
        return view('users.form', compact('user', 'roles', 'userRole', 'instituciones', 'userInstituciones', 'municipios'));
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    // public function update(Request $request, $id)
    // {
    //     $this->validate(
    //         $request,
    //         [
    //             'name' => 'required|string',
    //             'email' => 'required|email|unique:users,email,' . $id,
    //             'password' => 'confirmed',
    //             'roles' => 'nullable|string',
    //             'instituciones' => 'required_if:roles,Enlace|array|min:1',
    //             'id_institucion' => 'required_unless:roles,Enlace|exists:instituciones,id',
    //             'finalizado' => 'nullable|integer'

    //         ],
    //         [
    //             'name.required' => 'El campo No puede estar vacío',
    //             'name.string' => 'Ingresa un nombre válido',
    //             'email.required' => 'El campo No puede estar vacío',
    //             'email.email' => 'Debes ingresar un correo válido',
    //             'email.unique' => 'Este correo ya está registrado',
    //             'password.confirmed' => 'La contraseña y la confirmación de contraseña deben de coincidir',
    //             'id_institucion.required_if' => 'Debes elegir una institución si no es Enlace',
    //             'instituciones.required_if' => 'Debes seleccionar al menos una institución si es Enlace',
    //             'finalizado.integer' => 'El campo finalizado debe ser un número entero',
    //         ]
    //     );

    //     $input = $request->all();

    //     if (!empty($input['password'])) {
    //         $input['password'] = Hash::make($input['password']);
    //     } else {
    //         $input = Arr::except($input, array('password'));
    //     }

    //     $user = User::find($id);
    //     $user->update($input);

    //     DB::table('model_has_roles')->where('model_id', $id)->delete();
    //     $user->assignRole($request->input('roles'));

    //     if ($request->input('roles') === 'Enlace') {
    //         $user->id_institucion = null;
    //         $user->save();
    //         $user->instituciones()->sync($request->input('instituciones')); // Guardar las instituciones en la tabla pivote
    //     } else {
    //         $user->id_institucion = $request->input('id_institucion');
    //         $user->instituciones()->detach();
    //         $user->save();
    //     }

    //     return redirect()->route('panel-usuarios.index')->with('success', 'Usuario modificado correctamente');
    // }

    /**
     * Actualiza un usuario existente en la base de datos.
     * Incluye lógica para actualizar opcionalmente la contraseña y sincronizar roles y relaciones.
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, $id)
    {
        // Validación condicional
        $rules = [
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'password' => 'confirmed',
            'roles' => 'nullable|string',
            'instituciones' => 'required_if:roles,Enlace|array|min:1',
        ];

        // Validación de 'id_institucion' o 'id_municipio' dependiendo del tipo de usuario
        if ($request->input('tipo_usuario') === 'municipio') {
            $rules['id_municipio'] = 'required|exists:cat_municipios,id';
        } else {
            $rules['id_institucion'] = 'required_unless:roles,Enlace|exists:instituciones,id';
        }

        // Mensajes personalizados de error
        $messages = [
            'name.required' => 'El campo No puede estar vacío',
            'name.string' => 'Ingresa un nombre válido',
            'email.required' => 'El campo No puede estar vacío',
            'email.email' => 'Debes ingresar un correo válido',
            'email.unique' => 'Este correo ya está registrado',
            'password.confirmed' => 'La contraseña y la confirmación de contraseña deben de coincidir',
            'id_institucion.required_unless' => 'Debes de seleccionar una institución si no es Enlace',
            'id_institucion.exists' => 'La institución seleccionada no es válida',
            'instituciones.required_if' => 'Debes de seleccionar al menos una institución si es Enlace',
            'instituciones.array' => 'Selecciona instituciones válidas',
        ];

        // Ejecutamos la validación
        $this->validate($request, $rules, $messages);

        // Preparamos los datos de entrada
        $input = $request->all();

        // Si la contraseña no está vacía, la encriptamos
        if (!empty($input['password'])) {
            $input['password'] = Hash::make($input['password']);
        } else {
            // Si no se actualiza la contraseña, la eliminamos de los datos a actualizar
            $input = Arr::except($input, ['password']);
        }
        if (isset($input['finalizado']) && $input['finalizado'] == '0') {
            // Restablecemos los campos del reporte para permitir generarlo de nuevo
            $input['reporte_generado'] = false; // o 0
            $input['reporte_generado_at'] = null;
        }
        // Encontramos al usuario por su ID
        $user = User::findOrFail($id);
        $user->update($input);

        // Sincronizar roles (usa el sistema de Spatie para limpiar caché)
        $user->syncRoles($request->input('roles'));

        // Lógica de limpieza de campos según el tipo de usuario y rol
        if ($request->input('roles') === 'Enlace') {
            $user->id_institucion = null;
            $user->id_municipio = null;
            $user->save();
            $user->instituciones()->sync($request->input('instituciones'));
        } else {
            // No es Enlace, limpiamos tabla pivote
            $user->instituciones()->detach();

            if ($request->input('tipo_usuario') === 'municipio') {
                $user->id_municipio = $request->input('id_municipio');
                $user->id_institucion = null;
            } else {
                $user->id_institucion = $request->input('id_institucion');
                $user->id_municipio = null;
            }
            $user->save();
        }

        // Redirigimos con un mensaje de éxito
        return redirect()->route('panel-usuarios.index')->with('success', 'Usuario modificado correctamente');
    }


    /**
     * Elimina un usuario de la base de datos.
     * @param  int  $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy($id)
    {
        User::find($id)->delete();
        return redirect()->route('panel-usuarios.index')->with('success', 'Usuario borrado correctamente');
    }

    /**
     * Desactiva la cuenta de un usuario.
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function deactivate(User $user)
    {
        $user->is_active = false;
        $user->save();

        return back()->with('success', 'Usuario desactivado correctamente.');
    }

    /**
     * Activa la cuenta de un usuario.
     * @param  \App\Models\User $user
     * @return \Illuminate\Http\RedirectResponse
     */
    public function activate(User $user)
    {
        $user->is_active = true;
        $user->save();

        return back()->with('success', 'Usuario activado correctamente.');
    }
}
