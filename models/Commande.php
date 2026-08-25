<?php

class Commande
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    /**
     * Générer un numéro de commande
     */
    public function generateNumber(): string
    {
        do {
            $numero = 'CMD-' .
                date('Ymd') . '-' .
                strtoupper(
                    substr(bin2hex(random_bytes(4)), 0, 8)
                );

            $sql = "
                SELECT COUNT(*)
                FROM commandes
                WHERE numero_commande = :numero
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':numero' => $numero
            ]);

        } while ((int) $stmt->fetchColumn() > 0);

        return $numero;
    }

    /**
     * Générer une référence unique
     */
    public function generateReference(): string
    {
        do {
            $reference = 'REF-' .
                strtoupper(
                    substr(bin2hex(random_bytes(5)), 0, 10)
                );

            $sql = "
                SELECT COUNT(*)
                FROM commandes
                WHERE reference = :reference
            ";

            $stmt = $this->db->prepare($sql);
            $stmt->execute([
                ':reference' => $reference
            ]);

        } while ((int) $stmt->fetchColumn() > 0);

        return $reference;
    }

    /**
     * Créer une commande
     */
    public function create(array $data): int
    {
        $sql = "
            INSERT INTO commandes (
                id_client,
                numero_commande,
                reference,
                montant_total,
                statut
            )
            VALUES (
                :id_client,
                :numero_commande,
                :reference,
                :montant_total,
                :statut
            )
        ";

        $stmt = $this->db->prepare($sql);

        $stmt->execute([
            'id_client'       => $data['id_client'],
            'numero_commande' => $data['numero_commande'],
            'reference'       => $data['reference'],
            'montant_total'   => $data['montant_total'],
            'statut'          => $data['statut']
        ]);

        return (int) $this->db->lastInsertId();
    }

    public function getAll(): array
    {
        $sql = "
            SELECT
                c.id_commande,
                c.numero_commande,
                c.reference,
                c.montant_total,
                c.statut,
                c.date_commande,
                cl.nom,
                cl.prenom,
                cl.email,
                cl.telephone
            FROM commandes c
            INNER JOIN clients cl
                ON cl.id_client = c.id_client
            ORDER BY c.date_commande DESC
        ";

        return $this->db->query($sql)->fetchAll();
    }

    public function getById(int $id): ?array
    {
        $sql = "
            SELECT
                c.id_commande,
                c.id_client,
                c.numero_commande,
                c.reference,
                c.montant_total,
                c.statut,
                c.date_commande,
                cl.nom,
                cl.prenom,
                cl.email,
                cl.telephone
            FROM commandes c
            INNER JOIN clients cl
                ON cl.id_client = c.id_client
            WHERE c.id_commande = :id
            LIMIT 1
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['id' => $id]);
        $commande = $stmt->fetch();

        return $commande ?: null;
    }

    public function updateStatus(int $id, string $statut): bool
    {
        $statuts = [
            'en_attente',
            'confirmee',
            'preparee',
            'expediee',
            'livree',
            'annulee'
        ];

        if (!in_array($statut, $statuts, true)) {
            return false;
        }

        $sql = "
            UPDATE commandes
            SET statut = :statut
            WHERE id_commande = :id
        ";

        $stmt = $this->db->prepare($sql);

        return $stmt->execute([
            'statut' => $statut,
            'id' => $id
        ]);
    }
}