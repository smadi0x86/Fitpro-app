<?php

namespace Smadi0x86wsl\Backend\Database;

use PDO;
use PDOException;

/**
 * The DatabaseConnection class manages a single instance of the database connection.
 * It implements the Singleton pattern to ensure only one instance of the connection is created.
 *
 * TODO for Production:
 * - Ensure that environment variables are securely managed.
 * - Consider implementing more robust error handling and logging mechanisms.
 * - Test performance under load and optimize settings.
 */
class DatabaseConnection {
    /**
     * @var DatabaseConnection|null The single instance of the class.
     */
    private static $instance = null;

    /**
     * @var PDO The PDO database connection.
     */
    private $connection;

    /**
     * Constructor is private to prevent multiple instances.
     */
    private function __construct() {
        // Retrieve database configuration from environment variables
        $host = $_ENV['DB_HOST']; // Use localhost if running locally specially for unit tests
        $port = $_ENV['DB_PORT'];
        $db = $_ENV['DB_DATABASE'];
        $user = $_ENV['DB_USERNAME'];
        $pass = $_ENV['DB_PASSWORD'];

        $dsn = "pgsql:host=$host;port=$port;dbname=$db"; // Data Source Name (DSN) for the connection
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, // Throw exceptions on errors
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC, // Return associative arrays for the sake of security and consistency with JSON responses
            PDO::ATTR_EMULATE_PREPARES => false, // Use native prepared statements
            PDO::ATTR_PERSISTENT => true, // Persistent connections can improve performance
        ];

        try {
            $this->connection = new PDO($dsn, $user, $pass, $options);
        } catch (PDOException $e) {
            // Enhanced error handling for production environment
            throw new \Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Returns the single instance of DatabaseConnection for the application. If the instance does not exist, it is created.
     *
     * @return DatabaseConnection The single instance.
     */
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self(); // singleton design pattern to ensure only one instance of the connection is created
        }
        return self::$instance;
    }

    /**
     * Returns the PDO database connection. This method is used to retrieve the connection from the singleton instance.
     *
     * @return PDO The PDO database connection.
     */
    public function getConnection() {
        return $this->connection;
    }

    /**
     * Prevents cloning of the instance.
     */
    private function __clone() { }

    /**
     * Prevents unserialization of the instance.
     */
    public function __wakeup() {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}

// Usage example:
// $dbConnection = DatabaseConnection::getInstance()->getConnection();
