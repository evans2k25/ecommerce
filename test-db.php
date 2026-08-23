<?php

require_once __DIR__ . "/config/database.php";

try {

    $database = new Database();

    $db = $database->getConnection();

    echo "<h1>Connexion réussie !</h1>";

    echo "<p>La connexion à la base de données "
       . "<strong>ecommerce</strong> fonctionne correctement.</p>";

} catch (PDOException $e) {

    echo "<h1>Erreur de connexion</h1>";

    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}