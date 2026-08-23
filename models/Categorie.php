<?php

class Categorie
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer toutes les catégories actives
    |--------------------------------------------------------------------------
    */

    public function getAll(): array
    {
        $sql = "
            SELECT
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation,

                COUNT(p.id_produit) AS total_products

            FROM categories c

            LEFT JOIN produits p
                ON p.id_categorie = c.id_categorie

            WHERE c.statut = 'active'

            GROUP BY
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation

            ORDER BY c.nom ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer toutes les catégories
    | Actives + inactives
    |--------------------------------------------------------------------------
    */

    public function getAllAdmin(): array
    {
        $sql = "
            SELECT
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation,

                COUNT(p.id_produit) AS total_products

            FROM categories c

            LEFT JOIN produits p
                ON p.id_categorie = c.id_categorie

            GROUP BY
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation

            ORDER BY c.date_creation DESC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }


    /*
    |--------------------------------------------------------------------------
    | Récupérer une catégorie par ID
    |--------------------------------------------------------------------------
    */

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation,

                COUNT(p.id_produit) AS total_products

            FROM categories c

            LEFT JOIN produits p
                ON p.id_categorie = c.id_categorie

            WHERE c.id_categorie = :id

            GROUP BY
                c.id_categorie,
                c.nom,
                c.description,
                c.image,
                c.statut,
                c.date_creation

            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $categorie = $stmt->fetch(PDO::FETCH_ASSOC);

        return $categorie ?: null;
    }


    /*
    |--------------------------------------------------------------------------
    | Créer une catégorie
    |--------------------------------------------------------------------------
    */

    public function create(array $data): int
    {
        $sql = "
            INSERT INTO categories
            (
                nom,
                description,
                image,
                statut
            )

            VALUES
            (
                :nom,
                :description,
                :image,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'nom' =>
                $data['nom'] ?? '',

            'description' =>
                $data['description'] ?? null,

            'image' =>
                $data['image'] ?? null,

            'statut' =>
                $data['statut'] ?? 'active'
        ]);

        return (int) $this->db->lastInsertId();
    }


    /*
    |--------------------------------------------------------------------------
    | Modifier une catégorie
    |--------------------------------------------------------------------------
    */

    public function update(int $id, array $data): bool
    {
        $sql = "
            UPDATE categories

            SET
                nom = :nom,
                description = :description,
                image = :image,
                statut = :statut

            WHERE id_categorie = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'nom' =>
                $data['nom'] ?? '',

            'description' =>
                $data['description'] ?? null,

            'image' =>
                $data['image'] ?? null,

            'statut' =>
                $data['statut'] ?? 'active',

            'id' =>
                $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Supprimer une catégorie
    |--------------------------------------------------------------------------
    */

    public function delete(int $id): bool
    {
        $sql = "
            DELETE FROM categories

            WHERE id_categorie = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'id' => $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Activer / désactiver une catégorie
    |--------------------------------------------------------------------------
    */

    public function changeStatus(
        int $id,
        string $statut
    ): bool {

        if (
            !in_array(
                $statut,
                ['active', 'inactive'],
                true
            )
        ) {
            return false;
        }

        $sql = "
            UPDATE categories

            SET statut = :statut

            WHERE id_categorie = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'statut' => $statut,
            'id' => $id
        ]);
    }


    /*
    |--------------------------------------------------------------------------
    | Compter les catégories
    |--------------------------------------------------------------------------
    */

    public function count(): int
    {
        $sql = "
            SELECT COUNT(*)
            FROM categories
            WHERE statut = 'active'
        ";

        return (int) $this->db
            ->query($sql)
            ->fetchColumn();
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifier si une catégorie existe
    |--------------------------------------------------------------------------
    */

    public function exists(int $id): bool
    {
        $sql = "
            SELECT COUNT(*)

            FROM categories

            WHERE id_categorie = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        return (int) $stmt->fetchColumn() > 0;
    }
}