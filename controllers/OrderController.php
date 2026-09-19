<?php

require_once __DIR__ . '/../models/Commande.php';
require_once __DIR__ . '/../models/LigneCommande.php';
require_once __DIR__ . '/../models/Livraison.php';
require_once __DIR__ . '/../models/Paiement.php';

class OrderController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function listAll(): array
    {
        return (new Commande($this->db))->getAll();
    }

    public function getById(int $id): ?array
    {
        return (new Commande($this->db))->getById($id);
    }

    public function getDetails(int $id): array
    {
        $commande = $this->getById($id);

        if (!$commande) {
            return [];
        }

        $ligneCommande = new LigneCommande($this->db);
        $livraison = new Livraison($this->db);
        $paiement = new Paiement($this->db);

        return [
            'commande' => $commande,
            'lignes' => $ligneCommande->getByCommande($id),
            'livraison' => $livraison->getByCommande($id),
            'paiement' => $paiement->getByCommande($id),
        ];
    }

    public function updateStatus(int $id, string $status): bool
    {
        return (new Commande($this->db))->updateStatus($id, $status);
    }
}
