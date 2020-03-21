<?php

namespace App\Auth\Session;

use App\Auth\Actor as Actor;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface SessionHandler
{
    public function authenticate(Request $request): Actor;

    public function signIn(Response $response, Actor $subject): Response;

    public function signOut(Request $request, Response $response): Response;
}
