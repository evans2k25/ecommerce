<?php

session_start();

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Produit.php";

/*
|--------------------------------------------------------------------------
| Vérification de la connexion administrateur
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin'])) {
    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

$database = new Database();
$db = $database->getConnection();

$produitModel = new Produit($db);


/*
|--------------------------------------------------------------------------
| Récupération des produits
|--------------------------------------------------------------------------
*/

try {

    $produits = $produitModel->getAllAdmin();

} catch (Throwable $e) {

    $produits = [];

    $error = $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| Suppression d'un produit
|--------------------------------------------------------------------------
*/

if (
    isset($_GET['delete']) &&
    ctype_digit($_GET['delete'])
) {

    $idProduit = (int) $_GET['delete'];

    try {

        $produitModel->delete($idProduit);

        header("Location: index.php?success=deleted");
        exit;

    } catch (Throwable $e) {

        $error =
            "Impossible de supprimer le produit : "
            . $e->getMessage();
    }
}


/*
|--------------------------------------------------------------------------
| Messages
|--------------------------------------------------------------------------
*/

$success = '';

if (
    isset($_GET['success']) &&
    $_GET['success'] === 'deleted'
) {
    $success = "Le produit a été supprimé avec succès.";
} elseif (
    isset($_GET['success']) &&
    $_GET['success'] === 'created'
) {
    $success = "Le produit a été ajouté avec succès.";
} elseif (
    isset($_GET['success']) &&
    $_GET['success'] === 'updated'
) {
    $success = "Le produit a été modifié avec succès.";
} elseif (
    isset($_GET['success']) &&
    $_GET['success'] === 'archived'
) {
    $success = "Le produit a été archivé car il est lié à une commande.";
}

?>
<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Gestion des produits - Administration</title>


    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Google Font -->

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


    <style>
    :root {

        --primary: #ED80E9;
        --primary-dark: #C95BC5;
        --primary-light: #F5B3F2;

        --dark: #1F1F29;
        --text: #333333;
        --light: #F8F8FA;
        --white: #FFFFFF;

    }


    * {
        box-sizing: border-box;
    }


    body {

        font-family: "Poppins", sans-serif;

        background-color: var(--light);

        color: var(--text);

    }


    /* Navbar */

    .admin-navbar {

        background: var(--dark);

        min-height: 70px;

    }


    .admin-navbar .navbar-brand {

        color: var(--primary);

        font-weight: 700;

        font-size: 1.3rem;

    }


    .admin-navbar .navbar-brand:hover {

        color: var(--primary-light);

    }


    .admin-navbar .admin-name {

        color: white;

        font-size: 14px;

    }


    /* Contenu */

    .admin-content {

        padding: 35px;

    }


    /* En-tête */

    .page-title {

        font-weight: 700;

        color: var(--dark);

    }


    .page-subtitle {

        color: #777;

        font-size: 14px;

    }


    /* Bouton principal */

    .btn-primary-custom {

        background-color: var(--primary);

        border-color: var(--primary);

        color: white;

        font-weight: 600;

        border-radius: 10px;

        padding: 10px 18px;

    }


    .btn-primary-custom:hover {

        background-color: var(--primary-dark);

        border-color: var(--primary-dark);

        color: white;

    }


    /* Carte */

    .product-card {

        background: white;

        border: none;

        border-radius: 16px;

        box-shadow:
            0 5px 25px rgba(0, 0, 0, 0.06);

        overflow: hidden;

    }


    /* Tableau */

    .table {

        margin-bottom: 0;

        vertical-align: middle;

    }


    .table thead {

        background-color: #faf5fa;

    }


    .table thead th {

        color: var(--dark);

        font-size: 13px;

        font-weight: 600;

        border-bottom: 1px solid #eee;

        padding: 16px;

    }


    .table tbody td {

        padding: 15px;

        font-size: 14px;

        border-color: #f0f0f0;

    }


    .table tbody tr:hover {

        background-color: #fff8ff;

    }


    /* Image produit */

    .product-image {

        width: 60px;

        height: 60px;

        border-radius: 10px;

        object-fit: cover;

        background-color: #f4f4f4;

    }


    .product-placeholder {

        width: 60px;

        height: 60px;

        border-radius: 10px;

        background-color: #f4f4f4;

        display: flex;

        align-items: center;

        justify-content: center;

        color: #aaa;

        font-size: 22px;

    }


    .product-name {

        font-weight: 600;

        color: var(--dark);

    }


    /* Prix */

    .product-price {

        color: var(--primary-dark);

        font-weight: 700;

    }


    /* Badges */

    .badge-status {

        padding: 7px 11px;

        border-radius: 20px;

        font-size: 11px;

        font-weight: 600;

    }


    .badge-disponible {

        background-color: #e8f8ef;

        color: #198754;

    }


    .badge-indisponible {

        background-color: #fff3cd;

        color: #856404;

    }


    .badge-archive {

        background-color: #eeeeee;

        color: #666;

    }


    /* Stock */

    .stock-ok {

        color: #198754;

        font-weight: 600;

    }


    .stock-low {

        color: #dc3545;

        font-weight: 600;

    }


    /* Actions */

    .action-btn {

        width: 36px;

        height: 36px;

        display: inline-flex;

        align-items: center;

        justify-content: center;

        border-radius: 8px;

        border: none;

    }


    .btn-edit {

        background-color: #f1e8ff;

        color: #6f42c1;

    }


    .btn-edit:hover {

        background-color: #6f42c1;

        color: white;

    }


    .btn-delete {

        background-color: #ffe8e8;

        color: #dc3545;

    }


    .btn-delete:hover {

        background-color: #dc3545;

        color: white;

    }


    /* Responsive */

    @media (max-width: 768px) {

        .admin-content {

            padding: 20px;

        }

    }
    </style>

</head>


<body>




    <!--
|--------------------------------------------------------------------------
| Navbar administration
|--------------------------------------------------------------------------
-->

    <nav class="navbar admin-navbar">

        <div class="container-fluid px-4">

            <a href="../dashboard.php" class="navbar-brand">

                <i class="bi bi-shop"></i>

                E-Commerce Admin

            </a>


            <div class="d-flex align-items-center gap-3">

                <span class="admin-name">

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars(
                    $_SESSION['admin']['prenom']
                    ?? $_SESSION['admin']['nom']
                    ?? 'Administrateur'
                ) ?>

                </span>


                <a href="../logout.php" class="btn btn-outline-light btn-sm">

                    <i class="bi bi-box-arrow-right"></i>

                    Déconnexion

                </a>

            </div>

        </div>

    </nav>


    <!--
|--------------------------------------------------------------------------
| Contenu
|--------------------------------------------------------------------------
-->

    <main class="admin-content">

        <div class="container-fluid">


            <!-- En-tête -->

            <div class="d-flex justify-content-between align-items-center mb-4">

                <div>

                    <h1 class="page-title mb-1">

                        <i class="bi bi-box-seam"></i>

                        Produits

                    </h1>

                    <p class="page-subtitle mb-0">

                        Gérez les produits de votre boutique.

                    </p>

                </div>


                <a href="create.php" class="btn btn-primary-custom">

                    <i class="bi bi-plus-lg"></i>

                    Ajouter un produit

                </a>

            </div>


            <!--
        |--------------------------------------------------------------------------
        | Message succès
        |--------------------------------------------------------------------------
        -->

            <?php if ($success): ?>

            <div class="alert alert-success alert-dismissible fade show" role="alert">

                <i class="bi bi-check-circle"></i>

                <?= htmlspecialchars($success) ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

            <?php endif; ?>


            <!--
        |--------------------------------------------------------------------------
        | Message erreur
        |--------------------------------------------------------------------------
        -->

            <?php if (!empty($error)): ?>

            <div class="alert alert-danger alert-dismissible fade show" role="alert">

                <i class="bi bi-exclamation-triangle"></i>

                <?= htmlspecialchars($error) ?>

                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

            </div>

            <?php endif; ?>


            <!--
        |--------------------------------------------------------------------------
        | Tableau
        |--------------------------------------------------------------------------
        -->

            <div class="product-card">

                <div class="table-responsive">

                    <table class="table">

                        <thead>

                            <tr>

                                <th>#</th>

                                <th>Produit</th>

                                <th>Catégorie</th>

                                <th>Prix</th>

                                <th>Stock</th>

                                <th>Statut</th>

                                <th class="text-end">
                                    Actions
                                </th>

                            </tr>

                        </thead>


                        <tbody>

                            <?php if (empty($produits)): ?>

                            <tr>

                                <td colspan="7" class="text-center py-5">

                                    <div class="mb-3">

                                        <i class="bi bi-box-seam" style="
                                            font-size: 45px;
                                            color: #ccc;
                                        "></i>

                                    </div>

                                    <h5>

                                        Aucun produit

                                    </h5>

                                    <p class="text-muted">

                                        Aucun produit n'a encore
                                        été ajouté.

                                    </p>


                                    <a href="create.php" class="btn btn-primary-custom">

                                        <i class="bi bi-plus-lg"></i>

                                        Ajouter le premier produit

                                    </a>

                                </td>

                            </tr>

                            <?php else: ?>


                            <?php foreach (
                            $produits as $produit
                        ): ?>

                            <?php

                            $statut =
                                $produit['statut']
                                ?? 'disponible';


                            if (
                                $statut ===
                                'disponible'
                            ) {

                                $badgeClass =
                                    'badge-disponible';

                                $badgeText =
                                    'Disponible';

                            } elseif (
                                $statut ===
                                'indisponible'
                            ) {

                                $badgeClass =
                                    'badge-indisponible';

                                $badgeText =
                                    'Indisponible';

                            } else {

                                $badgeClass =
                                    'badge-archive';

                                $badgeText =
                                    'Archivé';
                            }


                            $stock =
                                (int) (
                                    $produit['stock']
                                    ?? 0
                                );

                            ?>


                            <tr>


                                <!-- ID -->

                                <td>

                                    <?= (int)
                                        $produit[
                                            'id_produit'
                                        ]
                                    ?>

                                </td>


                                <!-- Produit -->

                                <td>

                                    <div class="d-flex align-items-center gap-3">

                                        <?php if (
                                            !empty(
                                                $produit['image']
                                            )
                                        ): ?>

                                        <img src="../../uploads/products/<?= htmlspecialchars(
                                                    $produit['image']
                                                ) ?>" alt="<?= htmlspecialchars(
                                                    $produit['nom']
                                                ) ?>" class="product-image">

                                        <?php else: ?>

                                        <div class="product-placeholder">

                                            <i class="bi bi-image"></i>

                                        </div>

                                        <?php endif; ?>


                                        <div>

                                            <div class="product-name">

                                                <?= htmlspecialchars(
                                                    $produit['nom']
                                                ) ?>

                                            </div>

                                            <small class="text-muted">

                                                ID :
                                                <?= (int)
                                                    $produit[
                                                        'id_produit'
                                                    ]
                                                ?>

                                            </small>

                                        </div>

                                    </div>

                                </td>


                                <!-- Catégorie -->

                                <td>

                                    <?= htmlspecialchars(
                                        $produit[
                                            'categorie_nom'
                                        ]
                                        ?? 'Sans catégorie'
                                    ) ?>

                                </td>


                                <!-- Prix -->

                                <td>

                                    <span class="product-price">

                                        <?= number_format(
                                            (float)
                                            $produit['prix'],
                                            0,
                                            ',',
                                            ' '
                                        ) ?>

                                        FCFA

                                    </span>

                                </td>


                                <!-- Stock -->

                                <td>

                                    <span class="<?= $stock <= 5
                                            ? 'stock-low'
                                            : 'stock-ok'
                                        ?>">

                                        <?= $stock ?>

                                    </span>

                                </td>


                                <!-- Statut -->

                                <td>

                                    <span class="badge-status
                                        <?= $badgeClass ?>">

                                        <?= $badgeText ?>

                                    </span>

                                </td>


                                <!-- Actions -->

                                <td class="text-end">

                                    <div class="d-flex
                                        justify-content-end
                                        gap-2">


                                        <!-- Modifier -->

                                        <a href="edit.php?id=<?= (int) $produit['id_produit'] ?>"
                                            class="action-btn btn-edit" title="Modifier">

                                            <i class="bi bi-pencil"></i>

                                        </a>


                                        <!-- Supprimer -->

                                        <a href="delete.php?id=<?= (int) $produit['id_produit'] ?>"
                                            class="action-btn btn-delete" title="Supprimer" onclick="return confirm(
                                                'Voulez-vous vraiment supprimer ce produit ?'
                                            );">

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

        </div>

    </main>


    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

</body>

</html>