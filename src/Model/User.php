<?php

namespace App\Model;

class User extends Agent
{
    protected static function boot()
    {
        parent::boot();
        static::addGlobalScope('user', function (Builder $b) {
            $b->where('agent_type_id', 'User');
        });
    }
}
