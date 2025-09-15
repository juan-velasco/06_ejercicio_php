<?php
require '../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Predis\Client as RedisClient;

$app = AppFactory::create();

$redis = new RedisClient([
    'scheme' => 'tcp',
    'host' => getenv('REDIS_HOST') ?: 'redis',
    'port' => 6379
]);


// Configurar Twig
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
$twig = Twig::create(__DIR__ . '/../templates', ['cache' => false]);
$app->add(TwigMiddleware::create($app, $twig));

$app->get('/', function ($request, $response, $args) use ($twig, $redis) {
    // Incrementar el contador de visitas en Redis
    $contador = $redis->incr('contador_visitas');
    // Renderizar la plantilla Twig
    return $twig->render($response, 'contador.twig', [
        'contador' => $contador
    ]);
});

$app->run();
