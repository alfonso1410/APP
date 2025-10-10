<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */

    public function gruposImpartidos(): HasMany
    {
        // La clave foránea es 'maestro_id' en la tabla 'grupo_materia_maestro'.
        // La clave local es 'id' en la tabla 'users'.
        return $this->hasMany(GrupoMateriaMaestro::class, 'maestro_id', 'id');
    }

    public function gruposCoTitular(): BelongsToMany
{
    // 1. Modelo relacionado
    // 2. Nombre de la tabla pivot
    // 3. Clave foránea de User en la pivot
    // 4. Clave foránea del Grupo en la pivot
    return $this->belongsToMany(Grupo::class, 'grupo_titular', 'maestro_id', 'grupo_id')
                ->withTimestamps(); // Incluye created_at y updated_at
}

    protected $fillable = [
        'name',          // Asegúrate de que el nombre aquí coincida con tu campo de BD
        'apellido_paterno', // <-- ¡AGREGA ESTE CAMPO!
        'apellido_materno', // <-- ¡AGREGA ESTE CAMPO! (Si quieres guardarlo)
        'rol',              // <-- ¡AGREGA ESTE CAMPO!
        'email',
        'password',
        'activo',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}