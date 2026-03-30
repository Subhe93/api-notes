<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'google_id',
        'name',
        'email',
        'avatar',
    ];

    protected $hidden = [
        'google_id',
    ];

    public function notes()
    {
        return $this->hasMany(Note::class);
    }

    public function shares()
    {
        return $this->hasMany(Share::class, 'owner_id');
    }

    public function sharedWithMe()
    {
        return $this->hasMany(Share::class, 'shared_with_id');
    }
}
