<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Produit.php";
require_once __DIR__ . "/models/Panier.php";
require_once __DIR__ . "/models/Client.php";
require_once __DIR__ . "/models/Commande.php";
require_once __DIR__ . "/models/LigneCommande.php";
require_once __DIR__ . "/models/Livraison.php";
require_once __DIR__ . "/models/Paiement.php";


/*
|--------------------------------------------------------------------------
| Initialisation
|--------------------------------------------------------------------------
*/

Panier::init();

$pageTitle = "Passer la commande";
$baseUrl = "";

$database = new Database();
$db = $database->getConnection();

$produitModel = new Produit($db);
$clientModel = new Client($db);
$commandeModel = new Commande($db);
$ligneCommandeModel = new LigneCommande($db);
$livraisonModel = new Livraison($db);
$paiementModel = new Paiement($db);

$errors = [];


/*
|--------------------------------------------------------------------------
| Vérifier le panier
|--------------------------------------------------------------------------
*/

if (Panier::isEmpty()) {

    header("Location: cart.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les produits du panier
|--------------------------------------------------------------------------
*/

$cartItems = [];

foreach (Panier::getItems() as $productId => $item) {

    $produit = $produitModel->getById(
        (int) $productId
    );

    /*
    |--------------------------------------------------------------------------
    | Produit inexistant
    |--------------------------------------------------------------------------
    */

    if (!$produit) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Quantité
    |--------------------------------------------------------------------------
    */

    $quantity = (int) ($item['quantity'] ?? 0);

    if ($quantity <= 0) {
        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifier le stock
    |--------------------------------------------------------------------------
    */

    if ($quantity > (int) $produit['stock']) {

        $errors[] =
            "Le produit « " .
            htmlspecialchars($produit['nom']) .
            " » ne possède pas suffisamment de stock.";

        continue;
    }


    /*
    |--------------------------------------------------------------------------
    | Préparer le produit
    |--------------------------------------------------------------------------
    */

    $produit['quantity'] = $quantity;

    $produit['subtotal'] =
        (float) $produit['prix'] * $quantity;

    $cartItems[] = $produit;
}


/*
|--------------------------------------------------------------------------
| Panier invalide
|--------------------------------------------------------------------------
*/

if (empty($cartItems)) {

    Panier::clear();

    header("Location: cart.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Calcul du sous-total
|--------------------------------------------------------------------------
*/

$subtotal = 0;

foreach ($cartItems as $item) {

    $subtotal += (float) $item['subtotal'];
}


/*
|--------------------------------------------------------------------------
| Frais de livraison
|--------------------------------------------------------------------------
|
| Pour le moment, la livraison est gratuite.
|
*/

$deliveryFee = 0;


/*
|--------------------------------------------------------------------------
| Total
|--------------------------------------------------------------------------
*/

$total = $subtotal + $deliveryFee;


/*
|--------------------------------------------------------------------------
| Variables du formulaire
|--------------------------------------------------------------------------
*/

$nom = '';
$prenom = '';
$email = '';
$telephone = '';
$adresse = '';
$ville = '';
$commune = '';

$modeLivraison = '';
$modePaiement = '';


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    /*
    |--------------------------------------------------------------------------
    | Récupération des données
    |--------------------------------------------------------------------------
    */

    $nom = trim($_POST['nom'] ?? '');

    $prenom = trim($_POST['prenom'] ?? '');

    $email = trim($_POST['email'] ?? '');

    $telephone = trim($_POST['telephone'] ?? '');

    $adresse = trim($_POST['adresse'] ?? '');

    $ville = trim($_POST['ville'] ?? '');

    $commune = trim($_POST['commune'] ?? '');

    $modeLivraison =
        trim($_POST['mode_livraison'] ?? '');

    $modePaiement =
        trim($_POST['mode_paiement'] ?? '');


    /*
    |--------------------------------------------------------------------------
    | Validation du nom
    |--------------------------------------------------------------------------
    */

    if ($nom === '') {

        $errors[] =
            "Le nom est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation du prénom
    |--------------------------------------------------------------------------
    */

    if ($prenom === '') {

        $errors[] =
            "Le prénom est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation de l'email
    |--------------------------------------------------------------------------
    */

    if (
        $email === '' ||
        !filter_var(
            $email,
            FILTER_VALIDATE_EMAIL
        )
    ) {

        $errors[] =
            "L'adresse email est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation du téléphone
    |--------------------------------------------------------------------------
    */

    if ($telephone === '') {

        $errors[] =
            "Le numéro de téléphone est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation de l'adresse
    |--------------------------------------------------------------------------
    */

    if ($adresse === '') {

        $errors[] =
            "L'adresse de livraison est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation de la ville
    |--------------------------------------------------------------------------
    */

    if ($ville === '') {

        $errors[] =
            "La ville est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation de la commune
    |--------------------------------------------------------------------------
    */

    if ($commune === '') {

        $errors[] =
            "La commune est obligatoire.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation du mode de livraison
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $modeLivraison,
            [
                'domicile',
                'relais'
            ],
            true
        )
    ) {

        $errors[] =
            "Le mode de livraison sélectionné est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation du mode de paiement
    |--------------------------------------------------------------------------
    */

    if (
        !in_array(
            $modePaiement,
            [
                'especes',
                'wave',
                'orange_money'
            ],
            true
        )
    ) {

        $errors[] =
            "Le mode de paiement sélectionné est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Création de la commande
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            /*
            |--------------------------------------------------------------------------
            | Démarrer la transaction
            |--------------------------------------------------------------------------
            */

            $db->beginTransaction();


            /*
            |--------------------------------------------------------------------------
            | Vérification du stock
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                $sqlStockCheck = "
                    SELECT stock
                    FROM produits
                    WHERE id_produit = :id_produit
                    FOR UPDATE
                ";

                $stmtStockCheck =
                    $db->prepare(
                        $sqlStockCheck
                    );

                $stmtStockCheck->execute([
                    ':id_produit' =>
                        (int) $item['id_produit']
                ]);

                $stockActuel =
                    $stmtStockCheck->fetchColumn();


                /*
                |--------------------------------------------------------------------------
                | Produit inexistant
                |--------------------------------------------------------------------------
                */

                if ($stockActuel === false) {

                    throw new Exception(
                        "Le produit « " .
                        $item['nom'] .
                        " » n'existe plus."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Stock insuffisant
                |--------------------------------------------------------------------------
                */

                if (
                    (int) $stockActuel <
                    (int) $item['quantity']
                ) {

                    throw new Exception(
                        "Le stock du produit « " .
                        $item['nom'] .
                        " » est insuffisant."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Rechercher le client
            |--------------------------------------------------------------------------
            */

            $client =
                $clientModel->findByEmail(
                    $email
                );


            /*
            |--------------------------------------------------------------------------
            | Client existant
            |--------------------------------------------------------------------------
            */

            if ($client) {

                $clientId =
                    (int) $client['id_client'];

            }


            /*
            |--------------------------------------------------------------------------
            | Nouveau client
            |--------------------------------------------------------------------------
            */

            else {

                $clientId =
                    $clientModel->create([

                        'nom' =>
                            $nom,

                        'prenom' =>
                            $prenom,

                        'email' =>
                            $email,

                        'telephone' =>
                            $telephone,

                        'adresse' =>
                            $adresse,

                        'ville' =>
                            $ville,

                        'commune' =>
                            $commune
                    ]);
            }


            /*
            |--------------------------------------------------------------------------
            | Vérifier l'identifiant client
            |--------------------------------------------------------------------------
            */

            if (!$clientId) {

                throw new Exception(
                    "Impossible de créer ou récupérer le client."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Générer le numéro de commande
            |--------------------------------------------------------------------------
            */

            $numeroCommande =
                $commandeModel->generateNumber();


            /*
            |--------------------------------------------------------------------------
            | Générer la référence
            |--------------------------------------------------------------------------
            */

            $reference =
                $commandeModel->generateReference();


            /*
            |--------------------------------------------------------------------------
            | Créer la commande
            |--------------------------------------------------------------------------
            */

            $commandeId =
                $commandeModel->create([

                    'id_client' =>
                        $clientId,

                    'numero_commande' =>
                        $numeroCommande,

                    'reference' =>
                        $reference,

                    'montant_total' =>
                        $total,

                    'statut' =>
                        'en_attente'
                ]);


            /*
            |--------------------------------------------------------------------------
            | Vérifier la création de la commande
            |--------------------------------------------------------------------------
            */

            if (!$commandeId) {

                throw new Exception(
                    "Impossible de créer la commande."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Ajouter les lignes de commande
            |--------------------------------------------------------------------------
            */

            foreach ($cartItems as $item) {

                /*
                |--------------------------------------------------------------------------
                | Créer la ligne
                |--------------------------------------------------------------------------
                */

                $ligneId =
                    $ligneCommandeModel->create([

                        'id_commande' =>
                            $commandeId,

                        'id_produit' =>
                            (int) $item['id_produit'],

                        'quantite' =>
                            (int) $item['quantity'],

                        'prix_unitaire' =>
                            (float) $item['prix'],

                        'sous_total' =>
                            (float) $item['subtotal']
                    ]);


                /*
                |--------------------------------------------------------------------------
                | Vérifier la création de la ligne
                |--------------------------------------------------------------------------
                */

                if (!$ligneId) {

                    throw new Exception(
                        "Impossible d'ajouter le produit « " .
                        $item['nom'] .
                        " » à la commande."
                    );
                }


                /*
                |--------------------------------------------------------------------------
                | Décrémenter le stock
                |--------------------------------------------------------------------------
                */

                $sqlStock = "
                    UPDATE produits
                    SET stock = stock - :quantity
                    WHERE id_produit = :id_produit
                    AND stock >= :stock_quantity
                ";

                $stmtStock =
                    $db->prepare(
                        $sqlStock
                    );


                $quantity =
                    (int) $item['quantity'];

                $productId =
                    (int) $item['id_produit'];


                $stmtStock->execute([

                    ':quantity' =>
                        $quantity,

                    ':id_produit' =>
                        $productId,

                    ':stock_quantity' =>
                        $quantity
                ]);


                /*
                |--------------------------------------------------------------------------
                | Vérifier la mise à jour du stock
                |--------------------------------------------------------------------------
                */

                if ($stmtStock->rowCount() !== 1) {

                    throw new Exception(
                        "Impossible de mettre à jour le stock du produit « " .
                        $item['nom'] .
                        " »."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Créer la livraison
            |--------------------------------------------------------------------------
            |
            | IMPORTANT :
            | Les noms correspondent exactement à la table
            | livraisons :
            |
            | adresse_livraison
            | frais_livraison
            | mode_livraison
            |
            */

            $livraisonId =
                $livraisonModel->create([

                    'id_commande' =>
                        $commandeId,

                    'adresse_livraison' =>
                        $adresse,

                    'ville' =>
                        $ville,

                    'commune' =>
                        $commune,

                    'telephone' =>
                        $telephone,

                    'mode_livraison' =>
                        $modeLivraison,

                    'frais_livraison' =>
                        $deliveryFee,

                    'statut' =>
                        'en_attente'
                ]);


            /*
            |--------------------------------------------------------------------------
            | Vérifier la livraison
            |--------------------------------------------------------------------------
            */

            if (!$livraisonId) {

                throw new Exception(
                    "Impossible de créer la livraison."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Créer le paiement
            |--------------------------------------------------------------------------
            */

            $paiementId =
                $paiementModel->create([

                    'id_commande' =>
                        $commandeId,

                    'mode_paiement' =>
                        $modePaiement,

                    'montant' =>
                        $total,

                    'statut' =>
                        'en_attente'
                ]);


            /*
            |--------------------------------------------------------------------------
            | Vérifier le paiement
            |--------------------------------------------------------------------------
            */

            if (!$paiementId) {

                throw new Exception(
                    "Impossible de créer le paiement."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Valider la transaction
            |--------------------------------------------------------------------------
            */

            $db->commit();


            /*
            |--------------------------------------------------------------------------
            | Vider le panier
            |--------------------------------------------------------------------------
            */

            Panier::clear();


            /*
            |--------------------------------------------------------------------------
            | Sauvegarder les informations de commande
            |--------------------------------------------------------------------------
            */

            $_SESSION['order_number'] =
                $numeroCommande;

            $_SESSION['order_reference'] =
                $reference;

            $_SESSION['order_id'] =
                $commandeId;


            /*
            |--------------------------------------------------------------------------
            | Redirection
            |--------------------------------------------------------------------------
            */

            header(
                "Location: order-success.php"
            );

            exit;
        }


        /*
        |--------------------------------------------------------------------------
        | Gestion des erreurs
        |--------------------------------------------------------------------------
        */

        catch (Throwable $e) {

            /*
            |--------------------------------------------------------------------------
            | Annuler la transaction
            |--------------------------------------------------------------------------
            */

            if ($db->inTransaction()) {

                $db->rollBack();
            }


            /*
            |--------------------------------------------------------------------------
            | Message d'erreur
            |--------------------------------------------------------------------------
            */

            $errors[] =
                "Impossible de créer la commande : " .
                $e->getMessage();
        }
    }
}


/*
|--------------------------------------------------------------------------
| Affichage
|--------------------------------------------------------------------------
*/

require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <div class="row g-4">


        <!-- FORMULAIRE -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h2 class="fw-bold mb-4">

                        Finaliser ma commande

                    </h2>


                    <?php if (!empty($errors)): ?>

                    <div class="alert alert-danger">

                        <ul class="mb-0">

                            <?php foreach ($errors as $error): ?>

                            <li>

                                <?= htmlspecialchars($error) ?>

                            </li>

                            <?php endforeach; ?>

                        </ul>

                    </div>

                    <?php endif; ?>


                    <form method="POST" novalidate>


                        <!-- CLIENT -->

                        <h5 class="fw-bold mb-3">

                            Informations personnelles

                        </h5>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label" for="prenom">
                                    Prénom *
                                </label>

                                <input type="text" id="prenom" name="prenom" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['prenom'] ?? ''
                                    ) ?>">

                            </div>


                            <div class="col-md-6">

                                <label class="form-label" for="nom">
                                    Nom *
                                </label>

                                <input type="text" id="nom" name="nom" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['nom'] ?? ''
                                    ) ?>">

                            </div>


                            <div class="col-md-6">

                                <label class="form-label" for="email">
                                    Email *
                                </label>

                                <input type="email" id="email" name="email" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['email'] ?? ''
                                    ) ?>">

                            </div>


                            <div class="col-md-6">

                                <label class="form-label" for="telephone">
                                    Téléphone *
                                </label>

                                <input type="tel" id="telephone" name="telephone" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['telephone'] ?? ''
                                    ) ?>">

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- LIVRAISON -->

                        <h5 class="fw-bold mb-3">

                            Adresse de livraison

                        </h5>


                        <div class="row g-3">

                            <div class="col-md-6">

                                <label class="form-label" for="ville">
                                    Ville *
                                </label>

                                <input type="text" id="ville" name="ville" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['ville'] ?? ''
                                    ) ?>">

                            </div>


                            <div class="col-md-6">

                                <label class="form-label" for="commune">
                                    Commune *
                                </label>

                                <input type="text" id="commune" name="commune" class="form-control" required value="<?= htmlspecialchars(
                                        $_POST['commune'] ?? ''
                                    ) ?>">

                            </div>


                            <div class="col-12">

                                <label class="form-label" for="adresse">
                                    Adresse complète *
                                </label>

                                <textarea id="adresse" name="adresse" class="form-control" rows="3" required><?= htmlspecialchars(
                                    $_POST['adresse'] ?? ''
                                ) ?></textarea>

                            </div>

                        </div>


                        <hr class="my-4">


                        <!-- MODE LIVRAISON -->

                        <h5 class="fw-bold mb-3">

                            Mode de livraison

                        </h5>


                        <div class="form-check mb-2">

                            <input type="radio" name="mode_livraison" id="domicile" value="domicile"
                                class="form-check-input" checked>

                            <label class="form-check-label" for="domicile">

                                <strong>
                                    Livraison à domicile
                                </strong>

                                <br>

                                <small class="text-muted">
                                    Recevez votre commande à l'adresse indiquée.
                                </small>

                            </label>

                        </div>


                        <div class="form-check">

                            <input type="radio" name="mode_livraison" id="relais" value="relais"
                                class="form-check-input">

                            <label class="form-check-label" for="relais">

                                <strong>
                                    Retrait en point relais
                                </strong>

                            </label>

                        </div>


                        <hr class="my-4">


                        <!-- PAIEMENT -->

                        <h5 class="fw-bold mb-3">

                            Mode de paiement

                        </h5>


                        <div class="form-check mb-2">

                            <input type="radio" name="mode_paiement" id="especes" value="especes"
                                class="form-check-input" <?= ($modePaiement === '' || $modePaiement === 'especes') ? 'checked' : '' ?>>

                            <label class="form-check-label" for="especes">

                                Espèces à la livraison

                            </label>

                        </div>


                        <div class="form-check mb-2">

                            <input type="radio" name="mode_paiement" id="wave" value="wave"
                                class="form-check-input" <?= $modePaiement === 'wave' ? 'checked' : '' ?>>

                            <label class="form-check-label" for="wave">

                                Wave

                            </label>

                        </div>


                        <div class="form-check">

                            <input type="radio" name="mode_paiement" id="orange_money" value="orange_money"
                                class="form-check-input" <?= $modePaiement === 'orange_money' ? 'checked' : '' ?>>

                            <label class="form-check-label" for="orange_money">

                                Orange Money

                            </label>

                        </div>


                        <button type="submit" class="btn btn-dark btn-lg w-100 mt-4">

                            <i class="bi bi-check-circle"></i>

                            Confirmer la commande

                        </button>


                    </form>

                </div>

            </div>

        </div>


        <!-- RESUME -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body p-4">

                    <h4 class="fw-bold mb-4">

                        Résumé de la commande

                    </h4>


                    <?php foreach ($cartItems as $item): ?>

                    <div class="d-flex justify-content-between mb-3">

                        <div>

                            <strong>

                                <?= htmlspecialchars(
                                        $item['nom']
                                    ) ?>

                            </strong>

                            <br>

                            <small class="text-muted">

                                x<?= $item['quantity'] ?>

                            </small>

                        </div>


                        <span>

                            <?= number_format(
                                    $item['subtotal'],
                                    0,
                                    ',',
                                    ' '
                                ) ?>

                            FCFA

                        </span>

                    </div>

                    <?php endforeach; ?>


                    <hr>


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Sous-total
                        </span>

                        <strong>

                            <?= number_format(
                                $subtotal,
                                0,
                                ',',
                                ' '
                            ) ?>

                            FCFA

                        </strong>

                    </div>


                    <div class="d-flex justify-content-between mb-2">

                        <span>
                            Livraison
                        </span>

                        <strong>

                            <?= number_format(
                                $deliveryFee,
                                0,
                                ',',
                                ' '
                            ) ?>

                            FCFA

                        </strong>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between">

                        <strong>
                            Total
                        </strong>

                        <strong class="fs-4">

                            <?= number_format(
                                $subtotal + $deliveryFee,
                                0,
                                ',',
                                ' '
                            ) ?>

                            FCFA

                        </strong>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . "/includes/footer.php";

?>