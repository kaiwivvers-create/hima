<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Absence extends Model
{
    protected $fillable = [
        'student_id',
        'absence_date',
        'reason',
        'verification_status',
        'submitted_by',
    ];

    protected function casts(): array
    {
        return [
            'absence_date' => 'date',
        ];
    }

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }
}
