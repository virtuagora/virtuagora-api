<?php
declare(strict_types=1);

namespace App\Gate;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class UserGate implements AbstractApiGate
{
    protected $modelName = 'User';
    protected $modelSlug = 'usr';
    private $userRepo;

    public function createPendingUser(
        Request $request,
        Response $response,
        array $params
    ): Response {
        $agent = $request->getAttribute('agent');
        $data = $request->getAttribute('payload')->data ?? null;
        $pending = $this->userRepo->createPendingUser($agent, $data);
        return $this->sendSimpleResponse($response, 'Pending user created');
    }
}