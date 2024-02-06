<?php

namespace Smadi0x86wsl\Backend\Service;

use Smadi0x86wsl\Backend\Model\User;
use PDO; // Import the PDO class for database access
use PDOException;

/**
 * The UserService class provides functionality for user-related operations, such as registration.
 *
 * TODO for Production:
 * - Implement more robust input validation and sanitation.
 * - Consider implementing a more comprehensive user management system.
 * - Ensure secure handling of user data and passwords.
 */
class UserService {
    /**
     * @var \PDO The database connection object.
     */
    private $db;

    /**
     * @var \Monolog\Logger The logger instance.
     */
    private $logger;

    /**
     * Constructs the UserService with dependencies.
     *
     * @param \PDO $dbConnection The database connection.
     * @param \Monolog\Logger $logger The logger instance.
     */
    public function __construct($dbConnection, $logger) {
        $this->db = $dbConnection;
        $this->logger = $logger;
    }

    /**
     * Registers a new user with the given credentials.
     *
     * @param string $username The user's username.
     * @param string $password The user's password.
     * @param string $email The user's email.
     * @return User The newly created user object.
     */
    public function registerUser($username, $password, $email, $verificationToken) {
        $this->validateUserInput($username, $password, $email);

        if ($this->doesUserExist($username, $email)) {
            throw new \InvalidArgumentException('Username or Email already exists.');
        }

        return $this->createUser($username, $password, $email, $verificationToken);
    }

    /**
     * Validates user input for registration.
     *
     * @param string $username The user's username.
     * @param string $password The user's password.
     * @param string $email The user's email.
     */
    private function validateUserInput(&$username, &$password, &$email) {
        // Sanitize and validate input
        $username = htmlspecialchars(trim($username));
        $email = filter_var(trim($email), FILTER_SANITIZE_EMAIL);
        $password = trim($password);
        if (false === $email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new \InvalidArgumentException('Invalid email format.');
        }
        if (empty($username)) throw new \InvalidArgumentException('Username is required.');
        if (empty($email)) throw new \InvalidArgumentException('Email is required.');
        if (empty($password)) throw new \InvalidArgumentException('Password is required.');
    }

    /**
     * Checks if a user with the given username or email already exists.
     *
     * @param string $username The user's username.
     * @param string $email The user's email.
     * @return bool True if the user exists, false otherwise.
     */
    private function doesUserExist($username, $email) {
        $checkSql = "SELECT COUNT(*) FROM users WHERE username = :username OR email = :email";
        $checkStmt = $this->db->prepare($checkSql);
        $checkStmt->execute(['username' => $username, 'email' => $email]);
        return $checkStmt->fetchColumn() > 0;
    }

        /**
         * Creates a new user with the provided credentials.
         *
         * @param string $username The user's username.
         * @param string $password The user's password.
         * @param string $email The user's email.
         * @return User The newly created user object.
        */
        private function createUser($username, $password, $email, $verificationToken) {
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        $this->db->beginTransaction();
        try {
            $sql = "INSERT INTO users (username, password, email, verification_token) VALUES (:username, :password, :email, :token)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                'username' => $username,
                'password' => $hashedPassword,
                'email' => $email,
                'token' => $verificationToken
            ]);

            $this->db->commit();
            $id = $this->db->lastInsertId();
            $this->logger->info("New user created: {$username}");

            return new User($id, $username, $hashedPassword, $email);
        } catch (PDOException $e) {
            $this->db->rollback();
            $this->logger->error("Database error during user creation: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Authenticates a user and generates a JWT upon successful authentication.
     *
     * @param string $email User's email.
     * @param string $password User's password.
     * @return array An array containing success status and a message or JWT token.
    */
    public function authenticate($email, $password) {
        try {
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(\PDO::FETCH_ASSOC);

            if (!$user) {
                return ['success' => false, 'message' => 'User not found.'];
            }

            if (!password_verify($password, $user['password'])) {
                $this->logger->info("Login failed: Password mismatch for user $email.");
                return ['success' => false, 'message' => 'Invalid login credentials.'];
            }

            if (!$user['is_email_verified']) {
                $this->logger->info("Login failed: Email not verified for user $email.");
                return ['success' => false, 'message' => 'Please verify your email.'];
            }

            // Return user data on successful authentication
            return ['success' => true, 'user' => $user];
        } catch (PDOException $e) {
            $this->logger->error("Database error during authentication: " . $e->getMessage());
            return ['success' => false, 'message' => 'An error occurred during authentication.'];
        }
    }

    /**
     * Changes the user's password after validating the current password.
     *
     * @param int $userId The user's ID.
     * @param string $currentPassword The user's current password.
     * @param string $newPassword The user's new password.
     * @return bool True on success, false on failure.
     */
    public function changePassword($email, $currentPassword, $newPassword) {
        try {
            // Fetch the current user data from the database using email
            $stmt = $this->db->prepare("SELECT * FROM users WHERE email = :email");
            $stmt->execute(['email' => $email]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                throw new InvalidArgumentException('User not found.');
            }

            // Verify the current password
            if (!password_verify($currentPassword, $user['password'])) {
                throw new InvalidArgumentException('Current password is incorrect.');
            }

            // Hash the new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);

            // Update the user's password in the database
            $updateStmt = $this->db->prepare("UPDATE users SET password = :password WHERE email = :email");
            $updateStmt->execute([
                'email' => $email,
                'password' => $hashedPassword
            ]);

            return true;
        } catch (PDOException $e) {
            $this->logger->error("Database error during password change: " . $e->getMessage());
            return false;
        }
    }
}

