<?php

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Administrateur.php";

$database = new Database();
$db = $database->getConnection();

$adminModel = new Administrateur($db);

$email = $argv[1] ?? 'dev_admin@example.com';
$password = $argv[2] ?? 'secret123';

$admin = $adminModel->login($email, $password);

if ($admin) {
    echo "LOGIN OK: admin id " . ($admin['id_admin'] ?? 'unknown') . "\n";
    exit(0);
}

echo "LOGIN FAIL\n";
exit(1);
