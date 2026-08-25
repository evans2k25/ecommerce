<?php

require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/../config/database.php';

/*
|--------------------------------------------------------------------------
| Configuration de la page
|--------------------------------------------------------------------------
*/

$pageTitle = 'Tableau de bord';
$adminPage = 'dashboard';


/*
|--------------------------------------------------------------------------
| Variables par défaut
|--------------------------------------------------------------------------
*/

$totalProduits = 0;
$totalCategories = 0;
$totalClients = 0;
$totalCommandes = 0;

$chiffreAffaires = 0;

$commandesAttente = 0;
$commandesLivrees = 0;

$stockFaible = 0;

$dernieresCommandes = [];
$produitsStockFaible = [];

$error = '';


/*
|--------------------------------------------------------------------------
| Connexion base de données
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();
    $db = $database->getConnection();


    /*
    |--------------------------------------------------------------------------
    | STATISTIQUES
    |--------------------------------------------------------------------------
    */


    // Produits disponibles
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM produits
        WHERE statut = 'disponible'
    ");

    $totalProduits = (int) $stmt->fetchColumn();


    // Catégories
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM categories
    ");

    $totalCategories = (int) $stmt->fetchColumn();


    // Clients
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM clients
    ");

    $totalClients = (int) $stmt->fetchColumn();


    // Commandes
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
    ");

    $totalCommandes = (int) $stmt->fetchColumn();


    // Chiffre d'affaires
    $stmt = $db->query("
        SELECT COALESCE(SUM(montant_total), 0)
        FROM commandes
        WHERE statut != 'annulee'
    ");

    $chiffreAffaires = (float) $stmt->fetchColumn();


    // Commandes en attente
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
        WHERE statut = 'en_attente'
    ");

    $commandesAttente = (int) $stmt->fetchColumn();


    // Commandes livrées
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM commandes
        WHERE statut = 'livree'
    ");

    $commandesLivrees = (int) $stmt->fetchColumn();


    // Produits en stock faible
    $stmt = $db->query("
        SELECT COUNT(*)
        FROM produits
        WHERE stock <= 5
        AND statut = 'disponible'
    ");

    $stockFaible = (int) $stmt->fetchColumn();


    /*
    |--------------------------------------------------------------------------
    | DERNIÈRES COMMANDES
    |--------------------------------------------------------------------------
    */

    $stmt = $db->query("
        SELECT
            c.id_commande,
            c.numero_commande,
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

    $dernieresCommandes = $stmt->fetchAll(PDO::FETCH_ASSOC);


    /*
    |--------------------------------------------------------------------------
    | PRODUITS EN STOCK FAIBLE
    |--------------------------------------------------------------------------
    */

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

    $produitsStockFaible = $stmt->fetchAll(PDO::FETCH_ASSOC);


} catch (PDOException $e) {

    error_log(
        "admin/dashboard.php statistics error: "
        . $e->getMessage()
    );

    $error = "Impossible de charger les statistiques.";

}


/*
|--------------------------------------------------------------------------
| Fonction statut commande
|--------------------------------------------------------------------------
*/

function statutCommande(string $statut): array
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


/*
|--------------------------------------------------------------------------
| Header
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/includes/header.php';

?>


<style>
/*
|--------------------------------------------------------------------------
| VARIABLES
|--------------------------------------------------------------------------
*/

:root {

    --primary: #ED80E9;
    --primary-dark: #C95BC5;
    --primary-light: #F8D9F7;

    --dark: #1F1F29;
    --text: #555;

    --light: #F8F8FA;
    --white: #FFFFFF;

    --success: #198754;
    --warning: #ffc107;
    --danger: #dc3545;
    --info: #0dcaf0;

}


/*
|--------------------------------------------------------------------------
| BODY
|--------------------------------------------------------------------------
*/

body {

    background-color: var(--light);
    color: var(--text);

}


/*
|--------------------------------------------------------------------------
| WELCOME
|--------------------------------------------------------------------------
*/

.welcome-box {

    position: relative;

    overflow: hidden;

    background:
        linear-gradient(135deg,
            var(--primary),
            var(--primary-dark));

    color: white;

    border-radius: 20px;

    padding: 28px 30px;

    margin-bottom: 30px;

    box-shadow:
        0 10px 30px rgba(201, 91, 197, .20);

}


.welcome-box::after {

    content: "";

    position: absolute;

    width: 180px;
    height: 180px;

    border-radius: 50%;

    background:
        rgba(255, 255, 255, .10);

    right: -50px;
    top: -70px;

}


.welcome-box::before {

    content: "";

    position: absolute;

    width: 120px;
    height: 120px;

    border-radius: 50%;

    background:
        rgba(255, 255, 255, .08);

    right: 100px;
    bottom: -70px;

}


.welcome-box h2 {

    position: relative;

    z-index: 2;

    font-weight: 700;

    margin-bottom: 7px;

    font-size: 25px;

}


.welcome-box p {

    position: relative;

    z-index: 2;

    margin: 0;

    opacity: .9;

}


/*
|--------------------------------------------------------------------------
| ALERT
|--------------------------------------------------------------------------
*/

.dashboard-alert {

    border: none;

    border-radius: 12px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .05);

}


/*
|--------------------------------------------------------------------------
| STAT CARDS
|--------------------------------------------------------------------------
*/

.stat-card {

    position: relative;

    background: var(--white);

    border:
        1px solid rgba(0, 0, 0, .04);

    border-radius: 18px;

    padding: 22px;

    height: 100%;

    transition:
        all .3s ease;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .05);

    overflow: hidden;

}


.stat-card::after {

    content: "";

    position: absolute;

    width: 80px;
    height: 80px;

    background:
        var(--primary-light);

    border-radius: 50%;

    right: -30px;
    bottom: -30px;

    opacity: .5;

}


.stat-card:hover {

    transform:
        translateY(-5px);

    box-shadow:
        0 12px 30px rgba(0, 0, 0, .10);

    border-color:
        rgba(237, 128, 233, .25);

}


.stat-icon {

    width: 52px;
    height: 52px;

    border-radius: 15px;

    background:
        var(--primary-light);

    color:
        var(--primary-dark);

    display: flex;

    align-items: center;
    justify-content: center;

    font-size: 23px;

    margin-bottom: 17px;

}


.stat-title {

    color: #777;

    font-size: 14px;

    font-weight: 500;

    margin-bottom: 5px;

}


.stat-value {

    color: var(--dark);

    font-size: 26px;

    font-weight: 700;

    line-height: 1.2;

}


/*
|--------------------------------------------------------------------------
| DASHBOARD CARD
|--------------------------------------------------------------------------
*/

.dashboard-card {

    background: var(--white);

    border-radius: 18px;

    padding: 22px;

    border:
        1px solid rgba(0, 0, 0, .04);

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .05);

    height: 100%;

}


/*
|--------------------------------------------------------------------------
| SECTION TITLE
|--------------------------------------------------------------------------
*/

.section-title {

    display: flex;

    justify-content:
        space-between;

    align-items: center;

    margin-bottom: 18px;

    padding-bottom: 15px;

    border-bottom:
        1px solid #eee;

}


.section-title h5 {

    margin: 0;

    color:
        var(--dark);

    font-size: 17px;

    font-weight: 700;

}


.section-title a {

    text-decoration: none;

    color:
        var(--primary-dark);

    font-size: 13px;

    font-weight: 600;

}


.section-title a:hover {

    color:
        var(--primary);

}


/*
|--------------------------------------------------------------------------
| TABLE
|--------------------------------------------------------------------------
*/

.dashboard-card .table {

    margin-bottom: 0;

    vertical-align: middle;

}


.dashboard-card .table thead th {

    background:
        #fafafa;

    color:
        #777;

    font-size: 12px;

    font-weight: 600;

    text-transform:
        uppercase;

    letter-spacing:
        .3px;

    border-bottom:
        1px solid #eee;

    padding:
        13px 12px;

    white-space:
        nowrap;

}


.dashboard-card .table tbody td {

    padding:
        15px 12px;

    font-size: 13px;

    color:
        #555;

    border-bottom:
        1px solid #f1f1f1;

}


.dashboard-card .table tbody tr {

    transition:
        .2s;

}


.dashboard-card .table tbody tr:hover {

    background:
        #fff8ff;

}


.dashboard-card .table tbody tr:last-child td {

    border-bottom:
        none;

}


/*
|--------------------------------------------------------------------------
| ORDER NUMBER
|--------------------------------------------------------------------------
*/

.order-number {

    color:
        var(--primary-dark);

    background:
        var(--primary-light);

    padding:
        5px 9px;

    border-radius:
        7px;

    font-size:
        12px;

    font-weight:
        600;

}


/*
|--------------------------------------------------------------------------
| BADGES
|--------------------------------------------------------------------------
*/

.badge {

    border-radius:
        7px;

    padding:
        6px 9px;

    font-size:
        11px;

    font-weight:
        600;

}


/*
|--------------------------------------------------------------------------
| STOCK
|--------------------------------------------------------------------------
*/

.stock-item {

    padding:
        14px 0;

    border-bottom:
        1px solid #eee;

}


.stock-item:last-child {

    border-bottom:
        none;

}


.stock-name {

    font-size:
        13px;

    font-weight:
        600;

    color:
        var(--dark);

}


.stock-category {

    font-size:
        11px;

    color:
        #999;

    margin-top:
        3px;

}


.stock-warning,
.stock-danger {

    padding:
        5px 9px;

    border-radius:
        7px;

    font-size:
        11px;

    font-weight:
        700;

    white-space:
        nowrap;

}


.stock-warning {

    color:
        #856404;

    background:
        #fff3cd;

}


.stock-danger {

    color:
        #842029;

    background:
        #f8d7da;

}


/*
|--------------------------------------------------------------------------
| EMPTY STATE
|--------------------------------------------------------------------------
*/

.empty-state {

    text-align:
        center;

    padding:
        35px 15px;

    color:
        #999;

}


.empty-state i {

    font-size:
        42px;

    color:
        var(--success);

    display:
        block;

    margin-bottom:
        10px;

}


.empty-state p {

    margin:
        0;

    font-size:
        13px;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .welcome-box {

        padding:
            22px;

    }

    .welcome-box h2 {

        font-size:
            21px;

    }

    .stat-value {

        font-size:
            23px;

    }

    .dashboard-card {

        padding:
            16px;

    }

    .section-title h5 {

        font-size:
            15px;

    }

    .section-title a {

        font-size:
            12px;

    }

}
</style>


<!-- =========================================================
     MESSAGE ERREUR
========================================================= -->

<?php if (!empty($error)): ?>

<div class="alert alert-danger dashboard-alert mb-4">

    <i class="bi bi-exclamation-triangle me-2"></i>

    <?= htmlspecialchars($error) ?>

</div>

<?php endif; ?>


<!-- =========================================================
     BIENVENUE
========================================================= -->

<div class="welcome-box">

    <h2>

        Bonjour
        <?= htmlspecialchars(
            $admin['prenom'] ?? 'Administrateur'
        ) ?>

        👋

    </h2>

    <p>

        Voici un aperçu de votre boutique aujourd'hui.

    </p>

</div>


<!-- =========================================================
     STATISTIQUES PRINCIPALES
========================================================= -->

<div class="row g-4 mb-4">


    <!-- PRODUITS -->

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-box-seam"></i>

            </div>

            <div class="stat-title">

                Produits disponibles

            </div>

            <div class="stat-value">

                <?= number_format(
                    $totalProduits,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>


    <!-- CATEGORIES -->

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-grid"></i>

            </div>

            <div class="stat-title">

                Catégories

            </div>

            <div class="stat-value">

                <?= number_format(
                    $totalCategories,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>


    <!-- CLIENTS -->

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-people"></i>

            </div>

            <div class="stat-title">

                Clients

            </div>

            <div class="stat-value">

                <?= number_format(
                    $totalClients,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>


    <!-- COMMANDES -->

    <div class="col-xl-3 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-cart-check"></i>

            </div>

            <div class="stat-title">

                Commandes

            </div>

            <div class="stat-value">

                <?= number_format(
                    $totalCommandes,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     STATISTIQUES SECONDAIRES
========================================================= -->

<div class="row g-4 mb-4">


    <!-- CHIFFRE AFFAIRES -->

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

                <small style="
                        font-size:14px;
                        font-weight:600;
                    ">

                    FCFA

                </small>

            </div>

        </div>

    </div>


    <!-- COMMANDES ATTENTE -->

    <div class="col-xl-4 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-hourglass-split"></i>

            </div>

            <div class="stat-title">

                Commandes en attente

            </div>

            <div class="stat-value">

                <?= number_format(
                    $commandesAttente,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>


    <!-- COMMANDES LIVREES -->

    <div class="col-xl-4 col-md-6">

        <div class="stat-card">

            <div class="stat-icon">

                <i class="bi bi-check-circle"></i>

            </div>

            <div class="stat-title">

                Commandes livrées

            </div>

            <div class="stat-value">

                <?= number_format(
                    $commandesLivrees,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>

</div>


<!-- =========================================================
     COMMANDES + STOCK
========================================================= -->

<div class="row g-4">


    <!-- =====================================================
         DERNIERES COMMANDES
    ====================================================== -->

    <div class="col-xl-8">

        <div class="dashboard-card">

            <div class="section-title">

                <h5>

                    <i class="bi bi-receipt me-2" style="color:#ED80E9;"></i>

                    Dernières commandes

                </h5>


                <a href="orders/index.php">

                    Voir toutes

                    <i class="bi bi-arrow-right ms-1"></i>

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

                        <?php if (
                            empty($dernieresCommandes)
                        ): ?>

                        <tr>

                            <td colspan="5">

                                <div class="empty-state">

                                    <i class="bi bi-inbox"></i>

                                    <p>

                                        Aucune commande disponible.

                                    </p>

                                </div>

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
                                        $commande[
                                            'numero_commande'
                                        ]
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <div class="fw-semibold">

                                    <?= htmlspecialchars(
                                        $commande[
                                            'prenom'
                                        ]
                                        . ' '
                                        .
                                        $commande[
                                            'nom'
                                        ]
                                    ) ?>

                                </div>

                            </td>


                            <td>

                                <strong>

                                    <?= number_format(
                                        (float)
                                        $commande[
                                            'montant_total'
                                        ],
                                        0,
                                        ',',
                                        ' '
                                    ) ?>

                                    FCFA

                                </strong>

                            </td>


                            <td>

                                <span class="
                                        badge
                                        text-bg-<?=
                                        $status['class']
                                    ?>">

                                    <?= htmlspecialchars(
                                        $status['label']
                                    ) ?>

                                </span>

                            </td>


                            <td>

                                <small class="text-muted">

                                    <?= date(
                                        'd/m/Y H:i',
                                        strtotime(
                                            $commande[
                                                'date_commande'
                                            ]
                                        )
                                    ) ?>

                                </small>

                            </td>


                        </tr>


                        <?php endforeach; ?>

                        <?php endif; ?>

                    </tbody>

                </table>

            </div>

        </div>

    </div>


    <!-- =====================================================
         STOCK FAIBLE
    ====================================================== -->

    <div class="col-xl-4">

        <div class="dashboard-card">

            <div class="section-title">

                <h5>

                    <i class="
                            bi
                            bi-exclamation-triangle
                            me-2
                            text-warning
                        "></i>

                    Stock faible

                </h5>


                <a href="products/index.php">

                    Voir les produits

                    <i class="bi bi-arrow-right ms-1"></i>

                </a>

            </div>


            <?php if (
                empty($produitsStockFaible)
            ): ?>


            <div class="empty-state">

                <i class="bi bi-check-circle"></i>

                <p>

                    Aucun produit en stock faible.

                </p>

            </div>


            <?php else: ?>


            <?php foreach (
                $produitsStockFaible
                as $produit
            ): ?>


            <div class="stock-item">

                <div class="
                        d-flex
                        justify-content-between
                        align-items-center
                        gap-3
                    ">

                    <div class="flex-grow-1">

                        <div class="stock-name">

                            <?= htmlspecialchars(
                                $produit['nom']
                            ) ?>

                        </div>


                        <div class="stock-category">

                            <i class="
                                    bi
                                    bi-tag
                                    me-1
                                "></i>

                            <?= htmlspecialchars(
                                $produit[
                                    'categorie'
                                ]
                            ) ?>

                        </div>

                    </div>


                    <div class="<?=
                            $produit['stock'] <= 2
                            ? 'stock-danger'
                            : 'stock-warning'
                        ?>">

                        <i class="
                                bi
                                bi-box
                                me-1
                            "></i>

                        <?= (int)
                            $produit['stock']
                        ?>

                    </div>

                </div>

            </div>


            <?php endforeach; ?>


            <?php endif; ?>

        </div>

    </div>

</div>


<?php require_once __DIR__ . '/includes/footer.php'; ?>