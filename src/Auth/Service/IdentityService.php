<?php

namespace App\Auth\Service;

use App\Util\Exception\SystemException;
use App\Util\Exception\AppException;
use Carbon\Carbon;

class IdentityService
{
    private $providers;
    private $db;

    public function __construct($providers, $db)
    {
        $this->providers = $providers;
        $this->db = $db;
    }

    private function getProvider(string $provider)
    {
        if (!isset($this->providers[$provider])) {
            throw new SystemException('Identity provider not found');
        }
        return $this->providers[$provider];
    }

    public function signIn(string $provider, array $data)
    {
        $idProv = $this->getProvider($provider);
        $fields = $idProv->getSignInFields($data);
        $subject = $idProv->retrieveSubject($fields);
        if (is_null($subject)) {
            $token = $idProv->createMagicToken($fields);
            if (is_null($token)) {
                return [
                    'status' => 'not-found',
                ];
            } else {
                return [
                    'status' => 'magic-token',
                    'token' => $token,
                ];
            }
        } else {
            if (isset($subject->ban_expiration)) {
                if (Carbon::now()->lt($user->ban_expiration)) {
                    return [
                        'status' => 'banned',
                    ];
                } else {
                    $subject->ban_expiration = null;
                    $subject->save();
                }
            }
        }
        return [
            'status' => 'success',
            'subject' => $subject,
        ];
    }

    public function signUp(string $provider, string $token, array $data)
    {
        $idProv = $this->getProvider($provider);
        $fields = $idProv->getSignUpFields($token, $data);
        $person = $this->db->create('App:Person', $fields['person']);
        $person->save();
        $subject = $this->db->create('App:Subject', $fields['subject']);
        $subject->type = 'User';
        $subject->person()->associate($person);
        $subject->save();
        $this->db->table('subject_role')->insert([
            'role_id' => 'User',
            'subject_id' => $subject->id,
        ]);
        return $subject;
    }
}
