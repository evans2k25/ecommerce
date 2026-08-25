<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Categorie.php";

$database = new Database();
$db = $database->getConnection();

$categorieModel = new Categorie($db);

// Create
$id = $categorieModel->create([
    'nom' => 'SmokeCat' . time(),
    'description' => 'Created by smoke test',
    'statut' => 'active'
]);

if (!$id) {
    echo "CREATE FAIL\n";
    exit(1);
}

echo "CREATE OK id=$id\n";

// Update
$newName = 'SmokeCatUpdated' . time();
$ok = $categorieModel->update($id, [
    'nom' => $newName,
    'description' => 'Updated by smoke test',
    'image' => null,
    'statut' => 'active'
]);

if (!$ok) {
    echo "UPDATE FAIL\n";
    exit(1);
}

echo "UPDATE OK\n";

// Delete
$ok = $categorieModel->delete($id);

if (!$ok) {
    echo "DELETE FAIL\n";
    exit(1);
}

echo "DELETE OK\n";
exit(0);
