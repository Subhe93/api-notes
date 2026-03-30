<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'url',
        'title',
        'tags',
        'notes_data',
        'synced_at',
    ];

    protected $casts = [
        'tags' => 'array',
        'notes_data' => 'array',
        'synced_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getDomain()
    {
        return parse_url($this->url, PHP_URL_HOST);
    }
}
