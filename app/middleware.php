<?php
declare(strict_types=1);

use App\Middleware\BodyParsingMiddleware;
use Slim\App;

return function (App $app) {
    $app->add(BodyParsingMiddleware::class);
};
