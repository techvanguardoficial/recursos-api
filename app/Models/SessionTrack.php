<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SessionTrack extends Model
{
    protected $fillable = ['session_id', 'latitude', 'longitude', 'date'];
    protected $casts = [
        'date' => 'datetime',
    ];
}
