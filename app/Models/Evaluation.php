<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Evaluation extends Model
{
    protected $fillable = [
        'title',
        'description',
        'status',
    ];

    public function documents()
    {
        return $this->hasMany(EvaluationDocument::class);
    }
}
