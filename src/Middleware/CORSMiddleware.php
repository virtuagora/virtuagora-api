<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;

class CORSMiddleware implements MiddlewareInterface
{
    protected array $options;

    public function __construct(array $options)
    {
        $this->options = array_merge([
            'origin' => ['*'],
            'methods' => ['GET', 'POST'],
            'headers.allow' => ['Authorization', 'Content-Type'],
        ], $options);
    }

    /**
     * @param Request  $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($request->hasHeader('Origin')) {
            $origin = $request->getHeaderLine('Origin');
            if (in_array($origin, $this->options['origin'])) {
                $response = $response
                    ->withHeader('Access-Control-Allow-Origin', $origin)
                    ->withHeader('Access-Control-Allow-Credentials', 'true');
                if ($request->isOptions()) {
                    return $response->withHeader(
                        'Access-Control-Allow-Headers',
                        implode(', ', $this->options['headers.allow'])
                    )->withHeader(
                        'Access-Control-Allow-Methods',
                        implode(', ', $this->options['methods'])
                    );
                }
            } else {
                return $response->withStatus(401);
            }
        }
        return $handler->handle($request);
    }
}
