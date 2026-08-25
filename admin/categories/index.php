<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Categorie.php";

$database = new Database();
$db = $database->getConnection();

$categorieModel = new Categorie($db);

$error = '';
$success = '';

try {

    $categories = $categorieModel->getAllAdmin();

} catch (Throwable $e) {

    $categories = [];

    $error = $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| Messages de succès
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    $messages = [

        'created' => "La catégorie a été créée.",
        'updated' => "La catégorie a été modifiée.",
        'deleted' => "La catégorie a été supprimée."

    ];

    $success = $messages[$_GET['success']] ?? '';
}


/*
|--------------------------------------------------------------------------
| Messages d'erreur
|--------------------------------------------------------------------------
*/

if (isset($_GET['error'])) {

    $error = $_GET['error'] === 'has_products'

        ? "Impossible de supprimer une catégorie qui contient encore des produits."

        : htmlspecialchars($_GET['error']);
}


$pageTitle = "Catégories";
$adminPage = "categories";

require_once __DIR__ . "/../includes/header.php";

?>


<style>
/* =========================================================
   VARIABLES
========================================================= */

:root {

    --primary: #ED80E9;
    --primary-dark: #C95BC5;
    --primary-light: #F8D9F7;

    --dark: #1F1F29;
    --text: #555;
    --muted: #888;

    --light: #F8F8FA;
    --white: #FFFFFF;

    --success: #198754;
    --danger: #dc3545;
    --warning: #ffc107;

}


/* =========================================================
   BODY
========================================================= */

body {

    background-color: var(--light);

    color: var(--text);

}


/* =========================================================
   ANIMATIONS GÉNÉRALES
========================================================= */

@keyframes fadeIn {

    from {

        opacity: 0;

    }

    to {

        opacity: 1;

    }

}


@keyframes fadeUp {

    from {

        opacity: 0;

        transform: translateY(25px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


@keyframes fadeDown {

    from {

        opacity: 0;

        transform: translateY(-20px);

    }

    to {

        opacity: 1;

        transform: translateY(0);

    }

}


@keyframes scaleIn {

    from {

        opacity: 0;

        transform: scale(.92);

    }

    to {

        opacity: 1;

        transform: scale(1);

    }

}


@keyframes slideRight {

    from {

        opacity: 0;

        transform: translateX(-20px);

    }

    to {

        opacity: 1;

        transform: translateX(0);

    }

}


@keyframes iconPulse {

    0% {

        transform: scale(1);

    }

    50% {

        transform: scale(1.12);

    }

    100% {

        transform: scale(1);

    }

}


@keyframes iconFloat {

    0% {

        transform: translateY(0);

    }

    50% {

        transform: translateY(-3px);

    }

    100% {

        transform: translateY(0);

    }

}


@keyframes alertSlide {

    from {

        opacity: 0;

        transform: translateY(-15px) scale(.98);

    }

    to {

        opacity: 1;

        transform: translateY(0) scale(1);

    }

}


/* =========================================================
   EN-TÊTE DE PAGE
========================================================= */

.category-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

    background-color: var(--white);

    padding: 22px 25px;

    border-radius: 18px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow: 0 5px 20px rgba(0, 0, 0, .04);

    animation: fadeDown .6s ease both;

}


.category-title {

    margin: 0;

    color: var(--dark);

    font-size: 24px;

    font-weight: 700;

}


.category-title i {

    color: var(--primary);

    margin-right: 8px;

    display: inline-block;

    animation: iconFloat 2.5s ease-in-out infinite;

}


.category-subtitle {

    color: var(--muted);

    font-size: 13px;

    margin: 5px 0 0;

    animation: fadeIn .8s ease .2s both;

}


/* =========================================================
   BOUTON AJOUTER
========================================================= */

.btn-primary-custom {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    background-color: var(--primary);

    border: none;

    color: white;

    padding: 10px 17px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    text-decoration: none;

    box-shadow: 0 5px 15px rgba(237, 128, 233, .25);

    transition:
        background-color .25s ease,
        color .25s ease,
        transform .25s ease,
        box-shadow .25s ease;

}


.btn-primary-custom:hover {

    background-color: var(--primary-dark);

    color: white;

    transform: translateY(-3px) scale(1.02);

    box-shadow: 0 8px 20px rgba(201, 91, 197, .30);

}


.btn-primary-custom:active {

    transform: translateY(0) scale(.98);

}


.btn-primary-custom i {

    transition: transform .25s ease;

}


.btn-primary-custom:hover i {

    transform: rotate(90deg);

}


/* =========================================================
   ALERTES
========================================================= */

.category-alert {

    border: none;

    border-radius: 12px;

    padding: 13px 16px;

    font-size: 13px;

    box-shadow: 0 4px 15px rgba(0, 0, 0, .04);

    animation: alertSlide .5s ease both;

}


.category-alert .bi {

    display: inline-block;

    animation: iconPulse 1.5s ease-in-out infinite;

}


/* =========================================================
   CARTE
========================================================= */

.category-card {

    background-color: var(--white);

    border-radius: 18px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow: 0 5px 25px rgba(0, 0, 0, .05);

    overflow: hidden;

    animation: fadeUp .7s ease .15s both;

    transition:
        box-shadow .3s ease,
        transform .3s ease;

}


.category-card:hover {

    box-shadow: 0 12px 35px rgba(0, 0, 0, .08);

}


/* =========================================================
   EN-TÊTE CARTE
========================================================= */

.category-card-header {

    display: flex;

    align-items: center;

    justify-content: space-between;

    padding: 20px 22px;

    border-bottom: 1px solid #eee;

}


.category-card-title {

    margin: 0;

    color: var(--dark);

    font-size: 16px;

    font-weight: 700;

}


.category-card-title i {

    color: var(--primary);

    margin-right: 7px;

    display: inline-block;

    transition: transform .3s ease;

}


.category-card-header:hover .category-card-title i {

    transform: rotate(-8deg) scale(1.15);

}


.category-count {

    background-color: var(--primary-light);

    color: var(--primary-dark);

    padding: 5px 10px;

    border-radius: 8px;

    font-size: 11px;

    font-weight: 700;

    animation: scaleIn .5s ease .5s both;

}


/* =========================================================
   TABLEAU
========================================================= */

.category-card .table {

    margin-bottom: 0;

    vertical-align: middle;

}


.category-card .table thead th {

    background-color: #fafafa;

    color: #777;

    border-bottom: 1px solid #eee;

    padding: 15px 18px;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    white-space: nowrap;

}


/* =========================================================
   LIGNES ANIMÉES
========================================================= */

.category-card .table tbody tr {

    transition:
        background-color .25s ease,
        transform .25s ease,
        box-shadow .25s ease;

    animation: slideRight .45s ease both;

}


/*
|--------------------------------------------------------------------------
| Animation progressive des lignes
|--------------------------------------------------------------------------
*/

.category-card .table tbody tr:nth-child(1) {
    animation-delay: .15s;
}

.category-card .table tbody tr:nth-child(2) {
    animation-delay: .20s;
}

.category-card .table tbody tr:nth-child(3) {
    animation-delay: .25s;
}

.category-card .table tbody tr:nth-child(4) {
    animation-delay: .30s;
}

.category-card .table tbody tr:nth-child(5) {
    animation-delay: .35s;
}

.category-card .table tbody tr:nth-child(6) {
    animation-delay: .40s;
}

.category-card .table tbody tr:nth-child(7) {
    animation-delay: .45s;
}

.category-card .table tbody tr:nth-child(8) {
    animation-delay: .50s;
}

.category-card .table tbody tr:nth-child(9) {
    animation-delay: .55s;
}

.category-card .table tbody tr:nth-child(10) {
    animation-delay: .60s;
}


.category-card .table tbody td {

    padding: 15px 18px;

    border-bottom: 1px solid #f1f1f1;

    font-size: 13px;

    color: var(--text);

}


.category-card .table tbody tr:hover {

    background-color: #fff8ff;

    transform: translateX(4px);

}


.category-card .table tbody tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   ID
========================================================= */

.category-id {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 34px;

    height: 27px;

    padding: 0 8px;

    border-radius: 7px;

    background-color: #f2f2f2;

    color: #777;

    font-size: 11px;

    font-weight: 700;

    transition:
        background-color .25s ease,
        color .25s ease,
        transform .25s ease;

}


tr:hover .category-id {

    background-color: var(--primary-light);

    color: var(--primary-dark);

    transform: scale(1.05);

}


/* =========================================================
   NOM CATÉGORIE
========================================================= */

.category-name-wrapper {

    display: flex;

    align-items: center;

    gap: 12px;

}


.category-icon {

    width: 40px;

    height: 40px;

    min-width: 40px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 10px;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 18px;

    transition:
        transform .3s ease,
        background-color .3s ease,
        box-shadow .3s ease;

}


.category-name-wrapper:hover .category-icon {

    transform: rotate(-8deg) scale(1.12);

    background-color: #f3c4f1;

    box-shadow: 0 5px 12px rgba(237, 128, 233, .20);

}


.category-name {

    color: var(--dark);

    font-size: 13px;

    font-weight: 650;

    transition: color .25s ease;

}


.category-name-wrapper:hover .category-name {

    color: var(--primary-dark);

}


/* =========================================================
   NOMBRE DE PRODUITS
========================================================= */

.product-count {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    background-color: #f4f4f6;

    color: #555;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 11px;

    font-weight: 600;

    transition:
        transform .25s ease,
        background-color .25s ease;

}


.product-count i {

    color: var(--primary-dark);

    transition: transform .25s ease;

}


tr:hover .product-count {

    transform: scale(1.05);

    background-color: var(--primary-light);

}


tr:hover .product-count i {

    transform: translateY(-2px);

}


/* =========================================================
   STATUT
========================================================= */

.category-status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 700;

    transition:
        transform .25s ease,
        box-shadow .25s ease;

}


.category-status:hover {

    transform: scale(1.05);

}


.status-active {

    color: #146c43;

    background-color: #d1e7dd;

}


.status-inactive {

    color: #41464b;

    background-color: #e2e3e5;

}


.status-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background-color: currentColor;

}


.status-active .status-dot {

    animation: statusPulse 1.8s ease-in-out infinite;

}


@keyframes statusPulse {

    0% {

        transform: scale(1);

        opacity: 1;

    }

    50% {

        transform: scale(1.5);

        opacity: .5;

    }

    100% {

        transform: scale(1);

        opacity: 1;

    }

}


/* =========================================================
   ACTIONS
========================================================= */

.category-actions {

    display: flex;

    justify-content: flex-end;

    gap: 7px;

}


.action-btn {

    width: 37px;

    height: 37px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    border: none;

    text-decoration: none;

    transition:
        transform .25s ease,
        background-color .25s ease,
        color .25s ease,
        box-shadow .25s ease;

}


.btn-edit {

    background-color: #f1e8ff;

    color: #6f42c1;

}


.btn-edit:hover {

    background-color: #6f42c1;

    color: white;

    transform: translateY(-3px) rotate(-3deg);

    box-shadow: 0 6px 14px rgba(111, 66, 193, .25);

}


.btn-delete {

    background-color: #ffe8e8;

    color: var(--danger);

}


.btn-delete:hover {

    background-color: var(--danger);

    color: white;

    transform: translateY(-3px) rotate(3deg);

    box-shadow: 0 6px 14px rgba(220, 53, 69, .25);

}


.action-btn:active {

    transform: scale(.92);

}


/* =========================================================
   ÉTAT VIDE
========================================================= */

.empty-category {

    text-align: center;

    padding: 60px 20px;

    animation: fadeUp .6s ease both;

}


.empty-category-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 15px;

    display: flex;

    align-items: center;

    justify-content: center;

    border-radius: 18px;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 30px;

    animation: iconFloat 2.5s ease-in-out infinite;

}


.empty-category h5 {

    color: var(--dark);

    font-weight: 700;

    margin-bottom: 6px;

}


.empty-category p {

    color: #999;

    font-size: 13px;

    margin-bottom: 18px;

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .category-header {

        align-items: flex-start;

        flex-direction: column;

        gap: 15px;

        padding: 20px;

    }


    .category-title {

        font-size: 21px;

    }


    .category-card {

        border-radius: 14px;

    }


    .category-card .table thead th,
    .category-card .table tbody td {

        padding: 12px 10px;

    }


    .category-actions {

        justify-content: flex-start;

    }


    .category-name-wrapper {

        gap: 8px;

    }


    .category-icon {

        width: 36px;

        height: 36px;

        min-width: 36px;

    }

}


/* =========================================================
   ACCESSIBILITÉ
========================================================= */

@media (prefers-reduced-motion: reduce) {

    *,
    *::before,
    *::after {

        animation-duration: 0.01ms !important;

        animation-iteration-count: 1 !important;

        transition-duration: 0.01ms !important;

        scroll-behavior: auto !important;

    }

}
</style>


<!-- =========================================================
     EN-TÊTE
========================================================= -->

<div class="category-header">

    <div>

        <h1 class="category-title">

            <i class="bi bi-grid"></i>

            Catégories

        </h1>

        <p class="category-subtitle">

            Gérez les catégories de votre boutique.

        </p>

    </div>


    <a href="create.php" class="btn-primary-custom">

        <i class="bi bi-plus-lg"></i>

        Ajouter une catégorie

    </a>

</div>


<!-- =========================================================
     MESSAGE SUCCÈS
========================================================= -->

<?php if ($success): ?>

<div class="alert alert-success category-alert alert-dismissible fade show" role="alert">

    <i class="bi bi-check-circle me-2"></i>

    <?= htmlspecialchars($success) ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>


<!-- =========================================================
     MESSAGE ERREUR
========================================================= -->

<?php if ($error): ?>

<div class="alert alert-danger category-alert alert-dismissible fade show" role="alert">

    <i class="bi bi-exclamation-triangle me-2"></i>

    <?= htmlspecialchars($error) ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>


<!-- =========================================================
     CARTE CATÉGORIES
========================================================= -->

<div class="category-card">


    <!-- En-tête de la carte -->

    <div class="category-card-header">

        <h5 class="category-card-title">

            <i class="bi bi-list-ul"></i>

            Liste des catégories

        </h5>


        <span class="category-count">

            <?= count($categories) ?>

            catégorie<?= count($categories) > 1 ? 's' : '' ?>

        </span>

    </div>


    <!-- Tableau -->

    <div class="table-responsive">

        <table class="table align-middle mb-0">

            <thead>

                <tr>

                    <th>#</th>

                    <th>Catégorie</th>

                    <th>Produits</th>

                    <th>Statut</th>

                    <th class="text-end">Actions</th>

                </tr>

            </thead>


            <tbody>

                <?php if (empty($categories)): ?>

                <tr>

                    <td colspan="5">

                        <div class="empty-category">

                            <div class="empty-category-icon">

                                <i class="bi bi-grid"></i>

                            </div>

                            <h5>Aucune catégorie</h5>

                            <p>
                                Aucune catégorie n'a encore été créée.
                            </p>

                            <a href="create.php" class="btn-primary-custom">

                                <i class="bi bi-plus-lg"></i>

                                Ajouter la première catégorie

                            </a>

                        </div>

                    </td>

                </tr>

                <?php else: ?>

                <?php foreach ($categories as $categorie): ?>

                <?php

                $isActive =
                    $categorie['statut'] === 'active';

                ?>

                <tr>

                    <!-- ID -->

                    <td>

                        <span class="category-id">

                            #<?= (int)$categorie['id_categorie'] ?>

                        </span>

                    </td>


                    <!-- NOM -->

                    <td>

                        <div class="category-name-wrapper">

                            <div class="category-icon">

                                <i class="bi bi-tag"></i>

                            </div>

                            <div>

                                <div class="category-name">

                                    <?= htmlspecialchars(
                                        $categorie['nom']
                                    ) ?>

                                </div>

                            </div>

                        </div>

                    </td>


                    <!-- PRODUITS -->

                    <td>

                        <span class="product-count">

                            <i class="bi bi-box-seam"></i>

                            <?= (int)$categorie['total_products'] ?>

                            produit<?=

                                (int)$categorie['total_products'] > 1
                                    ? 's'
                                    : ''

                            ?>

                        </span>

                    </td>


                    <!-- STATUT -->

                    <td>

                        <?php if ($isActive): ?>

                        <span class="category-status status-active">

                            <span class="status-dot"></span>

                            Active

                        </span>

                        <?php else: ?>

                        <span class="category-status status-inactive">

                            <span class="status-dot"></span>

                            Inactive

                        </span>

                        <?php endif; ?>

                    </td>


                    <!-- ACTIONS -->

                    <td class="text-end">

                        <div class="category-actions">

                            <a href="edit.php?id=<?= (int)$categorie['id_categorie'] ?>" class="action-btn btn-edit"
                                title="Modifier">

                                <i class="bi bi-pencil"></i>

                            </a>

                            <a href="delete.php?id=<?= (int)$categorie['id_categorie'] ?>" class="action-btn btn-delete"
                                title="Supprimer"
                                onclick="return confirm('Voulez-vous vraiment supprimer cette catégorie ?');">

                                <i class="bi bi-trash"></i>

                            </a>

                        </div>

                    </td>

                </tr>

                <?php endforeach; ?>

                <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<?php require_once __DIR__ . "/../includes/footer.php"; ?>