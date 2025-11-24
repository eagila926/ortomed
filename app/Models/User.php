<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table = 'usuarios';
    protected $primaryKey = 'id_user';
    public $timestamps = false; // cámbialo a true si luego agregas created_at/updated_at

    protected $fillable = [
        'nombre','apellido','correo','rol','password','estado'
    ];

    protected $hidden = ['password'];

    // Usaremos "correo" como campo de login
    public function getEmailForPasswordReset() { return $this->correo; }

    public function hasRole($roles): bool
    {
        $roles = (array) $roles;

        // Admin siempre pasa
        if ($this->rol === 'Admin') {
            return true;
        }

        return in_array($this->rol, $roles, true);
    }


}
