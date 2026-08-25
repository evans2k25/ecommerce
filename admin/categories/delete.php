<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Categorie.php";

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();
$categorieModel = new Categorie($db);
$categorie = $categorieModel->getById($id);

if (!$categorie) {
    header("Location: index.php");
    exit;
}

if ((int) $categorie['total_products'] > 0) {
    header("Location: index.php?error=has_products");
    exit;
}

try {
    $categorieModel->delete($id);
    header("Location: index.php?success=deleted");
    exit;
} catch (Throwable $e) {
    header("Location: index.php?error=" . urlencode($e->getMessage()));
    exit;
}
