<?php

namespace Smadi0x86wsl\Backend\Middleware;

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;

class JwtMiddleware
{
    private $secretKey;

    public function __construct()
    {
        // Load the JWT secret key from environment variable or configuration
        $this->secretKey = $_ENV['JWT_SECRET'];
    }

    public function __invoke(Request $request, Response $response, callable $next)
    {
        $authHeader = $request->header['authorization'] ?? '';
        $token = str_replace('Bearer ', '', $authHeader);

        if (empty($token)) {
            $response->status(401);
            $response->end(json_encode(['error' => 'Token not provided']));
            return;
        }

        try {
            $decoded = JWT::decode($token, new Key($this->secretKey, 'HS256'));

            if ($decoded->exp < time()) {
                $response->status(401);
                $response->end(json_encode(['error' => 'Token expired']));
                return;
            }

            return $next($request, $response);
        } catch (\Exception $e) {
            error_log("JWT validation error: " . $e->getMessage());
            $response->status(401);
            $response->end(json_encode(['error' => 'Invalid token']));
            return;
        }
    }
}
