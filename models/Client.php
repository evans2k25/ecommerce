<?php

class Client
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /**
     * Rechercher un client par email
     */
    public function findByEmail(string $email): ?array
    {
        $sql = "
            SELECT *
            FROM clients
            WHERE email = :email
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':email' => $email
        ]);

        $client = $stmt->fetch();

        return $client ?: null;
    }


    /**
     * Créer un client
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO clients
            (
                nom,
                prenom,
                email,
                telephone,
                adresse,
                ville,
                commune,
                date_creation
            )
            VALUES
            (
                :nom,
                :prenom,
                :email,
                :telephone,
                :adresse,
                :ville,
                :commune,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':nom'       => $data['nom'],
            ':prenom'    => $data['prenom'],
            ':email'     => $data['email'],
            ':telephone' => $data['telephone'],
            ':adresse'   => $data['adresse'],
            ':ville'     => $data['ville'],
            ':commune'   => $data['commune']
        ]);

        return (int) $this->db->lastInsertId();
    }
}