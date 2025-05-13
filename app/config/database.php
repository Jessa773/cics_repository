<?php
// config/database.php

class Database
{
    public static ?Database $instance = null;
    public PDO $conn;

    // Load credentials from environment variables for better security
    public string $host;
    public string $dbname;
    public string $username;
    public string $password;

    /**
     * Private constructor to prevent external instantiation
     */
    public function __construct()
    {
        $this->host     = getenv('DB_HOST') ?: 'localhost';
        $this->dbname   = getenv('DB_NAME') ?: 'cics_repository';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$this->host};dbname={$this->dbname};charset=utf8mb4";

        try {
            $this->conn = new PDO($dsn, $this->username, $this->password, [
                PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_EMULATE_PREPARES   => false,
            ]);
        } catch (PDOException $e) {
            error_log('Database Connection Error: ' . $e->getMessage());
            http_response_code(500); // Internal Server Error
            exit(json_encode(['success' => false, 'message' => 'Database connection failed.']));
        }
    }

    /**
     * Get the singleton instance of the Database class
     */
    public static function getInstance(): Database
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Get the PDO connection object
     */
    public function getConnection(): PDO
    {
        return $this->conn;
    }

    // Prevent cloning and unserialization
    public function __clone() {}
    public function __wakeup() {}
}
