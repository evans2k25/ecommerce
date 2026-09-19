<?php

require_once __DIR__ . '/../models/Paiement.php';

class PaymentController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function createForOrder(int $orderId, string $mode, float $amount): int
    {
        $mode = strtolower(trim($mode));
        $allowedModes = ['especes', 'wave', 'orange_money'];

        if (!in_array($mode, $allowedModes, true)) {
            throw new InvalidArgumentException('Mode de paiement invalide.');
        }

        $paiementModel = new Paiement($this->db);

        return $paiementModel->create([
            'id_commande' => $orderId,
            'mode_paiement' => $mode,
            'montant' => $amount,
            'statut' => 'en_attente',
        ]);
    }

    public function getByOrder(int $orderId): ?array
    {
        return (new Paiement($this->db))->getByCommande($orderId);
    }

    public function updateStatus(int $paymentId, string $status): bool
    {
        return (new Paiement($this->db))->updateStatus($paymentId, $status);
    }
}
