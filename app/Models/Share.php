<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Share extends Model
{
    use HasFactory;

    protected $fillable = [
        'owner_id',
        'shared_with_id',
        'type',
        'value',
        'permissions',
    ];

    public function owner()
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    public function sharedWith()
    {
        return $this->belongsTo(User::class, 'shared_with_id');
    }
}
