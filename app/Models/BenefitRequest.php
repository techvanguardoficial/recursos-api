<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\SoftDeletes;

class BenefitRequest extends Model
{
    use HasUuids, SoftDeletes;

    protected $keyType = 'string';
    public $incrementing = false;

    protected $fillable = [
        'version',
        'current_step',
        'status',
        'personal_data',
        'benefit_situation',
        'benefit_details',
        'deferment_reason',
        'documentation',
        'submission',
        'analysis',
        'price',
        'payment_status',
    ];

    protected $casts = [
        'personal_data' => 'array',
        'benefit_situation' => 'array',
        'benefit_details' => 'array',
        'deferment_reason' => 'array',
        'documentation' => 'array',
        'submission' => 'array',
        'analysis' => 'array',
        'price' => 'decimal:2',
        'payment_status' => 'string',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'deleted_at' => 'datetime',
    ];
}
