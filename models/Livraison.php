<?php

class Livraison
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /**
     * Créer une livraison
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO livraisons (
                id_commande,
                adresse_livraison,
                ville,
                commune,
                telephone,
                frais_livraison,
                statut
            )
            VALUES (
                :id_commande,
                :adresse_livraison,
                :ville,
                :commune,
                :telephone,
                :frais_livraison,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id_commande' =>
                (int) $data['id_commande'],

            'adresse_livraison' =>
                $data['adresse_livraison'],

            'ville' =>
                $data['ville'],

            'commune' =>
                $data['commune'],

            'telephone' =>
                $data['telephone'],

            'frais_livraison' =>
                (float) $data['frais_livraison'],

            'statut' =>
                $data['statut']
        ]);

        return (int) $this->db->lastInsertId();
    }


    /**
     * Récupérer une livraison par ID
     */
    public function getById(int $id): ?array
    {
        $sql = "
            SELECT *
            FROM livraisons
            WHERE id_livraison = :id
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id' => $id
        ]);

        $livraison =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $livraison ?: null;
    }


    /**
     * Récupérer une livraison par commande
     */
    public function getByCommande(int $commandeId): ?array
    {
        $sql = "
            SELECT *
            FROM livraisons
            WHERE id_commande = :id_commande
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id_commande' =>
                $commandeId
        ]);

        $livraison =
            $stmt->fetch(PDO::FETCH_ASSOC);

        return $livraison ?: null;
    }


    /**
     * Modifier le statut
     */
    public function updateStatus(
        int $id,
        string $statut
    ): bool {

        $statuts = [
            'en_attente',
            'en_preparation',
            'expediee',
            'livree',
            'annulee'
        ];

        if (!in_array($statut, $statuts, true)) {
            return false;
        }

        $sql = "
            UPDATE livraisons
            SET statut = :statut
            WHERE id_livraison = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'statut' => $statut,
            'id' => $id
        ]);
    }
}