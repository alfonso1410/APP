<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
class MaestroController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $users = User::where('rol', 'MAESTRO')->latest()->paginate(10);
        return view('maestros.index', compact('users'));
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
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'activo' => ['required', 'boolean'],
        ]);

        // Añadimos el rol 'MAESTRO' por defecto
        $dataToSave = array_merge($validatedData, [
            'rol' => 'MAESTRO',
            'password' => Hash::make($request->password),
        ]);

        User::create($dataToSave);

        return redirect()->route('maestros.index')->with('success', 'Maestro creado exitosamente.');
    }
    /**
     * Display the specified resource.
     */
    public function show(User $user)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(User $user)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
     public function update(Request $request, User $maestro)
    {
        $validatedData = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'apellido_paterno' => ['required', 'string', 'max:255'],
            'apellido_materno' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', Rule::unique(User::class)->ignore($maestro->id)],
            'activo' => ['required', 'boolean'],
            // No validamos 'rol' porque no se puede cambiar desde aquí
        ]);

        $maestro->update($validatedData);
        return redirect()->route('maestros.index')->with('success', 'Maestro actualizado exitosamente.');
    }
    /**
     * Remove the specified resource from storage.
     */
       public function destroy(User $maestro)
    {
        $maestro->activo = false;
        $maestro->save();
        return redirect()->route('maestros.index')->with('success', 'Maestro desactivado exitosamente.');
    }
}
