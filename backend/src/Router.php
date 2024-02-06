<?php

namespace Smadi0x86wsl\Backend;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;

/**
 * The Router class is responsible for handling incoming HTTP requests and
 * dispatching them to the appropriate controller methods based on the route definitions.
 *
 * TODO: Implement the following features:
 * Dynamic Routing: Consider implementing regex-based dynamic routing if needed.
 * Middleware Integration: Plan for middleware support for advanced use cases like authentication.
 * Performance: Monitor performance with a growing number of routes.
 */
class Router {
    /**
     * @var array $routes Stores all the route definitions.
     */
    private $routes = [];
    private $logger;

    public function __construct() {
        // Create a logger instance
        $this->logger = new Logger('my_logger');
        $this->logger->pushHandler(new StreamHandler('./src/Controller/router.log', Logger::WARNING));
    }

    /**
     * Adds a new route definition to the router.
     *
     * @param string $method The HTTP method (GET, POST, etc.).
     * @param string $pattern The URI pattern to match.
     * @param string $controller The controller class to handle the request.
     * @param string $function The controller method to call.
     */
    public function add($method, $pattern, $controller, $function) {
        $this->routes[] = [
            'method' => $method,
            'pattern' => $pattern,
            'controller' => $controller,
            'function' => $function
        ];
    }

    /**
     * Dispatches an incoming request to the appropriate controller method.
     *
     * @param Request $request The incoming request.
     * @param Response $response The response object.
     * @param Server $server The OpenSwoole server instance.
     */
    public function dispatch(Request $request, Response $response, $server) {
        $path = $request->server['request_uri'];
        $method = $request->server['request_method'];

        foreach ($this->routes as $route) {
            if ($path === $route['pattern']) {
                if ($method === $route['method']) {
                    // Pass both $server and $logger to the controller
                    $controller = new $route['controller']($server, $this->logger);
                    if (method_exists($controller, $route['function'])) {
                        $controller->{$route['function']}($request, $response);
                        return;
                    } else {
                        $this->handleError($response, 500, 'Internal Server Error: Controller method not found.');
                        return;
                    }
                } else {
                    $this->handleError($response, 405, 'Method Not Allowed');
                    return;
                }
            }
        }

        $this->handleError($response, 404, 'Not Found');
    }

    /**
     * Handles errors by sending a JSON response with the appropriate status code and message.
     *
     * @param Response $response The response object.
     * @param int $statusCode The HTTP status code.
     * @param string $message The error message.
     */
    private function handleError(Response $response, int $statusCode, string $message) {
        $response->status($statusCode);
        $response->end(json_encode(['error' => $message]));
    }
}
