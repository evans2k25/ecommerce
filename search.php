<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Produit.php";

$pageTitle = "Recherche";
$baseUrl = "";

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);

$keyword = trim($_GET['q'] ?? '');
$produits = [];

if ($keyword !== '') {
    $produits = $produitModel->search($keyword);
}

require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <h1 class="fw-bold mb-2">Recherche</h1>

    <?php if ($keyword === ''): ?>

    <p class="text-muted">Saisissez un mot-clé dans la barre de recherche.</p>

    <?php else: ?>

    <p class="text-muted mb-4">
        Résultats pour « <?= htmlspecialchars($keyword) ?> »
        (<?= count($produits) ?>)
    </p>

    <div class="row g-4">

        <?php if (empty($produits)): ?>

        <div class="col-12">
            <div class="alert alert-info">
                Aucun produit ne correspond à votre recherche.
            </div>
        </div>

        <?php else: ?>

        <?php foreach ($produits as $produit): ?>

        <div class="col-sm-6 col-lg-4 col-xl-3">

            <div class="card product-card h-100 shadow-sm">

                <?php if (!empty($produit['image'])): ?>

                <img src="uploads/products/<?= htmlspecialchars($produit['image']) ?>"
                    class="card-img-top"
                    alt="<?= htmlspecialchars($produit['nom']) ?>">

                <?php else: ?>

                <div class="bg-light d-flex align-items-center justify-content-center" style="height:250px;">
                    <i class="bi bi-image text-secondary" style="font-size: 60px;"></i>
                </div>

                <?php endif; ?>

                <div class="card-body d-flex flex-column">

                    <small class="text-muted">
                        <?= htmlspecialchars($produit['categorie']) ?>
                    </small>

                    <h5 class="card-title mt-2">
                        <?= htmlspecialchars($produit['nom']) ?>
                    </h5>

                    <h5 class="fw-bold mt-auto mb-3">
                        <?= number_format((float) $produit['prix'], 0, ',', ' ') ?> FCFA
                    </h5>

                    <a href="product-details.php?id=<?= (int) $produit['id_produit'] ?>"
                        class="btn btn-dark w-100">
                        Voir le produit
                    </a>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

        <?php endif; ?>

    </div>

    <?php endif; ?>

</div>

<?php require_once __DIR__ . "/includes/footer.php"; ?>
