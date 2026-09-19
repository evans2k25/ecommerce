<?php

require_once __DIR__ . '/../models/Client.php';
require_once __DIR__ . '/../models/Commande.php';
require_once __DIR__ . '/../models/LigneCommande.php';
require_once __DIR__ . '/../models/Livraison.php';
require_once __DIR__ . '/../models/Paiement.php';
require_once __DIR__ . '/../models/Produit.php';

class CheckoutController
{
    private PDO $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function buildCartSummary(array $cart): array
    {
        if (empty($cart)) {
            throw new RuntimeException('Le panier est vide.');
        }

        $produitModel = new Produit($this->db);
        $items = [];
        $subtotal = 0.0;

        foreach ($cart as $productId => $item) {
            $id = (int) $productId;
            $quantity = (int) ($item['quantity'] ?? 0);

            if ($quantity <= 0) {
                continue;
            }

            $produit = $produitModel->getById($id);
            if (!$produit) {
                throw new RuntimeException('Un produit du panier est introuvable.');
            }

            if ((int) $produit['stock'] < $quantity) {
                throw new RuntimeException(
                    'Le produit « ' . $produit['nom'] . ' » ne dispose pas de suffisamment de stock.'
                );
            }

            $lineSubtotal = (float) $produit['prix'] * $quantity;
            $subtotal += $lineSubtotal;

            $items[] = [
                'id_produit' => $id,
                'nom' => $produit['nom'],
                'prix' => (float) $produit['prix'],
                'quantity' => $quantity,
                'subtotal' => $lineSubtotal,
                'stock' => (int) $produit['stock'],
            ];
        }

        if (empty($items)) {
            throw new RuntimeException('Aucun produit valide n’a été trouvé dans le panier.');
        }

        return [
            'items' => $items,
            'subtotal' => $subtotal,
            'delivery_fee' => 0.0,
            'total' => $subtotal,
        ];
    }

    public function placeOrder(array $customer, array $cart, string $deliveryMode = 'domicile', string $paymentMode = 'especes'): array
    {
        $clientModel = new Client($this->db);
        $commandeModel = new Commande($this->db);
        $ligneCommandeModel = new LigneCommande($this->db);
        $livraisonModel = new Livraison($this->db);
        $paiementModel = new Paiement($this->db);

        $summary = $this->buildCartSummary($cart);

        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        $this->db->beginTransaction();

        try {
            $email = trim((string) ($customer['email'] ?? ''));
            $nom = trim((string) ($customer['nom'] ?? ''));
            $prenom = trim((string) ($customer['prenom'] ?? ''));
            $telephone = trim((string) ($customer['telephone'] ?? ''));
            $adresse = trim((string) ($customer['adresse'] ?? ''));
            $ville = trim((string) ($customer['ville'] ?? ''));
            $commune = trim((string) ($customer['commune'] ?? ''));

            if ($email === '' || $nom === '' || $prenom === '') {
                throw new InvalidArgumentException('Les informations client sont incomplètes.');
            }

            $client = $clientModel->findByEmail($email);
            $clientId = $client['id_client'] ?? null;

            if ($clientId === null) {
                $clientId = $clientModel->create([
                    'nom' => $nom,
                    'prenom' => $prenom,
                    'email' => $email,
                    'telephone' => $telephone,
                    'adresse' => $adresse,
                    'ville' => $ville,
                    'commune' => $commune,
                ]);
            }

            if (!$clientId) {
                throw new RuntimeException('Impossible de récupérer ou de créer le client.');
            }

            $numeroCommande = $commandeModel->generateNumber();
            $reference = $commandeModel->generateReference();
            $commandeId = $commandeModel->create([
                'id_client' => $clientId,
                'numero_commande' => $numeroCommande,
                'reference' => $reference,
                'montant_total' => $summary['total'],
                'statut' => 'en_attente',
            ]);

            if (!$commandeId) {
                throw new RuntimeException('Impossible de créer la commande.');
            }

            foreach ($summary['items'] as $item) {
                $ligneId = $ligneCommandeModel->create([
                    'id_commande' => $commandeId,
                    'id_produit' => (int) $item['id_produit'],
                    'quantite' => (int) $item['quantity'],
                    'prix_unitaire' => (float) $item['prix'],
                    'sous_total' => (float) $item['subtotal'],
                ]);

                if (!$ligneId) {
                    throw new RuntimeException('Impossible d’enregistrer la ligne de commande.');
                }

                $stmt = $this->db->prepare(
                    'UPDATE produits SET stock = stock - :quantity WHERE id_produit = :id_produit AND stock >= :requested'
                );
                $stmt->execute([
                    ':quantity' => (int) $item['quantity'],
                    ':id_produit' => (int) $item['id_produit'],
                    ':requested' => (int) $item['quantity'],
                ]);

                if ($stmt->rowCount() !== 1) {
                    throw new RuntimeException('Le stock du produit est insuffisant pour la commande.');
                }
            }

            $livraisonId = $livraisonModel->create([
                'id_commande' => $commandeId,
                'adresse_livraison' => $adresse,
                'ville' => $ville,
                'commune' => $commune,
                'telephone' => $telephone,
                'mode_livraison' => $deliveryMode,
                'frais_livraison' => 0.0,
                'statut' => 'en_attente',
            ]);

            if (!$livraisonId) {
                throw new RuntimeException('Impossible de créer la livraison.');
            }

            $paiementId = $paiementModel->create([
                'id_commande' => $commandeId,
                'mode_paiement' => $paymentMode,
                'montant' => $summary['total'],
                'statut' => 'en_attente',
            ]);

            if (!$paiementId) {
                throw new RuntimeException('Impossible d’enregistrer le paiement.');
            }

            $this->db->commit();

            return [
                'id_commande' => $commandeId,
                'numero_commande' => $numeroCommande,
                'reference' => $reference,
                'livraison_id' => $livraisonId,
                'paiement_id' => $paiementId,
                'montant_total' => $summary['total'],
            ];
        } catch (Throwable $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }

            throw $e;
        }
    }
}
