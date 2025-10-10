<?php

namespace App\Http\Controllers;

use App\Models\Grado;
use Illuminate\Http\Request;
use Illuminate\View\View;

class GradoController extends Controller
{
    /**
     * Muestra una lista de grados y sus grupos activos, filtrada por nivel.
     */
    public function index(Request $request): View
    {
        // 1. Establece 'Preescolar' (ID 1) como filtro por defecto
        $nivel_id = $request->input('nivel', 1);
        $search = $request->input('search');

        // 2. Consulta los grados del nivel seleccionado
        $grados = Grado::query()
            ->where('nivel_id', $nivel_id)
            ->when($search, function ($query, $search) {
                // Añade la capacidad de búsqueda por nombre de grado
                return $query->where('nombre', 'like', "%{$search}%");
            })
            // 3. Carga previamente los grupos de cada grado para evitar consultas N+1
            ->with(['grupos' => function ($query) {
                // Opcional: Podrías filtrar aquí solo grupos de un ciclo escolar específico
                $query->where('estado', 'ACTIVO');
            }])
            ->orderBy('nombre')
            ->get();
        
        // 4. Pasamos los datos a la vista
        return view('grados.index', [
            'grados' => $grados,
            'nivel_id' => $nivel_id, // Necesario para el componente de filtro
            'search' => $search,
        ]);
    }
}