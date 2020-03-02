<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
    public $incrementing = false;
    protected $table = 'roles';
    protected $visible = [
        'id', 'name', 'description', 'show_badge', 'icon', 'extra_fields',
    ];
    protected $casts = [
        'extra_fields' => 'array',
        'show_badge' => 'boolean',
    ];
}
