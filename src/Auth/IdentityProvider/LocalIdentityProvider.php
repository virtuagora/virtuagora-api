<?php

namespace App\Auth\IdentityProvider;

use App\Util\Exception\AppException;

class LocalIdentityProvider implements IdentityProviderInterface
{
    protected $db;
    protected $validation;

    public function __construct($db, $validation)
    {
        $this->db = $db;
        $this->validation = $validation;
    }

    public function getSignInFields(array $data)
    {
        $v = $this->validation->fromSchema([
            'username' => [
                'type' => 'string',
                'format' => 'email',
            ],
            'password' => [
                'type' => 'string',
                'minLength' => 1,
                'maxLength' => 250,
            ],
        ]);
        $v->assert($data);
        return [
            'username' => $data['username'],
            'password' => $data['password'],
        ];
    }

    public function retrieveSubject(array $data)
    {
        $subject = $this->db->query('App:Subject')
            ->where('username', $data['username'])
            ->first();
        if (isset($subject)) {
            $pass = $data['password'];
            $hash = $subject->password;
            if (password_verify($pass, $hash)) {
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $subject->password = $pass;
                    $subject->save();
                }
                return $subject;
            }
        }
        return null;
    }

    public function createMagicToken(array $data)
    {
        return null;
    }

    // public function createPendingUser($data)
    // {
    //     $v = $this->validation->fromSchema([
    //         'type' => 'object',
    //         'properties' => [
    //             'identifier' => [
    //                 'type' => 'string',
    //                 'format' => 'email',
    //             ],
    //         ],
    //         'required' => ['identifier'],
    //         'additionalProperties' => false,
    //     ]);
    //     $v->assert($data);
    //     $user = $this->db->query('App:User')
    //         ->where('email', $data['identifier'])
    //         ->first();
    //     if (isset($user)) {
    //         throw new AppException('Email already registered');
    //     }
    //     $pending = $this->db->query('App:PendingUser')->firstOrNew([
    //         'provider' => 'local',
    //         'identifier' => $data['identifier'],
    //     ]);
    //     $pending->token = bin2hex(random_bytes(10));
    //     $pending->save();
    //     return $pending;
    // }

    public function getSignUpFields(string $token, array $data)
    {
        $pending = $this->db->query('App:Token')
            ->where('type', 'signUp')
            ->where('token', $token)
            ->first();
        if (is_null($pending)) {
            // TODO revisar
            throw new AppException('Invalid token', 'pendigUserNotFound', 400);
        }
        $result = [
            'subject' => [
                'username' => $pending->data['email'],
                'locale' => $pending->data['locale'],
                'password' => $data['password'],
                'display_name' => $data['names'] . ' ' . $data['surnames'],
                'img_type' => 1,
                'img_hash' => md5($pending->data['email']),
            ],
            'person' => [
                'email' => $pending->data['email'],
                'names' => $data['names'],
                'surnames' => $data['surnames'],
            ],
        ];
        $pending->delete();
        return $result;
    }
}
