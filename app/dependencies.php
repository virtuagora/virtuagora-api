<?php
declare(strict_types=1);

use DI\ContainerBuilder;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Monolog\Processor\UidProcessor;
use Psr\Container\ContainerInterface;
use Psr\Log\LoggerInterface;

use Illuminate\Container\Container as IlluminateContainer;
use Illuminate\Database\Connection;
use Illuminate\Database\Connectors\ConnectionFactory;
use Illuminate\Database\ConnectionResolver;
use Illuminate\Database\Eloquent\Model as Eloquent;


return function (ContainerBuilder $containerBuilder) {
    $containerBuilder->addDefinitions([
        LoggerInterface::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');

            $loggerSettings = $settings['logger'];
            $logger = new Logger($loggerSettings['name']);

            $processor = new UidProcessor();
            $logger->pushProcessor($processor);

            $handler = new StreamHandler($loggerSettings['path'], $loggerSettings['level']);
            $logger->pushHandler($handler);

            return $logger;
        },
        // Database connection
        Connection::class => function (ContainerInterface $c) {
            $settings = $c->get('settings');
            $factory = new ConnectionFactory(new IlluminateContainer());
            $connection = $factory->make(settings['db']);
            $connection->disableQueryLog();
            $resolver = new ConnectionResolver();
            $resolver->addConnection('default', $connection);
            $resolver->setDefaultConnection('default');
            Eloquent::setConnectionResolver($resolver);
            return $connection;
        },
        PDO::class => function (ContainerInterface $c) {
            return $c->get(Connection::class)->getPdo();
        },
    ]);
};
