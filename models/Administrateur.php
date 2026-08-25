<?php

class Administrateur
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function login(string $email, string $motDePasse): ?array
    {
        $sql = "
            SELECT *
            FROM administrateurs
            WHERE email = :email
            AND statut = 'actif'
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['email' => $email]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$admin) {
            return null;
        }

        if (!password_verify($motDePasse, $admin['mot_de_passe'])) {
            return null;
        }

        return $admin;
    }

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM administrateurs
            WHERE id_admin = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }

    public function findByEmail(string $email, ?int $excludeId = null): ?array
    {
        $sql = "
            SELECT *
            FROM administrateurs
            WHERE email = :email
        ";

        $params = ['email' => $email];

        if ($excludeId !== null) {
            $sql .= " AND id_admin != :id";
            $params['id'] = $excludeId;
        }

        $sql .= " LIMIT 1";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $admin = $stmt->fetch(PDO::FETCH_ASSOC);

        return $admin ?: null;
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                id_admin,
                nom,
                prenom,
                email,
                role,
                statut,
                date_creation
            FROM administrateurs
            ORDER BY date_creation DESC
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

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
                role,
                statut
            )
            VALUES
            (
                :nom,
                :prenom,
                :email,
                :mot_de_passe,
                :role,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'nom' => $data['nom'] ?? '',
            'prenom' => $data['prenom'] ?? '',
            'email' => $data['email'] ?? '',
            'mot_de_passe' => $password,
            'role' => $data['role'] ?? 'admin',
            'statut' => $data['statut'] ?? 'actif'
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE administrateurs
            SET
                nom = :nom,
                prenom = :prenom,
                email = :email,
                role = :role,
                statut = :statut
            WHERE id_admin = :id
        ";

        $params = [
            'nom' => $data['nom'] ?? '',
            'prenom' => $data['prenom'] ?? '',
            'email' => $data['email'] ?? '',
            'role' => $data['role'] ?? 'admin',
            'statut' => $data['statut'] ?? 'actif',
            'id' => $id
        ];

        if (!empty($data['mot_de_passe'])) {
            $sql = "
                UPDATE administrateurs
                SET
                    nom = :nom,
                    prenom = :prenom,
                    email = :email,
                    mot_de_passe = :mot_de_passe,
                    role = :role,
                    statut = :statut
                WHERE id_admin = :id
            ";

            $params['mot_de_passe'] = password_hash(
                $data['mot_de_passe'],
                PASSWORD_DEFAULT
            );
        }

        $stmt = $this->db->prepare($sql);

        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $sql = "
            DELETE FROM administrateurs
            WHERE id_admin = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute(['id' => $id]);
    }
}
