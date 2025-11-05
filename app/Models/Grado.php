<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Grado extends Model
{
    use HasFactory;

    protected $table = 'grados';
    protected $primaryKey = 'grado_id';
    
    protected $fillable = [
        'nombre',
        'nivel_id',
        'orden',     
        'tipo_grado',
    ];

    protected $visible = [
        'grado_id',
        'nombre'
    ];

    public function nivel()
    {
        return $this->belongsTo(Nivel::class, 'nivel_id', 'nivel_id');
    }

    public function grupos(): HasMany
    {
        return $this->hasMany(Grupo::class, 'grado_id', 'grado_id');
    }

    public function estructuraCurricular(): HasMany
    {
        return $this->hasMany(EstructuraCurricular::class, 'grado_id', 'grado_id');
    }

    public function gradosRegularesMapeados()
    {
        return $this->belongsToMany(
            Grado::class,        
            'grado_mapeo',       
            'extra_grado_id',    
            'regular_grado_id'   
        );
    }

    // ===============================================
    // =========== INICIO DE LA CORRECCIÓN ===========
    // ===============================================

    /**
     * Define la relación con Materias a través de la tabla pivote 'estructura_curricular'.
     * Le decimos a Eloquent que cargue las columnas extra 'campo_id' y 'ponderacion_materia'.
     */
    public function materias()
    {
        return $this->belongsToMany(Materia::class, 'estructura_curricular', 'grado_id', 'materia_id')
                   ->withPivot('campo_id', 'ponderacion_materia') // <-- LÍNEA CORREGIDA
                   ->withTimestamps();
    }
    
    // ===============================================
    // ============= FIN DE LA CORRECCIÓN ============
    // ===============================================
    
    public function getRouteKeyName()
    {
        return 'grado_id';
    }
    
}