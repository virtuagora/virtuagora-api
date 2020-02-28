<?php
declare(strict_types=1);

namespace App\Gate;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Opis\JsonSchema\Validator;
use Opis\JsonSchema\Schema;
use App\Util\SchemaFactory;

class MiscGate implements AbstractApiGate
{
    private $accountRepo;
    private $sessionHandler;
    private $validator;

    public function createSession(
        Request $request,
        Response $response,
        array $params
    ): Response {
        $schema = SchemaFactory::fromFile('signInSchema');
        $data = $request->getAttribute('payload')->data ?? null;
        $result = $this->validator->schemaValidation($data, $schema);
        if (!$result->isValid()) {
            return $this->respondWithError($response, 'invalidData');
        }
        $userResp = $this->accountRepo->retriveOneLocal(
            $data->username, $data->password
        );
        if ($userResp->getState() != 'success') {
            return $this->respondWithError($response, $userResp->getState());
        }
        $user = $userData->getModel()->agent;
        $response = $this->sessionHandler->signIn($request, $user);
        return $this->respondWithJson($response, [
            'status' => 'success',
            'user_id' => $user->id,
        ], 201);
    }
}