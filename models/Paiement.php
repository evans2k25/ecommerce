<?php

class Paiement
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }


    /**
     * Créer un paiement
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO paiements
            (
                id_commande,
                mode_paiement,
                montant,
                statut,
                date_paiement
            )
            VALUES
            (
                :id_commande,
                :mode_paiement,
                :montant,
                :statut,
                NOW()
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            ':id_commande'   => $data['id_commande'],
            ':mode_paiement' => $data['mode_paiement'],
            ':montant'       => $data['montant'],
            ':statut'        => $data['statut']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getByCommande(int $commandeId): ?array
    {
        $sql = "
            SELECT *
            FROM paiements
            WHERE id_commande = :id_commande
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id_commande' => $commandeId]);
        $paiement = $stmt->fetch();

        return $paiement ?: null;
    }

    public function updateStatus(int $id, string $statut): bool
    {
        $statuts = [
            'en_attente',
            'accepte',
            'refuse',
            'rembourse'
        ];

        if (!in_array($statut, $statuts, true)) {
            return false;
        }

        $sql = "
            UPDATE paiements
            SET statut = :statut
            WHERE id_paiement = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'statut' => $statut,
            'id' => $id
        ]);
    }
}