<?php

require_once __DIR__ . '/env.php';

class Database
{
    private string $host;
    private string $dbName;
    private string $username;
    private string $password;

    private ?PDO $connection = null;

    public function __construct()
    {
        $this->host = getenv('DB_HOST') ?: 'localhost';
        $this->dbName = getenv('DB_NAME') ?: 'ecommerce3';
        $this->username = getenv('DB_USER') ?: 'root';
        $this->password = getenv('DB_PASS') ?: '';
    }

    /**
     * Établit la connexion à la base de données
     */
    public function getConnection(): PDO
    {
        if ($this->connection === null) {
            try {
                $dsn = "mysql:host={$this->host};dbname={$this->dbName};charset=utf8mb4";

                $this->connection = new PDO(
                    $dsn,
                    $this->username,
                    $this->password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                        PDO::ATTR_EMULATE_PREPARES => false
                    ]
                );

            } catch (PDOException $e) {
                // Log the real error for debugging
                error_log("Database connection error: " . $e->getMessage());

                // Show a generic message to the user and stop execution
                echo "<p>Impossible de se connecter à la base de données. Contactez l'administrateur.</p>";
                exit;
            }
        }

        return $this->connection;
    }
}