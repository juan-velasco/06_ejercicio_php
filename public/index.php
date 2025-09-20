<?php
require '../vendor/autoload.php';

use Slim\Factory\AppFactory;
use Predis\Client as RedisClient;
use Slim\Exception\HttpNotFoundException;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

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

// Ruta /up devuelve 200 OK
$app->get('/up', function ($request, $response, $args) {
    $response->getBody()->write("OK");
    return $response->withStatus(200);
});


// Manejador para rutas no encontradas (404)
$app->addRoutingMiddleware();
$errorMiddleware = $app->addErrorMiddleware(true, true, true);
$errorMiddleware->setErrorHandler(
    HttpNotFoundException::class,
    function (
        Request $request,
        Throwable $exception,
        bool $displayErrorDetails,
        bool $logErrors,
        bool $logErrorDetails
    ) use ($app) {
        $response = $app->getResponseFactory()->createResponse();
        $response->getBody()->write('404 - Página no encontrada');
        return $response->withStatus(404);
    }
);

$app->run();
