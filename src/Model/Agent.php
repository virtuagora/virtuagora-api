<?php

namespace App\Model;

use App\Util\Utils;
use App\Auth\Actor;
use App\Auth\Relationable;
use Illuminate\Database\Eloquent\Model;

class Agent extends Model implements Actor, Relationable
{
    protected $table = 'agents';
    protected $visible = [
        'id', 'display_name', 'description', 'avatar', 'score', 'banned', 'locale',
        'extra_fields', 'pictures',
        'roles_list', 'pivot',
        'agent_type_id', 'roles', 'person', 'place', 'accounts',
    ];
    protected $fillable = [
        'display_name', 'description', 'avatar', 'locale',
        'extra_fields', 'hidden_fields', 'agent_type_id',
    ];
    protected $casts = [
        'banned' => 'boolean',
        'extra_fields' => 'array',
        'hidden_fields' => 'array',
        'pictures' => 'array',
    ];

    public function accounts()
    {
        return $this->hasMany('App\Model\Account');
    }

    public function person()
    {
        return $this->belongsTo('App\Model\Person');
    }

    public function place()
    {
        return $this->belongsTo('App\Model\Place');
    }

    public function roles()
    {
        return $this->belongsToMany('App\Model\Role', 'agent_role');
    }

    public function setDisplayNameAttribute($value)
    {
        $this->attributes['display_name'] = $value;
        $this->attributes['trace'] = Utils::traceStr($value);
    }

    public function rolesList()
    {
        return $this->roles->pluck('id')->toArray();
    }

    public function getRolesListAttribute()
    {
        return $this->rolesList();
    }

    public function relationsWith(Actor $other)
    {
        return (isset($this->id) && $this->id == $other->id) ? ['self'] : [];
    }
}
