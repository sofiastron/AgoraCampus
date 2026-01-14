<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens; 
use Illuminate\Notifications\Notifiable;
use App\Notifications\ResetPasswordNotification;
class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    protected $fillable = [

        'nom',
        'email',
        'password',
        'role',
        'photo'
    ];

    protected $hidden = [
        'password',
        'remember_token'
    ];

    /* Relations héritées */
    public function etudiant()
    {
        return $this->hasOne(Etudiant::class);
    }

    public function enseignant()
    {
         return $this->hasOne(Enseignant::class, 'user_id', 'id');
    }

    public function administrateur()
    {
        return $this->hasOne(Administrateur::class);
    }
    
public function sendPasswordResetNotification($token)
{
    $this->notify(new ResetPasswordNotification($token));
}
}
