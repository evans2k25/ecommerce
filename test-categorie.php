<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Categorie.php";

$database = new Database();
$db = $database->getConnection();

$categorieModel = new Categorie($db);

$categories = $categorieModel->getAll();

echo "<pre>";
print_r($categories);
echo "</pre>";