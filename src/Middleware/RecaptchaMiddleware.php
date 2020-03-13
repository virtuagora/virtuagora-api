<?php

declare(strict_types=1);

namespace App\Middleware;

use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use App\Util\Exception\RecaptchaException;
use ReCaptcha\ReCaptcha;

class RecaptchaMiddleware implements MiddlewareInterface
{
    protected array $settings;
    protected string $env;

    public function __construct(string $env, array $settings)
    {
        $this->settings = $settings;
        $this->env = $env;
    }

    /**
     * @param Request  $request
     * @param RequestHandler $handler
     * @return Response
     */
    public function process(Request $request, RequestHandler $handler): Response
    {
        if ($this->env !== 'dev') {
            $payload = $request->getAttribute('payload') ?? new StdClass();
            $field = $this->settings['fieldname'] ?? 'recaptcha';
            if (!property_exists($payload, $field)) {
                throw new RecaptchaException();
            }
            $recaptchaToken = $payload->{$field};
            $recaptcha = new ReCaptcha($this->settings['secret']);
            if (isset($this->settings['hostname'])) {
                $recaptcha->setExpectedHostname($this->settings['hostname']);
            }
            $recaptchaResp = $recaptcha->verify($recaptchaToken);
            if (!$recaptchaResp->isSuccess()) {
                throw new RecaptchaException($recaptchaResp->getErrorCodes());
            }
        }
        return $handler->handle($request);
    }
}
