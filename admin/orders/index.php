<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Commande.php";


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

$database = new Database();
$db = $database->getConnection();

$commandeModel = new Commande($db);

$error = '';



/*
|--------------------------------------------------------------------------
| Récupération des commandes
|--------------------------------------------------------------------------
*/

try {

    $commandes = $commandeModel->getAll();

} catch (Throwable $e) {

    $commandes = [];

    $error = $e->getMessage();

}



/*
|--------------------------------------------------------------------------
| Statut commande
|--------------------------------------------------------------------------
*/

function statutCommandeLabel(string $statut): array
{
    $statuts = [

        'en_attente' => [
            'En attente',
            'warning'
        ],

        'confirmee' => [
            'Confirmée',
            'info'
        ],

        'preparee' => [
            'Préparée',
            'primary'
        ],

        'expediee' => [
            'Expédiée',
            'secondary'
        ],

        'livree' => [
            'Livrée',
            'success'
        ],

        'annulee' => [
            'Annulée',
            'danger'
        ]

    ];

    return $statuts[$statut]
        ?? [ucfirst($statut), 'secondary'];
}



/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Commandes";

$adminPage = "orders";


require_once __DIR__ . "/../includes/header.php";

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
    --info: #0dcaf0;

}



/* =========================================================
       BODY
    ========================================================= */

body {

    background-color: var(--light);

    color: var(--text);

}



/* =========================================================
       EN-TÊTE
    ========================================================= */

.page-header {

    display: flex;

    justify-content: space-between;

    align-items: center;

    margin-bottom: 25px;

}



.page-title {

    color: var(--dark);

    font-size: 27px;

    font-weight: 700;

    margin: 0;

}



.page-title i {

    color: var(--primary);

    margin-right: 8px;

}



.page-subtitle {

    color: #888;

    font-size: 14px;

    margin-top: 6px;

    margin-bottom: 0;

}



/* =========================================================
       STATISTIQUES RAPIDES
    ========================================================= */

.order-stat-card {

    background-color: white;

    border-radius: 16px;

    border: 1px solid rgba(0, 0, 0, .04);

    padding: 18px;

    height: 100%;

    box-shadow: 0 5px 20px rgba(0, 0, 0, .04);

    transition: all .25s ease;

}



.order-stat-card:hover {

    transform: translateY(-3px);

    box-shadow: 0 10px 25px rgba(0, 0, 0, .08);

}



.order-stat-icon {

    width: 45px;

    height: 45px;

    border-radius: 12px;

    display: flex;

    align-items: center;

    justify-content: center;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 20px;

    margin-bottom: 12px;

}



.order-stat-title {

    color: #888;

    font-size: 12px;

    margin-bottom: 4px;

}



.order-stat-value {

    color: var(--dark);

    font-size: 22px;

    font-weight: 700;

}



/* =========================================================
       CARTE
    ========================================================= */

.admin-card {

    background-color: white;

    border-radius: 18px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow: 0 5px 25px rgba(0, 0, 0, .05);

    overflow: hidden;

}



/* =========================================================
       TABLEAU
    ========================================================= */

.orders-table {

    margin-bottom: 0;

    vertical-align: middle;

}



.orders-table thead th {

    background-color: #fafafa;

    color: #777;

    border-bottom: 1px solid #eee;

    font-size: 11px;

    font-weight: 700;

    text-transform: uppercase;

    letter-spacing: .4px;

    padding: 16px 18px;

    white-space: nowrap;

}



.orders-table tbody td {

    padding: 16px 18px;

    border-bottom: 1px solid #f1f1f1;

    font-size: 13px;

    color: #555;

}



.orders-table tbody tr {

    transition: all .2s ease;

}



.orders-table tbody tr:hover {

    background-color: #fff8ff;

}



.orders-table tbody tr:last-child td {

    border-bottom: none;

}



/* =========================================================
       NUMÉRO COMMANDE
    ========================================================= */

.order-number {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 8px;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 12px;

    font-weight: 700;

    white-space: nowrap;

}



/* =========================================================
       CLIENT
    ========================================================= */

.client-info {

    display: flex;

    align-items: center;

    gap: 10px;

}



.client-avatar {

    width: 38px;

    height: 38px;

    min-width: 38px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 16px;

}



.client-name {

    color: var(--dark);

    font-size: 13px;

    font-weight: 600;

}



/* =========================================================
       MONTANT
    ========================================================= */

.order-amount {

    color: var(--primary-dark);

    font-size: 13px;

    font-weight: 700;

    white-space: nowrap;

}



/* =========================================================
       BADGES STATUT
    ========================================================= */

.order-status {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 700;

    white-space: nowrap;

}



.status-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background-color: currentColor;

}



.status-warning {

    background-color: #fff3cd;

    color: #856404;

}



.status-info {

    background-color: #cff4fc;

    color: #087990;

}



.status-primary {

    background-color: var(--primary-light);

    color: var(--primary-dark);

}



.status-secondary {

    background-color: #e2e3e5;

    color: #41464b;

}



.status-success {

    background-color: #d1e7dd;

    color: #146c43;

}



.status-danger {

    background-color: #f8d7da;

    color: #842029;

}



/* =========================================================
       DATE
    ========================================================= */

.order-date {

    color: #777;

    font-size: 12px;

    white-space: nowrap;

}



/* =========================================================
       ACTION
    ========================================================= */

.action-btn {

    width: 37px;

    height: 37px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    text-decoration: none;

    transition: all .25s ease;

}



.btn-view {

    background-color: #f1e8ff;

    color: #6f42c1;

}



.btn-view:hover {

    background-color: #6f42c1;

    color: white;

    transform: translateY(-2px);

}



/* =========================================================
       ALERTE
    ========================================================= */

.alert {

    border: none;

    border-radius: 12px;

    padding: 14px 17px;

    font-size: 13px;

    box-shadow: 0 4px 15px rgba(0, 0, 0, .04);

}



/* =========================================================
       ÉTAT VIDE
    ========================================================= */

.empty-orders {

    padding: 65px 20px;

    text-align: center;

}



.empty-orders-icon {

    width: 70px;

    height: 70px;

    margin: 0 auto 18px;

    border-radius: 18px;

    display: flex;

    align-items: center;

    justify-content: center;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 30px;

}



.empty-orders h5 {

    color: var(--dark);

    font-weight: 700;

    margin-bottom: 7px;

}



.empty-orders p {

    color: #999;

    font-size: 13px;

    margin: 0;

}



/* =========================================================
       RESPONSIVE
    ========================================================= */

@media (max-width: 768px) {

    .page-header {

        align-items: flex-start;

    }



    .page-title {

        font-size: 22px;

    }



    .page-subtitle {

        font-size: 12px;

    }



    .admin-card {

        border-radius: 14px;

    }



    .orders-table thead th,
    .orders-table tbody td {

        padding: 12px 10px;

    }



    .client-avatar {

        width: 34px;

        height: 34px;

        min-width: 34px;

    }

}
</style>



<!-- =========================================================
     EN-TÊTE
========================================================= -->

<div class="page-header">

    <div>

        <h1 class="page-title">

            <i class="bi bi-cart-check"></i>

            Commandes

        </h1>

        <p class="page-subtitle">

            Consultez et gérez les commandes de votre boutique.

        </p>

    </div>

</div>



<!-- =========================================================
     ERREUR
========================================================= -->

<?php if (!empty($error)): ?>

<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

    <i class="bi bi-exclamation-triangle me-2"></i>

    <?= htmlspecialchars($error) ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>



<!-- =========================================================
     STATISTIQUES COMMANDES
========================================================= -->

<?php

$totalCommandes = count($commandes);

$commandesAttente = 0;

$commandesConfirmees = 0;

$commandesLivrees = 0;

$chiffreAffaires = 0;


foreach ($commandes as $commande) {

    $statut = $commande['statut'] ?? '';

    $chiffreAffaires +=
        (float)($commande['montant_total'] ?? 0);


    if ($statut === 'en_attente') {

        $commandesAttente++;

    }


    if ($statut === 'confirmee') {

        $commandesConfirmees++;

    }


    if ($statut === 'livree') {

        $commandesLivrees++;

    }

}

?>


<div class="row g-4 mb-4">


    <!-- TOTAL -->

    <div class="col-xl-3 col-md-6">

        <div class="order-stat-card">

            <div class="order-stat-icon">

                <i class="bi bi-cart-check"></i>

            </div>

            <div class="order-stat-title">

                Total commandes

            </div>

            <div class="order-stat-value">

                <?= number_format(
                    $totalCommandes,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>



    <!-- EN ATTENTE -->

    <div class="col-xl-3 col-md-6">

        <div class="order-stat-card">

            <div class="order-stat-icon">

                <i class="bi bi-hourglass-split"></i>

            </div>

            <div class="order-stat-title">

                En attente

            </div>

            <div class="order-stat-value">

                <?= number_format(
                    $commandesAttente,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>



    <!-- CONFIRMÉES -->

    <div class="col-xl-3 col-md-6">

        <div class="order-stat-card">

            <div class="order-stat-icon">

                <i class="bi bi-check2-circle"></i>

            </div>

            <div class="order-stat-title">

                Confirmées

            </div>

            <div class="order-stat-value">

                <?= number_format(
                    $commandesConfirmees,
                    0,
                    ',',
                    ' '
                ) ?>

            </div>

        </div>

    </div>



    <!-- LIVRÉES -->

    <div class="col-xl-3 col-md-6">

        <div class="order-stat-card">

            <div class="order-stat-icon">

                <i class="bi bi-truck"></i>

            </div>

            <div class="order-stat-title">

                Livrées

            </div>

            <div class="order-stat-value">

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
     TABLEAU COMMANDES
========================================================= -->

<div class="admin-card">

    <div class="table-responsive">

        <table class="table orders-table align-middle">

            <thead>

                <tr>

                    <th>N° commande</th>

                    <th>Client</th>

                    <th>Montant</th>

                    <th>Statut</th>

                    <th>Date</th>

                    <th class="text-end">

                        Actions

                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if (empty($commandes)): ?>

                <tr>

                    <td colspan="6">

                        <div class="empty-orders">

                            <div class="empty-orders-icon">

                                <i class="bi bi-cart-x"></i>

                            </div>

                            <h5>

                                Aucune commande

                            </h5>

                            <p>

                                Aucune commande n'a encore été enregistrée.

                            </p>

                        </div>

                    </td>

                </tr>


                <?php else: ?>


                <?php foreach ($commandes as $commande): ?>


                <?php

                    [$label, $class] =
                        statutCommandeLabel(
                            $commande['statut']
                        );

                    ?>


                <?php

                    $statusClass = match ($class) {

                        'warning' =>
                            'status-warning',

                        'info' =>
                            'status-info',

                        'primary' =>
                            'status-primary',

                        'secondary' =>
                            'status-secondary',

                        'success' =>
                            'status-success',

                        'danger' =>
                            'status-danger',

                        default =>
                            'status-secondary'

                    };

                    ?>


                <tr>


                    <!-- NUMÉRO -->

                    <td>

                        <span class="order-number">

                            <i class="bi bi-receipt"></i>

                            #<?= htmlspecialchars(
                                    $commande['numero_commande']
                                ) ?>

                        </span>

                    </td>


                    <!-- CLIENT -->

                    <td>

                        <div class="client-info">

                            <div class="client-avatar">

                                <i class="bi bi-person"></i>

                            </div>

                            <div class="client-name">

                                <?= htmlspecialchars(
                                        $commande['prenom']
                                        . ' '
                                        . $commande['nom']
                                    ) ?>

                            </div>

                        </div>

                    </td>


                    <!-- MONTANT -->

                    <td>

                        <span class="order-amount">

                            <?= number_format(
                                    (float)$commande['montant_total'],
                                    0,
                                    ',',
                                    ' '
                                ) ?>

                            FCFA

                        </span>

                    </td>


                    <!-- STATUT -->

                    <td>

                        <span class="order-status <?= $statusClass ?>">

                            <span class="status-dot"></span>

                            <?= htmlspecialchars($label) ?>

                        </span>

                    </td>


                    <!-- DATE -->

                    <td>

                        <span class="order-date">

                            <i class="bi bi-calendar3 me-1"></i>

                            <?= date(
                                    'd/m/Y H:i',
                                    strtotime(
                                        $commande['date_commande']
                                    )
                                ) ?>

                        </span>

                    </td>


                    <!-- ACTION -->

                    <td class="text-end">

                        <a href="view.php?id=<?= (int)$commande['id_commande'] ?>" class="action-btn btn-view"
                            title="Voir les détails">

                            <i class="bi bi-eye"></i>

                        </a>

                    </td>

                </tr>


                <?php endforeach; ?>


                <?php endif; ?>


            </tbody>

        </table>

    </div>

</div>



<?php require_once __DIR__ . "/../includes/footer.php"; ?>