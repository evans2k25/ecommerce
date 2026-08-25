<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Produit.php";
require_once __DIR__ . "/../models/Client.php";
require_once __DIR__ . "/../models/Commande.php";
require_once __DIR__ . "/../models/LigneCommande.php";
require_once __DIR__ . "/../models/Livraison.php";
require_once __DIR__ . "/../models/Paiement.php";

$database = new Database();
$db = $database->getConnection();

$produitModel = new Produit($db);
$clientModel = new Client($db);
$commandeModel = new Commande($db);
$ligneModel = new LigneCommande($db);
$livraisonModel = new Livraison($db);
$paiementModel = new Paiement($db);

$desiredQty = 2;

// Find a product with sufficient stock
$stmt = $db->prepare("SELECT id_produit, prix, stock FROM produits WHERE stock >= :qty AND statut = 'disponible' LIMIT 1");
$stmt->execute(['qty' => $desiredQty]);
$product = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$product) {
    // Create a category
    $db->beginTransaction();
    $db->exec("INSERT INTO categories (nom, statut) VALUES ('AutoTest', 'active')");
    $catId = (int) $db->lastInsertId();

    // Insert product
    $stmtInsert = $db->prepare("INSERT INTO produits (id_categorie, nom, description, prix, stock, statut, date_creation) VALUES (:cat, :nom, :desc, :prix, :stock, 'disponible', NOW())");
    $stmtInsert->execute([
        'cat' => $catId,
        'nom' => 'Test Product',
        'desc' => 'Auto-generated for smoke test',
        'prix' => 1500,
        'stock' => 10
    ]);
    $productId = (int) $db->lastInsertId();
    $db->commit();

    $product = [
        'id_produit' => $productId,
        'prix' => 1500,
        'stock' => 10
    ];
}

$productId = (int) $product['id_produit'];
$prix = (float) $product['prix'];
$initialStock = (int) $product['stock'];

$clientEmail = 'smoke_client+' . time() . '@example.com';

try {
    $db->beginTransaction();

    // verify stock with FOR UPDATE
    $stmtStock = $db->prepare("SELECT stock FROM produits WHERE id_produit = :id_produit FOR UPDATE");
    $stmtStock->execute(['id_produit' => $productId]);
    $stockActuel = (int) $stmtStock->fetchColumn();

    if ($stockActuel < $desiredQty) {
        throw new Exception('Not enough stock');
    }

    // create client
    $clientId = $clientModel->create([
        'nom' => 'Smoke',
        'prenom' => 'Tester',
        'email' => $clientEmail,
        'telephone' => '0000000000',
        'adresse' => 'Street 1',
        'ville' => 'City',
        'commune' => 'Commune'
    ]);

    if (!$clientId) {
        throw new Exception('Failed to create client');
    }

    $numero = $commandeModel->generateNumber();
    $reference = $commandeModel->generateReference();

    $total = $prix * $desiredQty;

    $commandeId = $commandeModel->create([
        'id_client' => $clientId,
        'numero_commande' => $numero,
        'reference' => $reference,
        'montant_total' => $total,
        'statut' => 'en_attente'
    ]);

    if (!$commandeId) {
        throw new Exception('Failed to create commande');
    }

    $ligneId = $ligneModel->create([
        'id_commande' => $commandeId,
        'id_produit' => $productId,
        'quantite' => $desiredQty,
        'prix_unitaire' => $prix,
        'sous_total' => $total
    ]);

    if (!$ligneId) {
        throw new Exception('Failed to create ligne');
    }

    $stmtUpd = $db->prepare("UPDATE produits SET stock = stock - :quantity WHERE id_produit = :id_produit AND stock >= :stock_quantity");
    $stmtUpd->execute([
        'quantity' => $desiredQty,
        'id_produit' => $productId,
        'stock_quantity' => $desiredQty
    ]);

    if ($stmtUpd->rowCount() !== 1) {
        throw new Exception('Failed to update stock');
    }

    $livraisonId = $livraisonModel->create([
        'id_commande' => $commandeId,
        'adresse_livraison' => 'Street 1',
        'ville' => 'City',
        'commune' => 'Commune',
        'telephone' => '0000000000',
        'frais_livraison' => 0,
        'statut' => 'en_attente'
    ]);

    if (!$livraisonId) {
        throw new Exception('Failed to create livraison');
    }

    $paiementId = $paiementModel->create([
        'id_commande' => $commandeId,
        'mode_paiement' => 'especes',
        'montant' => $total,
        'statut' => 'en_attente'
    ]);

    if (!$paiementId) {
        throw new Exception('Failed to create paiement');
    }

    $db->commit();

    // verify stock decremented
    $stmtCheck = $db->prepare("SELECT stock FROM produits WHERE id_produit = :id_produit");
    $stmtCheck->execute(['id_produit' => $productId]);
    $newStock = (int) $stmtCheck->fetchColumn();

    if ($newStock !== $initialStock - $desiredQty) {
        echo "SMOKE CHECKOUT FAIL: stock not decremented as expected (initial $initialStock, now $newStock)\n";
        exit(1);
    }

    echo "SMOKE CHECKOUT PASS: commande $commandeId, paiement $paiementId, stock now $newStock\n";
    exit(0);

} catch (Throwable $e) {
    if ($db->inTransaction()) {
        $db->rollBack();
    }

    echo "SMOKE CHECKOUT ERROR: " . $e->getMessage() . "\n";
    exit(1);
}
