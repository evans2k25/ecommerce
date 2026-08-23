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
}