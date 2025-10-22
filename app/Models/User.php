<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany; // Importante
use Illuminate\Database\Eloquent\Relations\BelongsToMany; // Importante


class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',             
        'apellido_paterno', 
        'apellido_materno', 
        'rol',              
        'email',
        'password',
        'activo',
    ];

    // --- INICIO CORRECCIÓN ---
    /**
     * Atributos visibles en JSON para el modal de campos formativos.
     */
    protected $visible = [
        'id',
        'name' // <-- El atributo clave
    ];
    // --- FIN CORRECCIÓN ---

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relaciones (sin cambios) ---
    public function gruposImpartidos(): HasMany
    {
        return $this->hasMany(GrupoMateriaMaestro::class, 'maestro_id', 'id');
    }

   public function gruposTitulares()
    {
        // --- CORRECCIÓN: Añadir withPivot para leer la columna 'idioma' de la tabla pivote ---
        return $this->belongsToMany(
            Grupo::class,
            'grupo_titular',
            'maestro_id', // <-- ¡CAMBIO AQUÍ! (en lugar de 'user_id')
            'grupo_id'
        )->withPivot('idioma'); // <-- AÑADIDO
    }

    public function scopeMaestros($query)
    {
        return $query->where('rol', 'MAESTRO'); // O como hayas definido tu rol
    }
}