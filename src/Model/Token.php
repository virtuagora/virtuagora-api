<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Token extends Model
{
    protected $table = 'tokens';
    protected $visible = [
        'id', 'type', 'token', 'data', 'expires_on',
    ];
    protected $fillable = [
        'type', 'token', 'data', 'expires_on', 'agent_id',
    ];
    protected $casts = [
        'data' => 'array',
        'expires_on' => 'datetime',
    ];

    public function agent()
    {
        return $this->belongsTo('App\Model\Agent', 'agent_id');
    }
}
