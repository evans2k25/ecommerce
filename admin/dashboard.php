<?php

session_start();

require_once __DIR__ . "/../config/database.php";


/*
|--------------------------------------------------------------------------
| Vérification de la connexion administrateur
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin'])) {

    header("Location: login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Récupérer les informations de l'administrateur
|--------------------------------------------------------------------------
*/

$admin = $_SESSION['admin'];

$adminNom = htmlspecialchars(
    ($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')
);

$adminRole = htmlspecialchars(
    $admin['role'] ?? 'admin'
);


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();

    $db = $database->getConnection();

} catch (PDOException $e) {

    die(
        "Erreur de connexion à la base de données : "
        . htmlspecialchars($e->getMessage())
    );
}


/*
|--------------------------------------------------------------------------
| Statistiques
|--------------------------------------------------------------------------
*/

try {

    /*
    | Nombre de produits
    */

    $stmt = $db->query("
        SELECT COUNT(*) 
        FROM produits
    ");

    $totalProduits =
        (int) $stmt->fetchColumn();


    /*
    | Nombre de catégories
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM categories
    ");

    $totalCategories =
        (int) $stmt->fetchColumn();


    /*
    | Nombre de clients
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM clients
    ");

    $totalClients =
        (int) $stmt->fetchColumn();


    /*
    | Nombre de commandes
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
    ");

    $totalCommandes =
        (int) $stmt->fetchColumn();


    /*
    | Chiffre d'affaires
    |
    | On exclut les commandes annulées.
    */

    $stmt = $db->query("
        SELECT COALESCE(
            SUM(montant_total),
            0
        )
        FROM commandes
        WHERE statut != 'annulee'
    ");

    $chiffreAffaires =
        (float) $stmt->fetchColumn();


    /*
    | Commandes en attente
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
        WHERE statut = 'en_attente'
    ");

    $commandesAttente =
        (int) $stmt->fetchColumn();


    /*
    | Commandes livrées
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
        WHERE statut = 'livree'
    ");

    $commandesLivrees =
        (int) $stmt->fetchColumn();


    /*
    | Produits avec stock faible
    */

    $stmt = $db->query("
        SELECT COUNT(*)
        FROM produits
        WHERE stock <= 5
        AND statut = 'disponible'
    ");

    $stockFaible =
        (int) $stmt->fetchColumn();


} catch (PDOException $e) {

    die(
        "Erreur lors du chargement des statistiques : "
        . htmlspecialchars($e->getMessage())
    );
}


/*
|--------------------------------------------------------------------------
| Dernières commandes
|--------------------------------------------------------------------------
*/

try {

    $stmt = $db->query("
        SELECT
            c.id_commande,
            c.numero_commande,
            c.reference,
            c.montant_total,
            c.statut,
            c.date_commande,
            cl.nom,
            cl.prenom
        FROM commandes c

        INNER JOIN clients cl
            ON cl.id_client = c.id_client

        ORDER BY c.date_commande DESC

        LIMIT 8
    ");

    $dernieresCommandes =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $dernieresCommandes = [];
}


/*
|--------------------------------------------------------------------------
| Produits avec stock faible
|--------------------------------------------------------------------------
*/

try {

    $stmt = $db->query("
        SELECT
            p.id_produit,
            p.nom,
            p.stock,
            p.prix,
            c.nom AS categorie
        FROM produits p

        INNER JOIN categories c
            ON c.id_categorie = p.id_categorie

        WHERE p.stock <= 5
        AND p.statut = 'disponible'

        ORDER BY p.stock ASC

        LIMIT 8
    ");

    $produitsStockFaible =
        $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {

    $produitsStockFaible = [];
}


/*
|--------------------------------------------------------------------------
| Formatage du statut
|--------------------------------------------------------------------------
*/

function statutCommande($statut)
{

    $statuts = [

        'en_attente' => [
            'label' => 'En attente',
            'class' => 'warning'
        ],

        'confirmee' => [
            'label' => 'Confirmée',
            'class' => 'info'
        ],

        'preparee' => [
            'label' => 'Préparée',
            'class' => 'primary'
        ],

        'expediee' => [
            'label' => 'Expédiée',
            'class' => 'secondary'
        ],

        'livree' => [
            'label' => 'Livrée',
            'class' => 'success'
        ],

        'annulee' => [
            'label' => 'Annulée',
            'class' => 'danger'
        ]
    ];

    return $statuts[$statut]
        ?? [
            'label' => ucfirst($statut),
            'class' => 'secondary'
        ];
}


$pageTitle = "Tableau de bord";

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        <?= htmlspecialchars($pageTitle) ?> - E-Commerce
    </title>


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

        margin: 0;

        font-family: "Poppins", sans-serif;

        background: var(--light);

        color: var(--text);

    }


    /* Sidebar */

    .sidebar {

        position: fixed;

        left: 0;

        top: 0;

        width: 260px;

        height: 100vh;

        background: var(--dark);

        color: white;

        padding: 25px 15px;

        overflow-y: auto;

    }


    .brand {

        display: flex;

        align-items: center;

        gap: 10px;

        padding: 0 15px 30px;

        font-size: 22px;

        font-weight: 700;

        color: var(--primary);

    }


    .brand i {

        font-size: 28px;

    }


    .menu-title {

        color: #999;

        font-size: 11px;

        text-transform: uppercase;

        margin: 20px 15px 8px;

        font-weight: 600;

    }


    .sidebar a {

        display: flex;

        align-items: center;

        gap: 12px;

        text-decoration: none;

        color: #d5d5d5;

        padding: 12px 15px;

        border-radius: 10px;

        margin-bottom: 5px;

        transition: 0.3s;

    }


    .sidebar a:hover,
    .sidebar a.active {

        background: var(--primary);

        color: white;

    }


    .sidebar a i {

        font-size: 18px;

    }


    /* Main */

    .main {

        margin-left: 260px;

        min-height: 100vh;

    }


    /* Topbar */

    .topbar {

        height: 75px;

        background: white;

        display: flex;

        align-items: center;

        justify-content: space-between;

        padding: 0 30px;

        border-bottom: 1px solid #eee;

    }


    .topbar h1 {

        font-size: 22px;

        font-weight: 600;

        margin: 0;

    }


    .admin-info {

        display: flex;

        align-items: center;

        gap: 12px;

    }


    .admin-avatar {

        width: 42px;

        height: 42px;

        border-radius: 50%;

        background: var(--primary);

        color: white;

        display: flex;

        align-items: center;

        justify-content: center;

        font-weight: 600;

    }


    .admin-name {

        font-weight: 600;

        font-size: 14px;

    }


    .admin-role {

        font-size: 11px;

        color: #888;

    }


    /* Content */

    .content {

        padding: 30px;

    }


    .welcome {

        margin-bottom: 25px;

    }


    .welcome h2 {

        font-size: 25px;

        font-weight: 600;

        margin-bottom: 5px;

    }


    .welcome p {

        color: #777;

        margin: 0;

    }


    /* Cards statistiques */

    .stat-card {

        background: white;

        border-radius: 16px;

        padding: 22px;

        border: none;

        height: 100%;

        transition: 0.3s;

    }


    .stat-card:hover {

        transform: translateY(-4px);

        box-shadow:
            0 10px 30px rgba(237, 128, 233, 0.15);

    }


    .stat-icon {

        width: 50px;

        height: 50px;

        border-radius: 13px;

        background: rgba(237, 128, 233, 0.12);

        color: var(--primary);

        display: flex;

        align-items: center;

        justify-content: center;

        font-size: 22px;

        margin-bottom: 15px;

    }


    .stat-title {

        color: #888;

        font-size: 13px;

        margin-bottom: 5px;

    }


    .stat-value {

        font-size: 25px;

        font-weight: 700;

        color: var(--dark);

    }


    /* Sections */

    .dashboard-card {

        background: white;

        border-radius: 16px;

        padding: 22px;

        border: none;

        height: 100%;

    }


    .section-title {

        display: flex;

        align-items: center;

        justify-content: space-between;

        margin-bottom: 20px;

    }


    .section-title h5 {

        margin: 0;

        font-weight: 600;

    }


    .section-title a {

        color: var(--primary);

        text-decoration: none;

        font-size: 13px;

    }


    .section-title a:hover {

        color: var(--primary-dark);

    }


    /* Table */

    .table {

        margin-bottom: 0;

        vertical-align: middle;

    }


    .table th {

        font-size: 12px;

        color: #888;

        font-weight: 500;

        border-bottom: 1px solid #eee;

    }


    .table td {

        font-size: 13px;

        border-bottom: 1px solid #f2f2f2;

    }


    .order-number {

        color: var(--primary-dark);

        font-weight: 600;

    }


    /* Badge */

    .badge {

        padding: 7px 10px;

        border-radius: 7px;

        font-size: 11px;

    }


    /* Stock */

    .stock-danger {

        color: #dc3545;

        font-weight: 600;

    }


    .stock-warning {

        color: #d99a00;

        font-weight: 600;

    }


    /* Responsive */

    @media (max-width: 992px) {

        .sidebar {

            width: 220px;

        }

        .main {

            margin-left: 220px;

        }

    }


    @media (max-width: 768px) {

        .sidebar {

            position: relative;

            width: 100%;

            height: auto;

        }

        .main {

            margin-left: 0;

        }

        .topbar {

            padding: 0 15px;

        }

        .content {

            padding: 20px 15px;

        }

    }
    </style>

</head>


<body>


    <!-- ========================================================= -->
    <!-- SIDEBAR -->
    <!-- ========================================================= -->

    <aside class="sidebar">


        <div class="brand">

            <i class="bi bi-shop"></i>

            E-Commerce

        </div>


        <div class="menu-title">
            Général
        </div>


        <a href="dashboard.php" class="active">

            <i class="bi bi-speedometer2"></i>

            Tableau de bord

        </a>


        <div class="menu-title">
            Catalogue
        </div>


        <a href="products/index.php">

            <i class="bi bi-box-seam"></i>

            Produits

        </a>


        <a href="categories/index.php">

            <i class="bi bi-grid"></i>

            Catégories

        </a>


        <div class="menu-title">
            Ventes
        </div>


        <a href="orders/index.php">

            <i class="bi bi-cart-check"></i>

            Commandes

        </a>


        <div class="menu-title">
            Administration
        </div>


        <a href="admins/index.php">

            <i class="bi bi-people"></i>

            Administrateurs

        </a>


        <a href="../index.php" target="_blank">

            <i class="bi bi-shop-window"></i>

            Voir la boutique

        </a>


        <a href="logout.php">

            <i class="bi bi-box-arrow-right"></i>

            Déconnexion

        </a>

    </aside>



    <!-- ========================================================= -->
    <!-- MAIN -->
    <!-- ========================================================= -->

    <main class="main">


        <!-- TOPBAR -->

        <header class="topbar">


            <h1>
                Tableau de bord
            </h1>


            <div class="admin-info">


                <div>

                    <div class="admin-name">

                        <?= $adminNom ?>

                    </div>

                    <div class="admin-role">

                        <?= $adminRole ?>

                    </div>

                </div>


                <div class="admin-avatar">

                    <?= strtoupper(
                    substr(
                        $admin['prenom'] ?? 'A',
                        0,
                        1
                    )
                ) ?>

                </div>

            </div>

        </header>



        <!-- CONTENT -->

        <div class="content">


            <!-- WELCOME -->

            <div class="welcome">

                <h2>
                    Bonjour <?= htmlspecialchars($admin['prenom'] ?? 'Administrateur') ?> 👋
                </h2>

                <p>
                    Voici un aperçu de votre boutique aujourd'hui.
                </p>

            </div>



            <!-- ================================================= -->
            <!-- STATISTIQUES -->
            <!-- ================================================= -->

            <div class="row g-4 mb-4">


                <!-- Produits -->

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-box-seam"></i>

                        </div>

                        <div class="stat-title">
                            Produits
                        </div>

                        <div class="stat-value">
                            <?= number_format($totalProduits, 0, ',', ' ') ?>
                        </div>

                    </div>

                </div>



                <!-- Catégories -->

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-grid"></i>

                        </div>

                        <div class="stat-title">
                            Catégories
                        </div>

                        <div class="stat-value">
                            <?= number_format($totalCategories, 0, ',', ' ') ?>
                        </div>

                    </div>

                </div>



                <!-- Clients -->

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-person"></i>

                        </div>

                        <div class="stat-title">
                            Clients
                        </div>

                        <div class="stat-value">
                            <?= number_format($totalClients, 0, ',', ' ') ?>
                        </div>

                    </div>

                </div>



                <!-- Commandes -->

                <div class="col-xl-3 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-cart-check"></i>

                        </div>

                        <div class="stat-title">
                            Commandes
                        </div>

                        <div class="stat-value">
                            <?= number_format($totalCommandes, 0, ',', ' ') ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- ================================================= -->
            <!-- CHIFFRE AFFAIRES / COMMANDES -->
            <!-- ================================================= -->

            <div class="row g-4 mb-4">


                <!-- CA -->

                <div class="col-xl-4 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-currency-exchange"></i>

                        </div>

                        <div class="stat-title">
                            Chiffre d'affaires
                        </div>

                        <div class="stat-value">

                            <?= number_format(
                            $chiffreAffaires,
                            0,
                            ',',
                            ' '
                        ) ?>

                            FCFA

                        </div>

                    </div>

                </div>



                <!-- Commandes attente -->

                <div class="col-xl-4 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-hourglass-split"></i>

                        </div>

                        <div class="stat-title">
                            Commandes en attente
                        </div>

                        <div class="stat-value">
                            <?= $commandesAttente ?>
                        </div>

                    </div>

                </div>



                <!-- Livrées -->

                <div class="col-xl-4 col-md-6">

                    <div class="stat-card">

                        <div class="stat-icon">

                            <i class="bi bi-check-circle"></i>

                        </div>

                        <div class="stat-title">
                            Commandes livrées
                        </div>

                        <div class="stat-value">
                            <?= $commandesLivrees ?>
                        </div>

                    </div>

                </div>

            </div>



            <!-- ================================================= -->
            <!-- COMMANDES + STOCK -->
            <!-- ================================================= -->

            <div class="row g-4">


                <!-- DERNIÈRES COMMANDES -->

                <div class="col-xl-8">

                    <div class="dashboard-card">


                        <div class="section-title">

                            <h5>
                                Dernières commandes
                            </h5>

                            <a href="orders/index.php">
                                Voir toutes
                            </a>

                        </div>


                        <div class="table-responsive">

                            <table class="table">

                                <thead>

                                    <tr>

                                        <th>
                                            Commande
                                        </th>

                                        <th>
                                            Client
                                        </th>

                                        <th>
                                            Montant
                                        </th>

                                        <th>
                                            Statut
                                        </th>

                                        <th>
                                            Date
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>


                                    <?php if (empty($dernieresCommandes)): ?>

                                    <tr>

                                        <td colspan="5" class="text-center text-muted py-4">

                                            Aucune commande.

                                        </td>

                                    </tr>

                                    <?php else: ?>


                                    <?php foreach (
                                    $dernieresCommandes
                                    as $commande
                                ): ?>


                                    <?php

                                    $status =
                                        statutCommande(
                                            $commande['statut']
                                        );

                                    ?>


                                    <tr>


                                        <td>

                                            <span class="order-number">

                                                #
                                                <?= htmlspecialchars(
                                                    $commande['numero_commande']
                                                ) ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $commande['prenom']
                                                . ' '
                                                . $commande['nom']
                                            ) ?>

                                        </td>


                                        <td>

                                            <strong>

                                                <?= number_format(
                                                    (float) $commande['montant_total'],
                                                    0,
                                                    ',',
                                                    ' '
                                                ) ?>

                                                FCFA

                                            </strong>

                                        </td>


                                        <td>

                                            <span class="badge text-bg-<?= $status['class'] ?>">

                                                <?= $status['label'] ?>

                                            </span>

                                        </td>


                                        <td>

                                            <?= date(
                                                'd/m/Y H:i',
                                                strtotime(
                                                    $commande['date_commande']
                                                )
                                            ) ?>

                                        </td>


                                    </tr>


                                    <?php endforeach; ?>


                                    <?php endif; ?>


                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>



                <!-- STOCK FAIBLE -->

                <div class="col-xl-4">

                    <div class="dashboard-card">


                        <div class="section-title">

                            <h5>
                                Stock faible
                            </h5>

                            <a href="products/index.php">
                                Voir les produits
                            </a>

                        </div>


                        <?php if (empty($produitsStockFaible)): ?>


                        <div class="text-center py-4">

                            <i class="bi bi-check-circle" style="
                                    font-size: 35px;
                                    color: #198754;
                                "></i>

                            <p class="mt-2 mb-0 text-muted">

                                Aucun produit en stock faible.

                            </p>

                        </div>


                        <?php else: ?>


                        <?php foreach (
                            $produitsStockFaible
                            as $produit
                        ): ?>


                        <div class="d-flex justify-content-between align-items-center border-bottom py-3">


                            <div>

                                <div class="fw-semibold" style="font-size: 13px;">

                                    <?= htmlspecialchars(
                                            $produit['nom']
                                        ) ?>

                                </div>


                                <small class="text-muted">

                                    <?= htmlspecialchars(
                                            $produit['categorie']
                                        ) ?>

                                </small>

                            </div>


                            <div class="<?=
                                        $produit['stock'] <= 2
                                            ? 'stock-danger'
                                            : 'stock-warning'
                                    ?>">

                                <?= (int) $produit['stock'] ?>

                                unité(s)

                            </div>


                        </div>


                        <?php endforeach; ?>


                        <?php endif; ?>


                    </div>

                </div>


            </div>


        </div>

    </main>



    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


</body>

</html>