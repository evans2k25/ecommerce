<?php

session_start();

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Produit.php";


/*
|--------------------------------------------------------------------------
| Vérification administrateur
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin'])) {

    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Vérification ID
|--------------------------------------------------------------------------
*/

if (
    !isset($_GET['id']) ||
    !ctype_digit($_GET['id'])
) {

    header("Location: index.php?error=invalid_id");
    exit;
}


$idProduit = (int) $_GET['id'];


/*
|--------------------------------------------------------------------------
| Connexion BDD
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();
    $db = $database->getConnection();

    $produitModel = new Produit($db);


    /*
    |--------------------------------------------------------------------------
    | Vérifier que le produit existe
    |--------------------------------------------------------------------------
    */

    $produit = $produitModel->getById($idProduit);

    if (!$produit) {

        header("Location: index.php?error=not_found");
        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifier si le produit est utilisé dans une commande
    |--------------------------------------------------------------------------
    */

    $sql = "
        SELECT COUNT(*)
        FROM lignes_commande
        WHERE id_produit = :id_produit
    ";

    $stmt = $db->prepare($sql);

    $stmt->execute([
        'id_produit' => $idProduit
    ]);

    $nombreCommandes = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | Produit déjà commandé
    |--------------------------------------------------------------------------
    */

    if ($nombreCommandes > 0) {

        /*
        | On archive au lieu de supprimer
        */

        $sqlArchive = "
            UPDATE produits
            SET statut = 'archive'
            WHERE id_produit = :id_produit
        ";

        $stmtArchive = $db->prepare($sqlArchive);

        $stmtArchive->execute([
            'id_produit' => $idProduit
        ]);


        header(
            "Location: index.php?success=archived"
        );

        exit;
    }


    /*
    |--------------------------------------------------------------------------
    | Produit jamais commandé
    |--------------------------------------------------------------------------
    */

    $db->beginTransaction();


    /*
    | Supprimer le produit
    */

    $produitModel->delete($idProduit);


    /*
    |--------------------------------------------------------------------------
    | Supprimer l'image
    |--------------------------------------------------------------------------
    */

    if (!empty($produit['image'])) {

        $imagePath =
            __DIR__
            . "/../../uploads/products/"
            . $produit['image'];

        if (file_exists($imagePath)) {

            unlink($imagePath);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Valider
    |--------------------------------------------------------------------------
    */

    $db->commit();


    header(
        "Location: index.php?success=deleted"
    );

    exit;

}


/*
|--------------------------------------------------------------------------
| Gestion des erreurs
|--------------------------------------------------------------------------
*/

catch (Throwable $e) {

    if ($db->inTransaction()) {

        $db->rollBack();
    }

    header(
        "Location: index.php?error="
        . urlencode($e->getMessage())
    );

    exit;
}