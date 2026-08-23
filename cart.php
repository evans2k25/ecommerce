<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Produit.php";
require_once __DIR__ . "/models/Panier.php";

Panier::init();

$pageTitle = "Mon panier";
$baseUrl = "";

$database = new Database();
$db = $database->getConnection();

$produitModel = new Produit($db);


/*
|--------------------------------------------------------------------------
| Actions du panier
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $action = $_POST['action'] ?? '';

    $productId = filter_input(
        INPUT_POST,
        'product_id',
        FILTER_VALIDATE_INT
    );


    /*
    | Modifier quantité
    */

    if ($action === 'update' && $productId) {

        $quantity = filter_input(
            INPUT_POST,
            'quantity',
            FILTER_VALIDATE_INT
        );

        if ($quantity && $quantity > 0) {

            $produit = $produitModel->getById($productId);

            if ($produit) {

                if ($quantity <= $produit['stock']) {

                    Panier::update(
                        $productId,
                        $quantity
                    );
                }
            }
        }

        header("Location: cart.php");
        exit;
    }


    /*
    | Supprimer
    */

    if ($action === 'remove' && $productId) {

        Panier::remove($productId);

        header("Location: cart.php");
        exit;
    }


    /*
    | Vider
    */

    if ($action === 'clear') {

        Panier::clear();

        header("Location: cart.php");
        exit;
    }
}


/*
|--------------------------------------------------------------------------
| Récupération des produits
|--------------------------------------------------------------------------
*/

$cartItems = [];

foreach (Panier::getItems() as $productId => $item) {

    $produit = $produitModel->getById(
        (int) $productId
    );

    if ($produit) {

        $produit['quantity'] = $item['quantity'];

        $produit['subtotal'] =
            $produit['prix'] * $item['quantity'];

        $cartItems[] = $produit;
    }
}


$subtotal = 0;

foreach ($cartItems as $item) {

    $subtotal += $item['subtotal'];
}


require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <div class="d-flex justify-content-between align-items-center mb-4">

        <div>

            <h1 class="fw-bold">
                Mon panier
            </h1>

            <p class="text-muted">
                Vérifiez vos articles avant de commander.
            </p>

        </div>

    </div>


    <?php if (isset($_GET['added'])): ?>

    <div class="alert alert-success">

        <i class="bi bi-check-circle"></i>

        Produit ajouté au panier.

    </div>

    <?php endif; ?>


    <?php if (empty($cartItems)): ?>

    <div class="text-center py-5">

        <i class="bi bi-cart-x text-secondary" style="font-size:80px;"></i>

        <h3 class="mt-4">
            Votre panier est vide
        </h3>

        <p class="text-muted">
            Vous n'avez encore ajouté aucun produit.
        </p>

        <a href="products.php" class="btn btn-dark">

            Découvrir les produits

        </a>

    </div>

    <?php else: ?>


    <div class="row g-4">

        <!-- PRODUITS -->

        <div class="col-lg-8">

            <div class="card border-0 shadow-sm">

                <div class="card-body">


                    <?php foreach ($cartItems as $item): ?>

                    <div class="row align-items-center g-3 py-3 border-bottom">

                        <!-- IMAGE -->

                        <div class="col-3 col-md-2">

                            <?php if (!empty($item['image'])): ?>

                            <img src="uploads/products/<?= htmlspecialchars($item['image']) ?>"
                                class="img-fluid rounded" style="height:80px;width:80px;object-fit:cover;"
                                alt="<?= htmlspecialchars($item['nom']) ?>">

                            <?php else: ?>

                            <div class="bg-light rounded d-flex align-items-center justify-content-center"
                                style="height:80px;width:80px;">

                                <i class="bi bi-image"></i>

                            </div>

                            <?php endif; ?>

                        </div>


                        <!-- NOM -->

                        <div class="col-9 col-md-4">

                            <h6 class="mb-1">

                                <?= htmlspecialchars(
                                            $item['nom']
                                        ) ?>

                            </h6>

                            <small class="text-muted">

                                <?= number_format(
                                            $item['prix'],
                                            0,
                                            ',',
                                            ' '
                                        ) ?>

                                FCFA

                            </small>

                        </div>


                        <!-- QUANTITE -->

                        <div class="col-6 col-md-3">

                            <form method="POST" class="d-flex">

                                <input type="hidden" name="action" value="update">

                                <input type="hidden" name="product_id" value="<?= $item['id_produit'] ?>">

                                <input type="number" name="quantity" value="<?= $item['quantity'] ?>" min="1"
                                    max="<?= $item['stock'] ?>" class="form-control form-control-sm">

                                <button type="submit" class="btn btn-outline-dark btn-sm ms-2">

                                    <i class="bi bi-arrow-repeat"></i>

                                </button>

                            </form>

                        </div>


                        <!-- TOTAL -->

                        <div class="col-4 col-md-2 text-end">

                            <strong>

                                <?= number_format(
                                            $item['subtotal'],
                                            0,
                                            ',',
                                            ' '
                                        ) ?>

                                FCFA

                            </strong>

                        </div>


                        <!-- SUPPRIMER -->

                        <div class="col-2 col-md-1 text-end">

                            <form method="POST">

                                <input type="hidden" name="action" value="remove">

                                <input type="hidden" name="product_id" value="<?= $item['id_produit'] ?>">

                                <button type="submit" class="btn btn-sm btn-outline-danger" title="Supprimer">

                                    <i class="bi bi-trash"></i>

                                </button>

                            </form>

                        </div>

                    </div>

                    <?php endforeach; ?>


                    <!-- VIDER -->

                    <div class="mt-4">

                        <form method="POST">

                            <input type="hidden" name="action" value="clear">

                            <button type="submit" class="btn btn-outline-danger">

                                <i class="bi bi-trash"></i>

                                Vider le panier

                            </button>

                        </form>

                    </div>

                </div>

            </div>

        </div>


        <!-- RESUME -->

        <div class="col-lg-4">

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h4 class="fw-bold mb-4">
                        Résumé
                    </h4>


                    <div class="d-flex justify-content-between mb-3">

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


                    <div class="d-flex justify-content-between mb-3">

                        <span>
                            Livraison
                        </span>

                        <span>
                            Calculée à l'étape suivante
                        </span>

                    </div>


                    <hr>


                    <div class="d-flex justify-content-between mb-4">

                        <strong>
                            Total
                        </strong>

                        <strong class="fs-5">

                            <?= number_format(
                                    $subtotal,
                                    0,
                                    ',',
                                    ' '
                                ) ?>

                            FCFA

                        </strong>

                    </div>


                    <a href="checkout.php" class="btn btn-dark btn-lg w-100">

                        Passer la commande

                        <i class="bi bi-arrow-right"></i>

                    </a>


                    <a href="products.php" class="btn btn-outline-secondary w-100 mt-2">

                        Continuer mes achats

                    </a>

                </div>

            </div>

        </div>

    </div>


    <?php endif; ?>

</div>


<?php

require_once __DIR__ . "/includes/footer.php";

?>