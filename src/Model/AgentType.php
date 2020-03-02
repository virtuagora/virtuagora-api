<?php

declare(strict_types=1);

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class AgentType extends Model
{
    public $incrementing = false;
    protected $table = 'agent_types';
    protected $visible = [
        'id', 'name', 'individual',
        'localized_fields_schema', 'extra_fields_schema', 'hidden_fields_schema',
        'allowed_relations',
    ];
    protected $fillable = [
        'id', 'name', 'description', 'role_policy', 'role_id',
        'public_schema', 'private_schema', 'allowed_relations',
    ];
    protected $casts = [
        'individual' => 'boolean',
        'extra_fields_schema' => 'array',
        'hidden_fields_schema' => 'array',
        'allowed_relations' => 'array',
    ];

    public function agents()
    {
        return $this->hasMany('App\Model\Agent');
    }

    public function role()
    {
        return $this->belongsTo('App\Model\Role');
    }
}