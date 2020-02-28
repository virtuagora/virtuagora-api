<?php

namespace App\Auth\SessionManager;

use App\Auth\DummySubject;
use App\Auth\SubjectInterface as Subject;
use Psr\Http\Message\ServerRequestInterface as Request;

class JWTSessionManager implements SessionManagerInterface
{
    protected $jwt;
    protected $options;

    public function __construct($jwt, $options = [])
    {
        $this->jwt = $jwt;
        $this->options = array_merge([
            "header" => 'Authorization',
            "regexp" => "/Bearer\s+(.*)$/i",
            "cookie" => 'token',
        ], $options);
    }

    public function authenticate(Request $request)
    {
        $token = $this->fetchToken($request);
        if (is_null($token)) {
            return new DummySubject('Annonymous');
        } else {
            $claims = $this->jwt->decode($token);
            return new DummySubject(
                $claims['type'],
                $claims['id'],
                $claims['name'],
                $claims['roles']
            );
        }
    }

    public function signIn(Subject $subject)
    {
        $claims = [
            'id' => $subject->id,
            'type' => $subject->type,
            'name' => $subject->display_name,
            'roles' => $subject->rolesList(),
        ];
        $token = $this->jwt->createToken($claims);
        return [
            'type' => 'jwt-session',
            'subject' => $claims,
            'token' => $token,
        ];
    }

    public function signOut()
    {
        return;
    }

    protected function fetchToken(Request $request)
    {
        $headers = $request->getHeader($this->options["header"]);
        $header = isset($headers[0]) ? $headers[0] : "";
        if (preg_match($this->options["regexp"], $header, $matches)) {
            return $matches[1];
        }
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options["cookie"]])) {
            return $cookieParams[$this->options["cookie"]];
        };
        return null;
    }
}
