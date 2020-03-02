<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Log extends Model
{
    protected $table = 'logs';
    protected $visible = [
        'id', 'agent_id', 'proxy_id', 'action_id',
        'first_target_type', 'first_target_id',
        'second_target_type', 'second_target_id',
        'parameters', 'created_at',
    ];
    protected $fillable = [
        'parameters',
    ];
    protected $casts = [
        'parameters' => 'array',
    ];

    public function agent()
    {
        return $this->belongsTo('App\Model\Agent', 'agent_id');
    }

    public function action()
    {
        return $this->belongsTo('App\Model\Action', 'action_id');
    }

    public function first_target()
    {
        return $this->morphTo();
    }

    public function second_target()
    {
        return $this->morphTo();
    }
}
