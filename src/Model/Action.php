<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Action extends Model
{
    public $incrementing = false;
    protected $table = 'actions';
    protected $visible = [
        'id', 'group', 'rule',
        'allowed_roles', 'allowed_first_targets', 'allowed_second_targets',
    ];
    protected $casts = [
        'allowed_roles' => 'array',
        'allowed_first_targets' => 'array',
        'allowed_second_targets' => 'array',
    ];
}
