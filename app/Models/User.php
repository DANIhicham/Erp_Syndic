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
    protected $fillable = ['nom', 'prenom', 'email', 'telephone', 'role', 'cin', 'password', 'etat'];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    public function appartements() { return $this->hasMany(Appartement::class, 'proprietaire_id'); }
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
    
    public function isAdmin()
    {
        return $this->role === 'admin';
    }

    public function isSyndic()
    {
        return $this->role === 'syndic';
    }

    public function isProprietaire()
    {
        return $this->role === 'proprietaire';
    }
    

    public function getInitialesAttribute()
    {
    return strtoupper(
        substr($this->nom, 0, 1) . substr($this->prenom, 0, 1)
    );
    }
}
