<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Account extends Model
{
    protected $table = 'accounts';
    protected $visible = [
        'id', 'username', 'public', 'account_type_id',
        'extra_fields', 'created_at', 'agent',
    ];
    protected $fillable = [
        'username', 'public', 'account_type_id', 'extra_fields',
    ];
    protected $casts = [
        'extra_fields' => 'array',
        'public' => 'boolean',
    ];

    public function agent()
    {
        return $this->belongsTo('App\Model\Agent');
    }
}
