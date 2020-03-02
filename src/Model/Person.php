<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Person extends Model
{
    protected $table = 'people';
    protected $visible = [
        'id', 'names', 'surnames', 'person_id', 'person_id_type',
        'gender', 'created_at', 'agent',
    ];
    protected $fillable = [
        'names', 'surnames', 'person_id', 'person_id_type', 'gender',
    ];

    public function agent()
    {
        return $this->hasOne('App\Model\Agent');
    }
}
