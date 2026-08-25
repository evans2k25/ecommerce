<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Produit.php";

$database = new Database();
$db = $database->getConnection();

// Insert product directly
$db->beginTransaction();
$db->exec("INSERT INTO categories (nom, statut) VALUES ('TempCat', 'active')");
$catId = (int) $db->lastInsertId();
$stmt = $db->prepare("INSERT INTO produits (id_categorie, nom, description, prix, stock, statut, date_creation) VALUES (:cat, :nom, :desc, :prix, :stock, 'disponible', NOW())");
$stmt->execute(['cat' => $catId, 'nom' => 'TempProduct', 'desc' => 'for delete', 'prix' => 1000, 'stock' => 5]);
$productId = (int) $db->lastInsertId();
$db->commit();

$produitModel = new Produit($db);

$deleted = $produitModel->delete($productId);

if ($deleted) {
    echo "DELETE PRODUCT OK id=$productId\n";
    exit(0);
}

echo "DELETE PRODUCT FAIL\n";
exit(1);
