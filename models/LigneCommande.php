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
}