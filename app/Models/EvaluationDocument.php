<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class EvaluationDocument extends Model
{
    protected $fillable = [
        'evaluation_id',
        'title',
        'file_path',
        'file_size',
        'file_type',
    ];

    public function evaluation()
    {
        return $this->belongsTo(Evaluation::class);
    }

    public function getUrlAttribute()
    {
        return Storage::disk('supabase')->url($this->file_path);
    }

    protected static function booted()
    {
        static::deleting(function ($document) {
            if (Storage::disk('supabase')->exists($document->file_path)) {
                Storage::disk('supabase')->delete($document->file_path);
            }
        });
    }
}
