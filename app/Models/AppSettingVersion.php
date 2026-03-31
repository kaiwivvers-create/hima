<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppSettingVersion extends Model
{
    protected $fillable = [
        'app_name',
        'app_logo_path',
        'text_overrides',
        'changed_branding',
        'changed_content',
        'created_by_user_id',
    ];

    protected $casts = [
        'text_overrides' => 'array',
        'changed_branding' => 'boolean',
        'changed_content' => 'boolean',
    ];
}
