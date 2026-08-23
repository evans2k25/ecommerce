<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Produit.php";

$pageTitle = "Accueil";

$baseUrl = "";

$database = new Database();

$db = $database->getConnection();

$produitModel = new Produit($db);

$produits = $produitModel->getAll();

require_once __DIR__ . "/includes/header.php";

?>

<!-- HERO -->

<section class="hero">

    <div class="container">

        <div class="row">

            <div class="col-lg-7">

                <span class="badge bg-light text-dark mb-3">
                    Bienvenue dans notre boutique
                </span>

                <h1 class="display-4 fw-bold">

                    Achetez simplement.
                    <br>
                    Recevez rapidement.

                </h1>

                <p class="lead mt-3">

                    Découvrez notre sélection de produits
                    et commandez facilement en ligne.

                </p>

                <a href="products.php" class="btn btn-light btn-lg mt-3">

                    Découvrir les produits

                    <i class="bi bi-arrow-right"></i>

                </a>

            </div>

        </div>

    </div>

</section>


<!-- PRODUITS -->

<section class="py-5">

    <div class="container">

        <div class="d-flex justify-content-between align-items-center mb-4">

            <div>

                <h2 class="fw-bold">
                    Nos produits
                </h2>

                <p class="text-muted">
                    Découvrez nos produits disponibles.
                </p>

            </div>

            <a href="products.php" class="btn btn-outline-dark">

                Voir tout

            </a>

        </div>


        <div class="row g-4">

            <?php foreach (array_slice($produits, 0, 4) as $produit): ?>

            <div class="col-sm-6 col-lg-3">

                <div class="card product-card h-100 shadow-sm">

                    <?php if (!empty($produit['image'])): ?>

                    <img src="uploads/products/<?= htmlspecialchars($produit['image']) ?>" class="card-img-top"
                        alt="<?= htmlspecialchars($produit['nom']) ?>">

                    <?php else: ?>

                    <div class="bg-light d-flex align-items-center justify-content-center" style="height:220px;">

                        <i class="bi bi-image text-secondary" style="font-size:50px;"></i>

                    </div>

                    <?php endif; ?>


                    <div class="card-body d-flex flex-column">

                        <small class="text-muted">

                            <?= htmlspecialchars(
                                    $produit['categorie']
                                ) ?>

                        </small>

                        <h5 class="mt-2">

                            <?= htmlspecialchars(
                                    $produit['nom']
                                ) ?>

                        </h5>


                        <h5 class="fw-bold mt-auto">

                            <?= number_format(
                                    $produit['prix'],
                                    0,
                                    ',',
                                    ' '
                                ) ?>

                            FCFA

                        </h5>


                        <a href="product-details.php?id=<?= $produit['id_produit'] ?>" class="btn btn-dark w-100 mt-2">

                            Voir le produit

                        </a>

                    </div>

                </div>

            </div>

            <?php endforeach; ?>

        </div>

    </div>

</section>


<!-- AVANTAGES -->

<section class="bg-white py-5">

    <div class="container">

        <div class="row g-4 text-center">

            <div class="col-md-4">

                <i class="bi bi-truck fs-1"></i>

                <h5 class="mt-3">
                    Livraison
                </h5>

                <p class="text-muted">
                    Livraison rapide et sécurisée.
                </p>

            </div>


            <div class="col-md-4">

                <i class="bi bi-shield-check fs-1"></i>

                <h5 class="mt-3">
                    Paiement sécurisé
                </h5>

                <p class="text-muted">
                    Vos transactions sont protégées.
                </p>

            </div>


            <div class="col-md-4">

                <i class="bi bi-headset fs-1"></i>

                <h5 class="mt-3">
                    Support client
                </h5>

                <p class="text-muted">
                    Une assistance à votre disposition.
                </p>

            </div>

        </div>

    </div>

</section>


<?php

require_once __DIR__ . "/includes/footer.php";

?>