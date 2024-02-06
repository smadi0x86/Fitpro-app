<?php

use OpenSwoole\Http\Server;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Smadi0x86wsl\Backend\Router;
use Smadi0x86wsl\Backend\Controller\UserController;
use Smadi0x86wsl\Backend\Database\DatabaseConnection;
use Smadi0x86wsl\Backend\Service\VerificationService;
use Smadi0x86wsl\Backend\Middleware\JwtMiddleware;
use Smadi0x86wsl\Backend\Utils\HttpUtils;
use Smadi0x86wsl\Backend\Utils\Mailer;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

require_once __DIR__ . '/../vendor/autoload.php';

$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

if (!isset($_ENV['DB_HOST'])) {
    throw new \Exception("Environment variable DB_HOST is not set.");
}

$logger = new Logger('my_logger');
$logger->pushHandler(new StreamHandler(__DIR__.'/fitpro_app.log', Logger::DEBUG));

$server = new Server('0.0.0.0', 9501);
$server->set([
    'worker_num' => 2, // The number of worker processes (which handle requests)
    'task_worker_num' => 2, // The number of task worker processes (which handle asynchronous tasks)
    'backlog' => 128, // The number of connections that can be queued by the server (which can be useful for handling bursts of traffic)
]);

$router = new Router();
$dbConnection = DatabaseConnection::getInstance()->getConnection();
$verificationService = new VerificationService($dbConnection, $logger, new Mailer());

$router->add('POST', '/api/register', UserController::class, 'register');
$router->add('POST', '/api/login', UserController::class, 'login');
$router->add('GET', '/api/verify-email', UserController::class, 'verifyEmail');
$router->add('POST', '/api/change-password', UserController::class, 'changePassword');
$router->add('GET', '/test-jwt', UserController::class, 'testJwt');

// Stripe
$router->add('POST', '/api/create-checkout-session', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'createCheckoutSession');
// $router->add('POST', '/api/create-membership-session', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'createMembershipSession');

$router->add('POST', '/api/create-membership', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'createMembership');
$router->add('GET', '/api/get-membership', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'getMembershipDetails');
$router->add('POST', '/api/update-membership', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'updateMembership');
$router->add('POST', '/api/delete-membership', \Smadi0x86wsl\Backend\Controller\StripeController::class, 'deleteMembership');

// Instantiate the JWT middleware
$jwtMiddleware = new JwtMiddleware();

$server->on('Request', function (Request $request, Response $response) use ($router, $logger, $server, $jwtMiddleware) {
    HttpUtils::setCORSHeaders($response);

    if ($request->getMethod() === 'OPTIONS') {
        $response->status(204);
        $response->end();
        return;
    }

    // Define public routes that don't require JWT
    $publicRoutes = ['/api/register', '/api/login', '/api/verify-email', '/api/create-membership', '/api/get-membership', '/api/update-membership', '/api/delete-membership'];

    $requestedRoute = $request->server['request_uri'];
    $isPublicRoute = false;

    // Check if the requested route is public
    foreach ($publicRoutes as $route) {
        if (strpos($requestedRoute, $route) === 0) {
            $isPublicRoute = true;
            break;
        }
    }

    if ($isPublicRoute) {
        $router->dispatch($request, $response, $server);
    } else {
        // Use the JWT middleware for protected routes
        $jwtMiddleware($request, $response, function($request, $response) use ($router, $server) {
            $router->dispatch($request, $response, $server);
        });
    }
});

$server->on('WorkerStart', function ($server, $workerId) {
    echo "Worker Started: {$workerId}\n";
});

$server->on('Start', function ($server) {
    echo "OpenSwoole HTTP Server Started @ 0.0.0.0:9501\n";
});

$server->on('Task', function ($server, $task_id, $reactorId, $data) use ($verificationService) {
    if ($data['type'] === 'sendVerificationEmail') {
        $verificationService->sendVerificationEmail($data['email'], $data['token']);
    }
    $server->finish(["status" => "success", "task_id" => $task_id]);
});

$server->on('Finish', function ($server, $task_id, $data) {
    echo "Sending Email Verification, Task #$task_id finished successfully!\n";
});

$server->on('Shutdown', function ($server) {
    echo "Server shutting down...\n";
});

$server->on('WorkerStop', function ($server, $workerId) {
    echo "Worker Stopped: {$workerId}\n";
});

$server->start();
