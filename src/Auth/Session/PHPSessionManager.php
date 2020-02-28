<?php

namespace App\Auth\SessionManager;

use App\Auth\DummySubject;
use App\Auth\SubjectInterface as Subject;
use Psr\Http\Message\ServerRequestInterface as Request;

class PHPSessionManager
{
    private $store;

    public function __construct($store)
    {
        $this->store = $store;
    }

    public function authenticate(Request $request)
    {
        if ($this->store->exists('subject')) {
            return new DummySubject(
                $this->store['subject']['type'],
                $this->store['subject']['id'],
                $this->store['subject']['name'],
                $this->store['subject']['roles'],
                $this->store['subject']['extra']
            );
        } else {
            return new DummySubject('Annonymous');
        }
    }

    public function signIn(Subject $subject)
    {
        $this->store->set('subject', $subject->toArray());
        return [
            'type' => 'php-session',
            'subject' => $this->store->get('subject'),
        ];
    }

    public function signOut()
    {
        $this->store->delete('subject');
    }
}
