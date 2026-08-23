<?php

class Produit
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Récupérer tous les produits disponibles
     */
    public function getAll(): array
    {
        $sql = "
            SELECT
                p.id_produit,
                p.nom,
                p.description,
                p.prix,
                p.stock,
                p.image,
                p.statut,
                p.date_creation,
                c.id_categorie,
                c.nom AS categorie
            FROM produits p
            INNER JOIN categories c
                ON c.id_categorie = p.id_categorie
            WHERE p.statut = 'disponible'
            ORDER BY p.date_creation DESC
        ";

        $stmt = $this->db->query($sql);

        return $stmt->fetchAll();
    }


    /**
     * Récupérer un produit par son ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT
                p.id_produit,
                p.id_categorie,
                p.nom,
                p.description,
                p.prix,
                p.stock,
                p.image,
                p.statut,
                p.date_creation,
                c.nom AS categorie
            FROM produits p
            INNER JOIN categories c
                ON c.id_categorie = p.id_categorie
            WHERE p.id_produit = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id' => $id
        ]);

        $produit = $stmt->fetch();

        return $produit ?: null;
    }


    /**
     * Rechercher des produits
     */
    public function search(string $keyword): array
    {
        $sql = "
            SELECT
                p.id_produit,
                p.nom,
                p.description,
                p.prix,
                p.stock,
                p.image,
                c.nom AS categorie
            FROM produits p
            INNER JOIN categories c
                ON c.id_categorie = p.id_categorie
            WHERE
                p.statut = 'disponible'
                AND (
                    p.nom LIKE :keyword
                    OR p.description LIKE :keyword
                )
            ORDER BY p.nom ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':keyword' => '%' . $keyword . '%'
        ]);

        return $stmt->fetchAll();
    }


    /**
     * Récupérer les produits d'une catégorie
     */
    public function getByCategory(int $categoryId): array
    {
        $sql = "
            SELECT
                p.id_produit,
                p.nom,
                p.description,
                p.prix,
                p.stock,
                p.image,
                p.statut,
                c.nom AS categorie
            FROM produits p
            INNER JOIN categories c
                ON c.id_categorie = p.id_categorie
            WHERE
                p.id_categorie = :category_id
                AND p.statut = 'disponible'
            ORDER BY p.nom ASC
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':category_id' => $categoryId
        ]);

        return $stmt->fetchAll();
    }

    public function getAllAdmin()
{
    $sql = "
        SELECT
            p.id_produit,
            p.id_categorie,
            p.nom,
            p.description,
            p.prix,
            p.stock,
            p.image,
            p.statut,
            p.date_creation,
            p.date_modification,
            c.nom AS categorie_nom

        FROM produits p

        INNER JOIN categories c
            ON c.id_categorie = p.id_categorie

        ORDER BY p.date_creation DESC
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute();

    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

public function delete($id)
{
    $sql = "
        DELETE FROM produits
        WHERE id_produit = :id
    ";

    $stmt = $this->db->prepare($sql);

    $stmt->execute([
        'id' => (int) $id
    ]);

    return $stmt->rowCount() > 0;
}

public function archive(int $idProduit): bool
{
    $sql = "
        UPDATE produits
        SET statut = 'archive'
        WHERE id_produit = :id_produit
    ";

    $stmt = $this->db->prepare($sql);

    return $stmt->execute([
        'id_produit' => $idProduit
    ]);
}
}