<?php

namespace App\Auth\SessionManager;

use App\Auth\Requester;
use App\Auth\SubjectInterface as Subject;
use Psr\Http\Message\ServerRequestInterface as Request;
use Firebase\JWT\JWT;
use HansOtt\PSR7Cookies\SetCookie;
use Exception;

class JWTSessionHandler implements SessionHandler
{
    private array $options;
    private $publicKey;
    private $privateKey;

    public function __construct(array $options = [])
    {
        
        $this->options = array_merge([
            'header' => 'Authorization',
            'regexp' => "/Bearer\s+(.*)$/i",
            'cookie' => 'token',
            'algorithm' => 'RS512',
        ], $options);
        if (in_array($options['algorithm'], ['RS256', 'RS384', 'RS512'])) {
            $this->privateKey = openssl_get_privatekey($options['privateKey']);
            $this->publicKey = openssl_get_pubickey($options['publicKey']);
        } else {
            $this->privateKey = $this->publicKey = $options['secret'];
        }
    }

    public function authenticate(Request $request): Requester
    {
        $token = $this->fetchToken($request);
        if (is_null($token)) {
            return new Requester('Annonymous');
        } else {
            $claims = $this->decodeToken($token);
            return new Requester(
                $claims->type,
                $claims->id,
                $claims->name,
                $claims->roles
            );
        }
    }

    public function signIn(Response $response, Actor $agent)
    {
        $claims = [
            'id' => $subject->getId(),
            'type' => $subject->getType(),
            'name' => $subject->getDisplayName(),
            'roles' => $subject->getRolesList(),
        ];
        $token = $this->encodeToken($claims);
        $cookie = new SetCookie('token', $token, time() + 3600, '', '', true, true);
        return $cookie->addToResponse($response);
    }

    public function signOut(Request $request, Response $response)
    {
        return $response;
    }

    private function fetchToken(Request $request): string
    {
        /* Check for token in header. */
        $header = $request->getHeaderLine($this->options["header"]);
        if (false === empty($header)) {
            if (preg_match($this->options["regexp"], $header, $matches)) {
                return $matches[1];
            }
        }
        /* Token not found in header try a cookie. */
        $cookieParams = $request->getCookieParams();
        if (isset($cookieParams[$this->options["cookie"]])) {
            if (
                preg_match(
                    $this->options["regexp"],
                    $cookieParams[$this->options["cookie"]],
                    $matches
                )
            ) {
                return $matches[1];
            }
            return $cookieParams[$this->options["cookie"]];
        };
        /* If everything fails return null. */
        return null;
    }

    private function decodeToken(string $token): object
    {
        try {
            $decoded = JWT::decode(
                $token, $this->publicKey, [$this->options["algorithm"]]
            );
            return $decoded;
        } catch (Exception $exception) {
            throw $exception;
        }
    }

    private function encodeToken(array $claims): string
    {
        try {
            $encoded = JWT::encode(
                $claims, $this->privateKey, $this->options['algorithm']
            );
            return $encoded;
        } catch (Exception $exception) {
            throw $exception;
        }
    }
}
