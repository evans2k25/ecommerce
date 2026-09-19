<?php

require_once __DIR__ . '/../models/Produit.php';

class ProductController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listAll(): array
    {
        return (new Produit($this->db))->getAll();
    }

    public function listByCategory(int $categoryId): array
    {
        return (new Produit($this->db))->getByCategory($categoryId);
    }

    public function getById(int $id): ?array
    {
        return (new Produit($this->db))->getById($id);
    }

    public function create(array $data): int
    {
        $nom = trim((string) ($data['nom'] ?? ''));
        $idCategorie = (int) ($data['id_categorie'] ?? 0);
        $prix = (float) ($data['prix'] ?? 0);
        $stock = (int) ($data['stock'] ?? 0);
        $description = $data['description'] ?? null;
        $image = $data['image'] ?? null;
        $statut = $data['statut'] ?? 'disponible';

        if ($nom === '') {
            throw new InvalidArgumentException('Le nom du produit est obligatoire.');
        }

        if ($idCategorie <= 0) {
            throw new InvalidArgumentException('La catégorie est obligatoire.');
        }

        if ($prix < 0) {
            throw new InvalidArgumentException('Le prix ne peut pas être négatif.');
        }

        if ($stock < 0) {
            throw new InvalidArgumentException('Le stock ne peut pas être négatif.');
        }

        $allowedStatuts = ['disponible', 'indisponible', 'archive'];
        if (!in_array($statut, $allowedStatuts, true)) {
            $statut = 'disponible';
        }

        $sql = "
            INSERT INTO produits (
                id_categorie,
                nom,
                description,
                prix,
                stock,
                image,
                statut
            ) VALUES (
                :id_categorie,
                :nom,
                :description,
                :prix,
                :stock,
                :image,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            ':id_categorie' => $idCategorie,
            ':nom' => $nom,
            ':description' => $description !== '' ? $description : null,
            ':prix' => $prix,
            ':stock' => $stock,
            ':image' => $image,
            ':statut' => $statut,
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $produit = new Produit($this->db);

        return $produit->update($id, $data);
    }

    public function delete(int $id): bool
    {
        $produit = new Produit($this->db);

        return $produit->delete($id);
    }

    public function archive(int $id): bool
    {
        $produit = new Produit($this->db);

        return $produit->archive($id);
    }
}
