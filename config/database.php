<?php

class Database
{
    private string $host = "localhost";
    private string $dbName = "ecommerce3
    ";
    private string $username = "root";
    private string $password = "";

    private ?PDO $connection = null;

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

                die(
                    "Erreur de connexion à la base de données : "
                    . $e->getMessage()
                );
            }
        }

        return $this->connection;
    }
}