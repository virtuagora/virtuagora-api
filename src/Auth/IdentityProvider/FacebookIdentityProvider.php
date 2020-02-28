<?php

namespace App\Auth\IdentityProvider;

use App\Util\Exception\AppException;

class FacebookIdentityProvider
{
    protected $db;
    protected $facebook;

    public function __construct($db, $facebook)
    {
        $this->db = $db;
        $this->facebook = $facebook;
    }

    public function makeIdentifiers($options)
    {
        $response = $this->facebook->get('/me?fields=id,first_name,last_name,email', $options['access']);
        $userNode = $response->getGraphUser();
        return [
            'facebook' => $userNode['id'],
            'email' => $userNode['email'],
            'names' => $userNode['first_name'],
            'surnames' => $userNode['last_name'],
        ];
    }

    public function retrieveUser($data)
    {
        if (isset($data['email'])) {
            return $this->db->query('App:User')->where('email', $data['email'])->first();
        } else {
            return $this->db->query('App:User')->where('facebook', $data['facebook'])->first();
        }
    }

    public function makeRegistrationToken($data)
    {
        $pending = $this->db->query('App:PendingUser')->firstOrNew([
            'provider' => 'facebook',
            'identifier' => $data['facebook'],
        ]);
        $pending->token = bin2hex(random_bytes(10));
        $pending->fields = [
            'names' => $data['names'],
            'surnames' => $data['surnames'],
            'email' => $data['email'],
        ];
        $pending->save();
        return $pending->token;
    }

    public function createPendingUser($data)
    {
        return null;
    }

    public function registerUser($data, $pending)
    {
        $subj = $this->db->new('App:Subject');
        $subj->display_name = $pending->fields['names'] . ' ' . $pending->fields['surnames'];
        $subj->img_type = 1;
        $subj->img_hash = $pending->identifier;
        $subj->type = 'User';
        $subj->save();
        $user = $this->db->new('App:User');
        $user->facebook = $pending->identifier;
        $user->email = $pending->fields['email'];
        $user->names = $pending->fields['names'];
        $user->surnames = $pending->fields['surnames'];
        $user->subject()->associate($subj);
        $user->save();
        $pending->delete();
        $this->db->table('subject_role')->insert([
            'role_id' => 'user',
            'subject_id' => $subj->id,
        ]);
        return $user;
    }
}
