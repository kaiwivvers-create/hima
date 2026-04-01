<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TuitionProgram extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'monthly_amount',
    ];

    protected function casts(): array
    {
        return [
            'monthly_amount' => 'decimal:2',
        ];
    }
}

