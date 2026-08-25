<?php

/*
|--------------------------------------------------------------------------
| AUTHENTIFICATION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/auth.php';


/*
|--------------------------------------------------------------------------
| CONFIGURATION
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Produit.php';


/*
|--------------------------------------------------------------------------
| INITIALISATION
|--------------------------------------------------------------------------
*/

$error = '';
$success = '';

$q = trim($_GET['q'] ?? '');

$page = isset($_GET['page']) && ctype_digit($_GET['page'])
    ? max(1, (int) $_GET['page'])
    : 1;

$perPage = 10;

$produits = [];

$total = 0;


/*
|--------------------------------------------------------------------------
| CONNEXION BASE DE DONNÉES
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();

    $db = $database->getConnection();

    $produitModel = new Produit($db);


    /*
    |--------------------------------------------------------------------------
    | RÉCUPÉRATION DES PRODUITS
    |--------------------------------------------------------------------------
    |
    | On utilise les méthodes disponibles dans le modèle Produit.
    |
    */

    if (!empty($q)) {

        /*
        | Recherche
        */

        if (method_exists($produitModel, 'search')) {

            $produits = $produitModel->search($q);

        } elseif (method_exists($produitModel, 'searchAdmin')) {

            $produits = $produitModel->searchAdmin($q);

        } else {

            /*
            | Recherche directe si le modèle ne possède
            | pas de méthode search().
            */

            $sql = "
                SELECT
                    p.*,
                    c.nom AS categorie_nom
                FROM produits p
                LEFT JOIN categories c
                    ON c.id_categorie = p.id_categorie
                WHERE
                    p.nom LIKE :q
                    OR p.description LIKE :q
                ORDER BY p.id_produit DESC
            ";

            $stmt = $db->prepare($sql);

            $stmt->execute([
                ':q' => '%' . $q . '%'
            ]);

            $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }

    } else {

        /*
        | Récupération normale
        */

        if (method_exists($produitModel, 'getAllAdmin')) {

            $produits = $produitModel->getAllAdmin();

        } elseif (method_exists($produitModel, 'getAll')) {

            $produits = $produitModel->getAll();

        } else {

            /*
            | Requête de secours
            */

            $sql = "
                SELECT
                    p.*,
                    c.nom AS categorie_nom
                FROM produits p
                LEFT JOIN categories c
                    ON c.id_categorie = p.id_categorie
                ORDER BY p.id_produit DESC
            ";

            $stmt = $db->query($sql);

            $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }
    }


    /*
    |--------------------------------------------------------------------------
    | SÉCURITÉ
    |--------------------------------------------------------------------------
    */

    if (!is_array($produits)) {

        $produits = [];

    }


    /*
    |--------------------------------------------------------------------------
    | TOTAL
    |--------------------------------------------------------------------------
    */

    $total = count($produits);


    /*
    |--------------------------------------------------------------------------
    | PAGINATION
    |--------------------------------------------------------------------------
    |
    | Si le modèle retourne tous les produits, on fait la pagination
    | ici.
    |
    */

    if ($total > $perPage) {

        $offset = ($page - 1) * $perPage;

        $produits = array_slice(
            $produits,
            $offset,
            $perPage
        );

    }


} catch (Throwable $e) {

    $produits = [];

    $total = 0;

    $error = $e->getMessage();

}


/*
|--------------------------------------------------------------------------
| MESSAGES
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    $messages = [

        'created' =>
            'Le produit a été créé avec succès.',

        'updated' =>
            'Le produit a été modifié avec succès.',

        'deleted' =>
            'Le produit a été supprimé avec succès.'

    ];

    $success =
        $messages[$_GET['success']]
        ?? '';

}


if (isset($_GET['error'])) {

    $error = htmlspecialchars(
        $_GET['error'],
        ENT_QUOTES,
        'UTF-8'
    );

}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

$pageTitle = 'Produits';

$adminPage = 'products';


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

?>

<style>
:root {

    --primary: #ED80E9;
    --primary-dark: #C95BC5;
    --primary-light: #F8D9F7;

    --dark: #1F1F29;
    --text: #555;
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
   CONTENU
========================================================= */

.admin-content {

    padding: 30px;

}


.page-title {

    color: var(--dark);

    font-size: 27px;

    font-weight: 700;

}


.page-title i {

    color: var(--primary);

    margin-right: 8px;

}


.page-subtitle {

    color: #888;

    font-size: 14px;

}


/* =========================================================
   BOUTON PRINCIPAL
========================================================= */

.btn-primary-custom {

    background-color: var(--primary);

    border: none;

    color: white;

    padding: 11px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(237, 128, 233, .25);

    transition: all .25s ease;

}


.btn-primary-custom:hover {

    background-color: var(--primary-dark);

    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(201, 91, 197, .30);

}


/* =========================================================
   RECHERCHE
========================================================= */

.search-box {

    background-color: white;

    border-radius: 14px;

    padding: 15px;

    margin-bottom: 20px;

    border: 1px solid #eee;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .04);

}


.search-box .form-control {

    height: 43px;

    border: 1px solid #e5e5e5;

    font-size: 13px;

    box-shadow: none;

}


.search-box .form-control:focus {

    border-color: var(--primary);

    box-shadow:
        0 0 0 .2rem rgba(237, 128, 233, .12);

}


.search-box .input-group-text {

    border-color: #e5e5e5;

}


/* =========================================================
   ALERTES
========================================================= */

.alert {

    border: none;

    border-radius: 12px;

    font-size: 13px;

    box-shadow:
        0 4px 15px rgba(0, 0, 0, .04);

}


/* =========================================================
   CARTE PRODUITS
========================================================= */

.product-card {

    background-color: white;

    border-radius: 18px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow:
        0 5px 25px rgba(0, 0, 0, .05);

    overflow: hidden;

}


/* =========================================================
   TABLEAU
========================================================= */

.product-card .table {

    margin-bottom: 0;

    vertical-align: middle;

}


.product-card .table thead th {

    background-color: #fafafa;

    color: #777;

    border-bottom: 1px solid #eee;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    padding: 16px 15px;

    white-space: nowrap;

}


.product-card .table tbody td {

    padding: 15px;

    border-bottom: 1px solid #f1f1f1;

    font-size: 13px;

    color: #555;

}


.product-card .table tbody tr {

    transition: all .2s ease;

}


.product-card .table tbody tr:hover {

    background-color: #fff8ff;

}


.product-card .table tbody tr:last-child td {

    border-bottom: none;

}


/* =========================================================
   IMAGE PRODUIT
========================================================= */

.product-image,
.product-placeholder {

    width: 52px;

    height: 52px;

    min-width: 52px;

    border-radius: 12px;

}


.product-image {

    object-fit: cover;

    border: 1px solid #eee;

    background-color: #fafafa;

}


.product-placeholder {

    display: flex;

    align-items: center;

    justify-content: center;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 20px;

}


.product-name {

    color: var(--dark);

    font-size: 13px;

    font-weight: 650;

    margin-bottom: 3px;

}


/* =========================================================
   PRIX
========================================================= */

.product-price {

    color: var(--primary-dark);

    font-weight: 700;

    white-space: nowrap;

}


/* =========================================================
   STOCK
========================================================= */

.stock-ok,
.stock-low {

    display: inline-flex;

    align-items: center;

    justify-content: center;

    min-width: 55px;

    padding: 5px 9px;

    border-radius: 7px;

    font-size: 12px;

    font-weight: 700;

}


.stock-ok {

    color: #146c43;

    background-color: #d1e7dd;

}


.stock-low {

    color: #842029;

    background-color: #f8d7da;

}


/* =========================================================
   STATUT
========================================================= */

.badge-status {

    display: inline-block;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 700;

}


.badge-disponible {

    color: #146c43;

    background-color: #d1e7dd;

}


.badge-indisponible {

    color: #664d03;

    background-color: #fff3cd;

}


.badge-archive {

    color: #41464b;

    background-color: #e2e3e5;

}


/* =========================================================
   ACTIONS
========================================================= */

.action-btn {

    width: 37px;

    height: 37px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    border: none;

    text-decoration: none;

    transition: all .25s ease;

}


.btn-edit {

    background-color: #f1e8ff;

    color: #6f42c1;

}


.btn-edit:hover {

    background-color: #6f42c1;

    color: white;

    transform: translateY(-2px);

}


.btn-delete {

    background-color: #ffe8e8;

    color: var(--danger);

}


.btn-delete:hover {

    background-color: var(--danger);

    color: white;

    transform: translateY(-2px);

}


/* =========================================================
   PRODUIT VIDE
========================================================= */

.empty-product {

    padding: 60px 20px;

    text-align: center;

}


.empty-product i {

    display: block;

    font-size: 50px;

    color: #ddd;

    margin-bottom: 15px;

}


.empty-product h5 {

    color: var(--dark);

    font-weight: 700;

}


.empty-product p {

    color: #999;

    font-size: 13px;

}


/* =========================================================
   PAGINATION
========================================================= */

.pagination {

    margin-bottom: 0;

}


.pagination .page-link {

    color: var(--primary-dark);

    border: none;

    margin: 0 3px;

    border-radius: 8px;

    font-size: 13px;

    font-weight: 600;

}


.pagination .page-link:hover {

    background-color: var(--primary-light);

    color: var(--primary-dark);

}


.pagination .page-item.active .page-link {

    background-color: var(--primary);

    color: white;

    box-shadow:
        0 4px 10px rgba(237, 128, 233, .25);

}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width:768px) {

    .admin-content {

        padding: 20px 15px;

    }


    .page-title {

        font-size: 22px;

    }


    .product-card {

        border-radius: 14px;

    }


    .product-card .table thead th,
    .product-card .table tbody td {

        padding: 12px 10px;

    }


    .btn-primary-custom {

        padding: 9px 13px;

    }

}
</style>


<!-- =========================================================
     CONTENU
========================================================= -->

<main class="admin-content">

    <div class="container-fluid">


        <!-- =====================================================
             EN-TÊTE
        ====================================================== -->

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

                <i class="bi bi-plus-lg me-1"></i>

                Ajouter un produit

            </a>

        </div>


        <!-- =====================================================
             RECHERCHE
        ====================================================== -->

        <div class="search-box">

            <form method="GET">

                <div class="row g-2 align-items-center">

                    <div class="col">

                        <div class="input-group">

                            <span class="input-group-text bg-white">

                                <i class="bi bi-search text-muted"></i>

                            </span>


                            <input type="text" name="q" value="<?= htmlspecialchars($q) ?>" class="form-control"
                                placeholder="Rechercher un produit...">

                        </div>

                    </div>


                    <div class="col-auto">

                        <button type="submit" class="btn btn-primary-custom">

                            <i class="bi bi-search me-1"></i>

                            Rechercher

                        </button>

                    </div>


                    <?php if (!empty($q)): ?>

                    <div class="col-auto">

                        <a href="index.php" class="btn btn-light border" title="Réinitialiser">

                            <i class="bi bi-x-lg"></i>

                        </a>

                    </div>

                    <?php endif; ?>

                </div>

            </form>

        </div>


        <!-- =====================================================
             MESSAGE SUCCÈS
        ====================================================== -->

        <?php if (!empty($success)): ?>

        <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">

            <i class="bi bi-check-circle me-2"></i>

            <?= htmlspecialchars($success) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             MESSAGE ERREUR
        ====================================================== -->

        <?php if (!empty($error)): ?>

        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

            <i class="bi bi-exclamation-triangle me-2"></i>

            <?= htmlspecialchars($error) ?>

            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

        <?php endif; ?>


        <!-- =====================================================
             TABLEAU
        ====================================================== -->

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

                            <td colspan="7">

                                <div class="empty-product">

                                    <i class="bi bi-box-seam"></i>

                                    <h5>
                                        Aucun produit
                                    </h5>

                                    <p>
                                        Aucun produit n'a encore été ajouté.
                                    </p>

                                    <a href="create.php" class="btn btn-primary-custom">

                                        <i class="bi bi-plus-lg me-1"></i>

                                        Ajouter le premier produit

                                    </a>

                                </div>

                            </td>

                        </tr>


                        <?php else: ?>


                        <?php foreach ($produits as $produit): ?>


                        <?php

                            $statut =
                                $produit['statut']
                                ?? 'disponible';


                            if ($statut === 'disponible') {

                                $badgeClass =
                                    'badge-disponible';

                                $badgeText =
                                    'Disponible';

                            } elseif (
                                $statut === 'indisponible'
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


                            $stock = (int)(
                                $produit['stock']
                                ?? 0
                            );

                            ?>


                        <tr>


                            <!-- ID -->

                            <td>

                                <span class="text-muted">

                                    #<?= (int)
                                            $produit['id_produit']
                                        ?>

                                </span>

                            </td>


                            <!-- PRODUIT -->

                            <td>

                                <div class="d-flex align-items-center gap-3">


                                    <?php if (
                                            !empty($produit['image'])
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
                                                    $produit['id_produit']
                                                ?>

                                        </small>

                                    </div>

                                </div>

                            </td>


                            <!-- CATÉGORIE -->

                            <td>

                                <?= htmlspecialchars(
                                        $produit['categorie_nom']
                                        ?? 'Sans catégorie'
                                    ) ?>

                            </td>


                            <!-- PRIX -->

                            <td>

                                <span class="product-price">

                                    <?= number_format(
                                            (float)$produit['prix'],
                                            0,
                                            ',',
                                            ' '
                                        ) ?>

                                    FCFA

                                </span>

                            </td>


                            <!-- STOCK -->

                            <td>

                                <?php if ($stock <= 5): ?>

                                <span class="stock-low">

                                    <i class="bi bi-exclamation-triangle me-1"></i>

                                    <?= $stock ?>

                                </span>

                                <?php else: ?>

                                <span class="stock-ok">

                                    <i class="bi bi-check-circle me-1"></i>

                                    <?= $stock ?>

                                </span>

                                <?php endif; ?>

                            </td>


                            <!-- STATUT -->

                            <td>

                                <span class="badge-status <?= $badgeClass ?>">

                                    <?= $badgeText ?>

                                </span>

                            </td>


                            <!-- ACTIONS -->

                            <td class="text-end">

                                <div class="d-flex justify-content-end gap-2">

                                    <a href="edit.php?id=<?= (int)$produit['id_produit'] ?>" class="action-btn btn-edit"
                                        title="Modifier">

                                        <i class="bi bi-pencil"></i>

                                    </a>


                                    <a href="delete.php?id=<?= (int)$produit['id_produit'] ?>"
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


        <!-- =====================================================
             PAGINATION
        ====================================================== -->

        <?php if ($total > $perPage): ?>

        <?php

            $pages = (int)ceil(
                $total / $perPage
            );

            ?>


        <nav class="d-flex justify-content-end mt-3" aria-label="Pagination">

            <ul class="pagination">


                <?php for (
                        $p = 1;
                        $p <= $pages;
                        $p++
                    ): ?>

                <li class="page-item
                            <?= $p === $page ? 'active' : '' ?>">

                    <a class="page-link" href="?q=<?= urlencode($q) ?>&page=<?= $p ?>">

                        <?= $p ?>

                    </a>

                </li>

                <?php endfor; ?>


            </ul>

        </nav>

        <?php endif; ?>


    </div>

</main>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>