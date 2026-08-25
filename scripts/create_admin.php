<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Administrateur.php";

if (PHP_SAPI !== 'cli') {
    echo "Run this script from CLI: php create_admin.php email password nom prenom\n";
    exit(1);
}

if ($argc < 5) {
    echo "Usage: php create_admin.php email password nom prenom\n";
    exit(1);
}

$email = $argv[1];
$password = $argv[2];
$nom = $argv[3];
$prenom = $argv[4];

$database = new Database();
$db = $database->getConnection();

$adminModel = new Administrateur($db);

if ($adminModel->findByEmail($email)) {
    echo "An administrator with this email already exists.\n";
    exit(1);
}

try {
    $id = $adminModel->create([
        'nom' => $nom,
        'prenom' => $prenom,
        'email' => $email,
        'mot_de_passe' => $password,
        'role' => 'super_admin',
        'statut' => 'actif'
    ]);

    if ($id) {
        echo "Created administrator with id: $id\n";
        exit(0);
    }

    echo "Failed to create administrator.\n";
    exit(1);
} catch (Throwable $e) {
    echo "Error: " . $e->getMessage() . "\n";
    exit(1);
}
