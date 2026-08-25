<?php

class LigneCommande
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /**
     * Ajouter une ligne à la commande
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO lignes_commande
            (
                id_commande,
                id_produit,
                quantite,
                prix_unitaire,
                sous_total
            )
            VALUES
            (
                :id_commande,
                :id_produit,
                :quantite,
                :prix_unitaire,
                :sous_total
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_commande'   => $data['id_commande'],
            ':id_produit'    => $data['id_produit'],
            ':quantite'      => $data['quantite'],
            ':prix_unitaire' => $data['prix_unitaire'],
            ':sous_total'    => $data['sous_total']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getByCommande(int $commandeId): array
    {
        $sql = "
            SELECT
                lc.id_ligne_commande,
                lc.quantite,
                lc.prix_unitaire,
                lc.sous_total,
                p.nom AS produit_nom,
                p.image
            FROM lignes_commande lc
            INNER JOIN produits p
                ON p.id_produit = lc.id_produit
            WHERE lc.id_commande = :id_commande
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_commande' => $commandeId]);

        return $stmt->fetchAll();
    }
}