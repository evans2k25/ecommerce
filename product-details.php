<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Produit.php";
require_once __DIR__ . "/models/Panier.php";

Panier::init();

$pageTitle = "Détails du produit";
$baseUrl = "";

$database = new Database();
$db = $database->getConnection();

$produitModel = new Produit($db);


/*
|--------------------------------------------------------------------------
| Vérification de l'ID
|--------------------------------------------------------------------------
*/

$id = filter_input(
    INPUT_GET,
    'id',
    FILTER_VALIDATE_INT
);

if (!$id) {

    header("Location: products.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupération du produit
|--------------------------------------------------------------------------
*/

$produit = $produitModel->getById($id);

if (!$produit) {

    http_response_code(404);

    $pageTitle = "Produit introuvable";

    require_once __DIR__ . "/includes/header.php";

    ?>

<div class="container py-5">

    <div class="alert alert-danger">

        Produit introuvable.

    </div>

    <a href="products.php" class="btn btn-dark">
        Retour aux produits
    </a>

</div>

<?php

    require_once __DIR__ . "/includes/footer.php";

    exit;
}


/*
|--------------------------------------------------------------------------
| Ajouter au panier
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $quantity = filter_input(
        INPUT_POST,
        'quantity',
        FILTER_VALIDATE_INT
    );

    if (!$quantity || $quantity < 1) {
        $quantity = 1;
    }


    /*
    | Vérification du stock
    */

    if ($quantity > $produit['stock']) {

        $error = "La quantité demandée dépasse le stock disponible.";

    } else {

        Panier::add(
            (int) $produit['id_produit'],
            $quantity,
            (float) $produit['prix']
        );

        header("Location: cart.php?added=1");
        exit;
    }
}

require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <?php if (isset($error)): ?>

    <div class="alert alert-danger">

        <?= htmlspecialchars($error) ?>

    </div>

    <?php endif; ?>


    <div class="row g-5">

        <!-- IMAGE -->

        <div class="col-md-6">

            <?php if (!empty($produit['image'])): ?>

            <img src="uploads/products/<?= htmlspecialchars($produit['image']) ?>"
                class="img-fluid rounded shadow-sm w-100" style="max-height:500px;object-fit:cover;"
                alt="<?= htmlspecialchars($produit['nom']) ?>">

            <?php else: ?>

            <div class="bg-light rounded d-flex align-items-center justify-content-center" style="height:500px;">

                <i class="bi bi-image text-secondary" style="font-size:100px;"></i>

            </div>

            <?php endif; ?>

        </div>


        <!-- INFORMATIONS -->

        <div class="col-md-6">

            <span class="badge bg-secondary mb-3">

                <?= htmlspecialchars($produit['categorie']) ?>

            </span>


            <h1 class="fw-bold">

                <?= htmlspecialchars($produit['nom']) ?>

            </h1>


            <h2 class="fw-bold mt-3">

                <?= number_format(
                    $produit['prix'],
                    0,
                    ',',
                    ' '
                ) ?>

                FCFA

            </h2>


            <hr>


            <p class="text-muted">

                <?= nl2br(
                    htmlspecialchars(
                        $produit['description'] ?? ''
                    )
                ) ?>

            </p>


            <!-- STOCK -->

            <?php if ($produit['stock'] > 0): ?>

            <p class="text-success">

                <i class="bi bi-check-circle"></i>

                <?= $produit['stock'] ?> produit(s)
                disponible(s)

            </p>

            <?php else: ?>

            <p class="text-danger">

                <i class="bi bi-x-circle"></i>

                Produit indisponible

            </p>

            <?php endif; ?>


            <?php if ($produit['stock'] > 0): ?>

            <form method="POST" class="mt-4">

                <label for="quantity" class="form-label fw-semibold">

                    Quantité

                </label>


                <div class="input-group mb-3">

                    <button type="button" class="btn btn-outline-secondary" onclick="decreaseQuantity()">
                        −
                    </button>


                    <input type="number" name="quantity" id="quantity" class="form-control text-center" value="1"
                        min="1" max="<?= $produit['stock'] ?>">


                    <button type="button" class="btn btn-outline-secondary"
                        onclick="increaseQuantity(<?= $produit['stock'] ?>)">
                        +
                    </button>

                </div>


                <button type="submit" class="btn btn-dark btn-lg w-100">

                    <i class="bi bi-cart-plus"></i>

                    Ajouter au panier

                </button>

            </form>

            <?php endif; ?>


            <a href="products.php" class="btn btn-outline-secondary w-100 mt-3">

                <i class="bi bi-arrow-left"></i>

                Continuer mes achats

            </a>

        </div>

    </div>

</div>


<script>
function increaseQuantity(max) {
    const input = document.getElementById("quantity");

    let value = parseInt(input.value) || 1;

    if (value < max) {
        input.value = value + 1;
    }
}


function decreaseQuantity() {
    const input = document.getElementById("quantity");

    let value = parseInt(input.value) || 1;

    if (value > 1) {
        input.value = value - 1;
    }
}
</script>


<?php

require_once __DIR__ . "/includes/footer.php";

?>