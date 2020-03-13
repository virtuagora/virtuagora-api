<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Auth\Session\SessionHandler;

class AuthenticationMiddleware
{
    protected SessionHandler $session;

    public function __construct(SessionHandler $session)
    {
        $this->session = $session;
    }

    /**
     * @param Request  $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        $agent = $this->session->authenticate($request);
        $request = $request->withAttribute('agent', $agent);
        return $handler->handle($request);
    }
}
