<?php

namespace App\Http\Controllers;
use App\Models\User; 
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
         
         $users = User::whereNot('rol', 'MAESTRO') // 1. Excluye a los maestros
                 ->orderBy('apellido_paterno')     // 2. Ordena por el apellido paterno
                 ->paginate(10);                   // 3. Pagina los resultados

        return view('users.index', [
            'users' => $users // El nombre 'users' será la variable en tu Blade
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:100'],
            'apellido_materno' => ['required', 'string', 'max:100'],
            'rol' => ['required', 'string', 'max:25'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'activo' => ['required', 'boolean'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'apellido_paterno' => $request->apellido_paterno,
            'apellido_materno' => $request->apellido_materno,
            'rol' => $request->rol,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'activo' => $request->activo,
        ]);
        return redirect()->route('admin.users.index')->with('status', 'Usuario creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
  public function update(Request $request, User $user)
{
    // 1. Definición de las reglas base (sin password)
    $rules = [
        'name' => ['required', 'string', 'max:255'],
        'apellido_paterno' => ['required', 'string', 'max:255'],
        'apellido_materno' => ['nullable', 'string', 'max:255'],
        'rol' => ['required', 'string', 'in:DIRECTOR,COORDINADOR,MAESTRO'],
        'activo' => ['required', 'boolean'],
        // Regla UNIQUE para email, ignorando el email del usuario actual.
        'email' => ['required', 'string','lowercase', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
    ];

    // 2. Si el usuario proporcionó una contraseña, la hacemos requerida y la validamos.
   // if ($request->filled('password')) {
     //   $rules['password'] = ['required', 'confirmed', Rules\Password::defaults()];
    //}

    // 3. Validar
    $validatedData = $request->validate($rules);

    // 4. Construir los datos a actualizar
    $userData = [
        'name' => $validatedData['name'],
        'apellido_paterno' => $validatedData['apellido_paterno'],
        'apellido_materno' => $validatedData['apellido_materno'],
        'rol' => $validatedData['rol'],
        'activo' => $validatedData['activo'],
        'email' => $validatedData['email'],
    ];

    // 5. Aplicar la contraseña SÓLO si se proporcionó una nueva
    //if ($request->filled('password')) {
      //  $userData['password'] = Hash::make($validatedData['password']);
    //}

    // 6. Actualizar
    $user->update($userData);

    return redirect()->route('admin.users.index')
                     ->with('success', 'Usuario actualizado exitosamente.');
}
    /**
     * Remove the specified resource from storage.
     */
  public function destroy(User $user)
{
    // 🔥 1. Cambiamos el estado 'activo' a 0 (Inactivo)
    $user->activo = 0; 
    $user->save(); // 2. Guardamos el cambio

    // 3. Redirección con mensaje
    return redirect()->route('admin.users.index')
                     ->with('success', 'El usuario ' . $user->name . ' ha sido desactivado exitosamente.');
}
}