<?php

class Administrateur
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /*
    |--------------------------------------------------------------------------
    | Connexion
    |--------------------------------------------------------------------------
    */

    public function login(
        string $email,
        string $motDePasse
    ): ?array {

        $sql = "
            SELECT *
            FROM administrateurs
            WHERE email = :email
            AND statut = 'actif'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'email' => $email
        ]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return null;
        }

        if (
            !password_verify(
                $motDePasse,
                $admin['mot_de_passe']
            )
        ) {
            return null;
        }

        return $admin;
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer par ID
    |--------------------------------------------------------------------------
    */

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM administrateurs
            WHERE id_administrateur = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer tous les administrateurs
    |--------------------------------------------------------------------------
    */

    public function getAll(): array
    {
        $sql = "
            SELECT
                id_administrateur,
                nom,
                prenom,
                email,
                statut,
                date_creation

            FROM administrateurs

            ORDER BY date_creation DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Créer un administrateur
    |--------------------------------------------------------------------------
    */

    public function create(array $data): int
    {
        $password = password_hash(
            $data['mot_de_passe'],
            PASSWORD_DEFAULT
        );

        $sql = "
            INSERT INTO administrateurs
            (
                nom,
                prenom,
                email,
                mot_de_passe,
                statut
            )

            VALUES
            (
                :nom,
                :prenom,
                :email,
                :mot_de_passe,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'nom' =>
                $data['nom'] ?? '',

            'prenom' =>
                $data['prenom'] ?? '',

            'email' =>
                $data['email'] ?? '',

            'mot_de_passe' =>
                $password,

            'statut' =>
                $data['statut'] ?? 'actif'
        ]);

        return (int) $this->db->lastInsertId();
    }
}