<?php

namespace Smadi0x86wsl\Backend\Controller;

use OpenSwoole\Http\Request;
use OpenSwoole\Http\Response;
use Smadi0x86wsl\Backend\Service\UserService;
use Smadi0x86wsl\Backend\Database\DatabaseConnection;
use DateTimeImmutable;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

/**
 * The UserController handles user-related HTTP requests.
 * This controller includes the register endpoint for user registration.
 *
 * TODO for Production:
 * - Implement more comprehensive error handling and response messaging.
 * - Securely manage sensitive data and user authentication.
 * - Integrate advanced features such as rate limiting and user activity logging.
 */

/**
 * @OA\Info(title="Swagger OpenSwoole API", version="0.1")
 * @OA\Server(url="http://localhost:9501", description="API Server")
 */

class UserController {
    private $userService;
    private $server;
    private $logger;

    // Modify the constructor to accept a Logger instance
    public function __construct($server, \Monolog\Logger $logger) {
        $this->server = $server;
        $this->logger = $logger; // Assign the logger

        $dbConnection = DatabaseConnection::getInstance()->getConnection();
        $this->userService = new UserService($dbConnection, $this->logger); // Pass the logger to UserService
    }

    // The testJwt method is a placeholder for a protected endpoint that requires JWT authentication
    public function testJwt(Request $request, Response $response) {
        return $response->end(json_encode(['message' => 'JWT is valid']));
    }

    /**
     * @OA\Post(
     *     path="/register",
     *     tags={"User"},
     *     summary="Register a new user",
     *     operationId="registerUser",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User registration data",
     *         @OA\JsonContent(
     *             required={"username", "password", "email"},
     *             @OA\Property(property="username", type="string", example="user1"),
     *             @OA\Property(property="password", type="string", format="password", example="pass123"),
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User registered successfully, a verification email has been sent."
     *     )
     * )
     */
    public function register(Request $request, Response $response) {
        $data = json_decode($request->getContent(), true);

        try {
            $verificationToken = bin2hex(random_bytes(16));
            $user = $this->userService->registerUser($data['username'], $data['password'], $data['email'], $verificationToken);

            // Correctly retrieve the email from the user object
            $userEmail = $user->getEmail();  // Make sure this method exists in your User class

            // Queue an email verification task
            $this->server->task([
                'type' => 'sendVerificationEmail',
                'email' => $user->getEmail(),
                'token' => $verificationToken
            ]);

            // Send a success response
            $response->header('Content-Type', 'application/json');
            $response->status(200);
            $response->end(json_encode(['message' => 'User registered successfully, a verification email has been sent.']));

        } catch (\InvalidArgumentException $e) {
            $this->logger->error("Registration error: " . $e->getMessage());
            $response->header('Content-Type', 'application/json');
            $response->status(409); // conflict e.g. username or email already exists
            $response->end(json_encode(['error' => $e->getMessage()]));

        } catch (\Exception $e) {
            $this->logger->error("Unexpected error during registration: " . $e->getMessage());
            $response->header('Content-Type', 'application/json');
            $response->status(500);
            $response->end(json_encode(['error' => 'An error occurred.']));
        }
    }

    /**
     * @OA\Post(
     *     path="/login",
     *     tags={"User"},
     *     summary="Log in a user",
     *     operationId="loginUser",
     *     @OA\RequestBody(
     *         required=true,
     *         description="User login data",
     *         @OA\JsonContent(
     *             required={"email", "password"},
     *             @OA\Property(property="email", type="string", format="email", example="user@example.com"),
     *             @OA\Property(property="password", type="string", format="password", example="pass123")
     *         )
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="User logged in successfully."
     *     )
     * )
     */
    public function login(Request $request, Response $response) {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $password = $data['password'] ?? null;

        $authResult = $this->userService->authenticate($email, $password);

        if ($authResult['success']) {
            $user = $authResult['user'];

            // JWT creation process
            $tokenId    = base64_encode(random_bytes(32));
            $issuedAt   = new DateTimeImmutable();
            $expire     = $issuedAt->modify('+24 hours')->getTimestamp(); // Set expiration time to 24 hours
            $serverName = "fitpro.com";

            $data = [
                'iat'  => $issuedAt->getTimestamp(),
                'jti'  => $tokenId,
                'iss'  => $serverName,
                'nbf'  => $issuedAt->getTimestamp(),
                'exp'  => $expire,
                'data' => [
                    'userId' => $user['id'],
                ]
            ];

            $secretKey = $_ENV['JWT_SECRET'];
            $jwt = JWT::encode($data, $secretKey, 'HS256');

            $response->header('Content-Type', 'application/json');
            $response->status(200);
            $response->end(json_encode(['message' => 'User logged in successfully.', 'token' => $jwt]));
        } else {
            $response->status(401);
            $response->end(json_encode(['error' => $authResult['message']]));
        }
    }

    public function verifyEmail(Request $request, Response $response) {
        global $verificationService;

        $token = $request->get['token'] ?? null;

        if ($token && $verificationService->verifyEmailToken($token)) {
            $response->status(200);
            $response->end(json_encode(['message' => 'Email verified successfully.']));
        } else {
            $response->status(400);
            $response->end(json_encode(['error' => 'Invalid or expired token.']));
        }
    }

    /**
     * Endpoint to change a user's password.
     */
    public function changePassword(Request $request, Response $response) {
        $data = json_decode($request->getContent(), true);
        $email = $data['email'] ?? null;
        $currentPassword = $data['currentPassword'] ?? null;
        $newPassword = $data['newPassword'] ?? null;

        if ($email && $currentPassword && $newPassword) {
            $result = $this->userService->changePassword($email, $currentPassword, $newPassword);

            if ($result) {
                $response->status(200);
                $response->end(json_encode(['message' => 'Password changed successfully.']));
            } else {
                $response->status(400);
                $response->end(json_encode(['error' => 'Failed to change password.']));
            }
        } else {
            $response->status(400);
            $response->end(json_encode(['error' => 'Invalid request.']));
        }
    }
}
