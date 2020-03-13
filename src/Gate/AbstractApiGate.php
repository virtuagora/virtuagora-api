<?php
declare(strict_types=1);

namespace App\Gate;

use App\Repository\EntityData;
use Slim\Routing\RouteContext;
use Illuminate\Database\Eloquent\Model;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use JsonSerializable;

abstract class AbstractApiGate
{
    /**
     * @var string
     */
    protected $modelName;

    /**
     * @var string
     */
    protected $modelSlug;

    /**
     * @param Response         $response
     * @param JsonSerializable $payload
     * @param int              $status
     * @return Response
     */
    protected function respondWithJson(
        Response $response,
        ?JsonSerializable $payload = null,
        int $status = 200
    ): Response {
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Response         $response
     * @param string           $error
     * @param JsonSerializable $payload
     * @param int              $status
     * @return Response
     */
    protected function respondWithError(
        Response $response,
        string $error,
        ?JsonSerializable $payload = null,
        int $status = 400
    ): Response {
        $payload = $payload ?? [];
        $json = json_encode($payload, JSON_PRETTY_PRINT);
        $response->getBody()->write($json);
        return $response
            ->withStatus($status)
            ->withHeader('Content-Type', 'application/json');
    }

    /**
     * @param Request    $request
     * @param Response   $response
     * @param EntityData $entity
     * @param string     $modelName
     * @param string     $modelSlug
     * @return Response
     */
    protected function sendCreatedResponse(
        Request $request,
        Response $response,
        EntityData $entity,
        ?string $modelName = null,
        ?string $modelSlug = null
    ): Response {
        if ($entity->getState() != 'success') {
            return $this->respondWithError(
                $response, $entity->getState(), $entity->getMetadata()
            );
        }
        $modelName = $modelName ?? $this->modelName;
        $modelSlug = $modelSlug ?? $this->modelSlug;
        $entityUri = RouteContext::fromRequest($request)
            ->getRouteParser()
            ->urlFor('apiR1' . $modelName, [
                $modelSlug => $data->getModel()->id,
            ]);
        return $respondWithJson(
            $response->withHeader('Location', $entityUri),
            [Str::snake($modelName) => $entity->toArray()],
            201
        );
    }

    /**
     * @param Request    $request
     * @param Response   $response
     * @param EntityData $entity
     * @return Response
     */
    protected function sendEntityResponse(
        Request $request,
        Response $response,
        EntityData $entity
    ): Response {
        if ($entity->getState() != 'success') {
            return $this->respondWithError(
                $response, $entity->getState(), $entity->getMetadata()
            );
        }
        $payload = [
            'data' => $entity->getModel()->toArray(),
            'metadata' => $entity->getMetadata(),
            'warnings' => $entity->getWarnings(),
        ];
        return $this->respondWithJson($response, $payload);
    }

    /**
     * @param Request    $request
     * @param Response   $response
     * @param EntityData $entity
     * @param string     $modelName
     * @param string     $modelSlug
     * @return Response
     */
    protected function sendCollectionResponse(
        Request $request,
        Response $response,
        EntityData $entity,
        ?string $modelName = null,
        ?string $modelSlug = null
    ): Response {
        if ($entity->getState() != 'success') {
            return $this->respondWithError(
                $response, $entity->getState(), $entity->getMetadata()
            );
        }
        $payload = [
            'data' => $entity->getCollection()->toArray(),
            'metadata' => $entity->getMetadata(),
            'warnings' => $entity->getWarnings(),
        ];
        $paginator = $entity->getPaginator();
        if (isset($paginator)) {
            $entityUri = RouteContext::fromRequest($request)
                ->getRouteParser()
                ->urlFor('apiRN' . $modelName, []);
            $paginator->setUri($entityUrl);
            $payload['pagination'] = $paginator->getPaginationInfo();
            $payload['links'] = $paginator->getLinks();
        }
        return $this->respondWithJson($response, $payload);
    }

    /**
     * @param Response $response
     * @param string   $message
     * @param int      $status
     * @param array    $fields
     * @return Response
     */
    protected function sendSimpleResponse(
        Response $response,
        string $message,
        int $status = 200,
        array $fields = []
    ): Response {
        $fields['message'] = $message;
        return $this->respondWithJson($response, $fields, $status);
    }
}