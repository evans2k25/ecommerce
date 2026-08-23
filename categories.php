<?php

require_once __DIR__ . "/config/database.php";
require_once __DIR__ . "/models/Categorie.php";

$pageTitle = "Catégories";
$baseUrl = "";

$database = new Database();
$db = $database->getConnection();

$categorieModel = new Categorie($db);

$categories = $categorieModel->getAll();

require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <!-- En-tête -->
    <div class="text-center mb-5">

        <span class="category-title-badge">
            <i class="bi bi-grid"></i>
            Nos catégories
        </span>

        <h1 class="fw-bold mt-3">
            Explorez nos catégories
        </h1>

        <p class="text-muted">
            Découvrez nos différentes catégories de produits
            et trouvez facilement ce que vous recherchez.
        </p>

    </div>


    <!-- Vérification -->
    <?php if (empty($categories)): ?>

    <div class="text-center py-5">

        <div class="empty-category-icon">
            <i class="bi bi-box-seam"></i>
        </div>

        <h3 class="mt-3">
            Aucune catégorie disponible
        </h3>

        <p class="text-muted">
            Les catégories seront bientôt disponibles.
        </p>

        <a href="products.php" class="btn btn-primary">

            <i class="bi bi-shop"></i>

            Voir les produits

        </a>

    </div>

    <?php else: ?>


    <!-- Grille catégories -->

    <div class="row g-4">

        <?php foreach ($categories as $categorie): ?>

        <?php

                $image = !empty($categorie['image'])
                    ? "uploads/categories/" .
                      htmlspecialchars($categorie['image'])
                    : "assets/images/category-placeholder.jpg";

                ?>

        <div class="col-12 col-sm-6 col-lg-4 col-xl-3">

            <div class="category-card h-100">

                <!-- Image -->

                <div class="category-image">

                    <img src="<?= $image ?>" alt="<?= htmlspecialchars($categorie['nom']) ?>" loading="lazy">

                    <div class="category-overlay">

                        <a href="products.php?id_categorie=<?= (int) $categorie['id_categorie'] ?>"
                            class="btn category-btn">

                            <i class="bi bi-eye"></i>

                            Voir les produits

                        </a>

                    </div>

                </div>


                <!-- Contenu -->

                <div class="category-content">

                    <h3 class="category-name">

                        <?= htmlspecialchars(
                                    $categorie['nom']
                                ) ?>

                    </h3>


                    <?php if (!empty($categorie['description'])): ?>

                    <p class="category-description">

                        <?= htmlspecialchars(
                                        $categorie['description']
                                    ) ?>

                    </p>

                    <?php endif; ?>


                    <div class="category-footer">

                        <span class="product-count">

                            <i class="bi bi-box-seam"></i>

                            <?= (int) $categorie['total_products'] ?>

                            produit<?=

                                        (int) $categorie['total_products'] > 1
                                            ? 's'
                                            : ''

                                    ?>

                        </span>


                        <a href="products.php?id_categorie=<?= (int) $categorie['id_categorie'] ?>"
                            class="category-arrow">

                            <i class="bi bi-arrow-right"></i>

                        </a>

                    </div>

                </div>

            </div>

        </div>

        <?php endforeach; ?>

    </div>

    <?php endif; ?>

</div>


<style>
/*
|--------------------------------------------------------------------------
| Badge titre
|--------------------------------------------------------------------------
*/

.category-title-badge {

    display: inline-flex;

    align-items: center;

    gap: 8px;

    padding: 8px 16px;

    border-radius: 50px;

    background: rgba(237, 128, 233, 0.12);

    color: var(--primary);

    font-size: 14px;

    font-weight: 600;

}


/*
|--------------------------------------------------------------------------
| Carte catégorie
|--------------------------------------------------------------------------
*/

.category-card {

    background: var(--white);

    border-radius: 18px;

    overflow: hidden;

    border: 1px solid rgba(0, 0, 0, 0.05);

    box-shadow:
        0 5px 20px rgba(0, 0, 0, 0.05);

    transition:
        transform 0.3s ease,
        box-shadow 0.3s ease;

}


.category-card:hover {

    transform: translateY(-7px);

    box-shadow:
        0 15px 35px rgba(237,
            128,
            233,
            0.20);

}


/*
|--------------------------------------------------------------------------
| Image
|--------------------------------------------------------------------------
*/

.category-image {

    position: relative;

    height: 220px;

    overflow: hidden;

    background: #f5f5f7;

}


.category-image img {

    width: 100%;

    height: 100%;

    object-fit: cover;

    transition:
        transform 0.5s ease;

}


.category-card:hover .category-image img {

    transform: scale(1.08);

}


/*
|--------------------------------------------------------------------------
| Overlay
|--------------------------------------------------------------------------
*/

.category-overlay {

    position: absolute;

    inset: 0;

    display: flex;

    align-items: center;

    justify-content: center;

    background:
        rgba(31,
            31,
            41,
            0.55);

    opacity: 0;

    transition:
        opacity 0.3s ease;

}


.category-card:hover .category-overlay {

    opacity: 1;

}


/*
|--------------------------------------------------------------------------
| Bouton overlay
|--------------------------------------------------------------------------
*/

.category-btn {

    background: var(--primary);

    color: var(--white);

    border-radius: 10px;

    padding: 10px 18px;

    font-weight: 600;

    border: none;

    transition: 0.3s;

}


.category-btn:hover {

    background: var(--primary-dark);

    color: var(--white);

    transform: translateY(-2px);

}


/*
|--------------------------------------------------------------------------
| Contenu
|--------------------------------------------------------------------------
*/

.category-content {

    padding: 20px;

}


/*
|--------------------------------------------------------------------------
| Nom
|--------------------------------------------------------------------------
*/

.category-name {

    margin: 0 0 8px;

    font-size: 20px;

    font-weight: 700;

    color: var(--dark);

}


/*
|--------------------------------------------------------------------------
| Description
|--------------------------------------------------------------------------
*/

.category-description {

    color: #777;

    font-size: 14px;

    line-height: 1.6;

    display: -webkit-box;

    -webkit-line-clamp: 2;

    -webkit-box-orient: vertical;

    overflow: hidden;

    min-height: 45px;

}


/*
|--------------------------------------------------------------------------
| Footer carte
|--------------------------------------------------------------------------
*/

.category-footer {

    display: flex;

    align-items: center;

    justify-content: space-between;

    margin-top: 15px;

    padding-top: 15px;

    border-top:
        1px solid #eee;

}


/*
|--------------------------------------------------------------------------
| Nombre produits
|--------------------------------------------------------------------------
*/

.product-count {

    color: var(--primary);

    font-size: 13px;

    font-weight: 600;

}


.product-count i {

    margin-right: 5px;

}


/*
|--------------------------------------------------------------------------
| Flèche
|--------------------------------------------------------------------------
*/

.category-arrow {

    width: 38px;

    height: 38px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background: rgba(237,
            128,
            233,
            0.12);

    color: var(--primary);

    text-decoration: none;

    transition: 0.3s;

}


.category-arrow:hover {

    background: var(--primary);

    color: var(--white);

    transform: translateX(3px);

}


/*
|--------------------------------------------------------------------------
| Icône catégorie vide
|--------------------------------------------------------------------------
*/

.empty-category-icon {

    width: 80px;

    height: 80px;

    margin: auto;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 50%;

    background:
        rgba(237,
            128,
            233,
            0.12);

    color: var(--primary);

    font-size: 35px;

}
</style>