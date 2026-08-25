<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Administrateur.php";


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

$database = new Database();
$db = $database->getConnection();

$adminModel = new Administrateur($db);

$error = '';
$success = '';



/*
|--------------------------------------------------------------------------
| Suppression d'un administrateur
|--------------------------------------------------------------------------
*/

if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {

    $deleteId = (int) $_GET['delete'];

    /*
    |----------------------------------------------------------------------
    | Empêcher la suppression de son propre compte
    |----------------------------------------------------------------------
    */

    if ($deleteId === (int) ($_SESSION['admin']['id'] ?? 0)) {

        $error = "Vous ne pouvez pas supprimer votre propre compte.";

    } else {

        try {

            $adminModel->delete($deleteId);

            header("Location: index.php?success=deleted");

            exit;

        } catch (Throwable $e) {

            $error = $e->getMessage();

        }

    }

}



/*
|--------------------------------------------------------------------------
| Récupération des administrateurs
|--------------------------------------------------------------------------
*/

try {

    $admins = $adminModel->getAll();

} catch (Throwable $e) {

    $admins = [];

    $error = $e->getMessage();

}



/*
|--------------------------------------------------------------------------
| Messages de succès
|--------------------------------------------------------------------------
*/

if (isset($_GET['success'])) {

    $messages = [

        'created' => "Administrateur créé avec succès.",

        'deleted' => "Administrateur supprimé avec succès."

    ];

    $success = $messages[$_GET['success']] ?? $success;

}



/*
|--------------------------------------------------------------------------
| Page
|--------------------------------------------------------------------------
*/

$pageTitle = "Administrateurs";

$adminPage = "admins";


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
       BOUTON AJOUTER
    ========================================================= */

.btn-primary-custom {

    background-color: var(--primary);

    border: none;

    color: white;

    padding: 11px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    box-shadow: 0 5px 15px rgba(237, 128, 233, .25);

    transition: all .25s ease;

}



.btn-primary-custom:hover {

    background-color: var(--primary-dark);

    color: white;

    transform: translateY(-2px);

    box-shadow: 0 8px 20px rgba(201, 91, 197, .30);

}



/* =========================================================
       ALERTES
    ========================================================= */

.alert {

    border: none;

    border-radius: 12px;

    padding: 14px 17px;

    font-size: 13px;

    box-shadow: 0 4px 15px rgba(0, 0, 0, .04);

}



.alert-success {

    background-color: #e9f7ef;

    color: #146c43;

}



.alert-danger {

    background-color: #fce8e8;

    color: #842029;

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

.admins-table {

    margin-bottom: 0;

    vertical-align: middle;

}



.admins-table thead th {

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



.admins-table tbody td {

    padding: 15px 18px;

    border-bottom: 1px solid #f1f1f1;

    font-size: 13px;

    color: #555;

}



.admins-table tbody tr {

    transition: all .2s ease;

}



.admins-table tbody tr:hover {

    background-color: #fff8ff;

}



.admins-table tbody tr:last-child td {

    border-bottom: none;

}



/* =========================================================
       PROFIL ADMIN
    ========================================================= */

.admin-profile {

    display: flex;

    align-items: center;

    gap: 12px;

}



.admin-avatar {

    width: 43px;

    height: 43px;

    min-width: 43px;

    border-radius: 50%;

    display: flex;

    align-items: center;

    justify-content: center;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 18px;

    font-weight: 700;

}



.admin-fullname {

    color: var(--dark);

    font-size: 13px;

    font-weight: 650;

    margin-bottom: 2px;

}



.admin-id {

    color: #999;

    font-size: 11px;

}



/* =========================================================
       EMAIL
    ========================================================= */

.admin-email {

    color: #555;

    font-size: 13px;

}



.admin-email i {

    color: var(--primary-dark);

    margin-right: 5px;

}



/* =========================================================
       RÔLES
    ========================================================= */

.role-badge {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 700;

    background-color: #f1e8ff;

    color: #6f42c1;

}



.role-badge i {

    font-size: 11px;

}



/* =========================================================
       STATUT
    ========================================================= */

.status-badge {

    display: inline-flex;

    align-items: center;

    gap: 6px;

    padding: 6px 10px;

    border-radius: 7px;

    font-size: 10px;

    font-weight: 700;

}



.status-dot {

    width: 6px;

    height: 6px;

    border-radius: 50%;

    background-color: currentColor;

}



.status-active {

    background-color: #d1e7dd;

    color: #146c43;

}



.status-inactive {

    background-color: #e2e3e5;

    color: #41464b;

}



/* =========================================================
       COMPTE CONNECTÉ
    ========================================================= */

.current-admin {

    display: inline-flex;

    align-items: center;

    gap: 5px;

    margin-left: 8px;

    padding: 4px 8px;

    border-radius: 6px;

    background-color: var(--primary-light);

    color: var(--primary-dark);

    font-size: 9px;

    font-weight: 700;

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



.btn-delete {

    background-color: #ffe8e8;

    color: var(--danger);

}



.btn-delete:hover {

    background-color: var(--danger);

    color: white;

    transform: translateY(-2px);

}



.action-disabled {

    width: 37px;

    height: 37px;

    display: inline-flex;

    align-items: center;

    justify-content: center;

    border-radius: 9px;

    background-color: #f1f1f1;

    color: #aaa;

    cursor: not-allowed;

}



/* =========================================================
       ÉTAT VIDE
    ========================================================= */

.empty-admins {

    padding: 65px 20px;

    text-align: center;

}



.empty-admins-icon {

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



.empty-admins h5 {

    color: var(--dark);

    font-weight: 700;

    margin-bottom: 7px;

}



.empty-admins p {

    color: #999;

    font-size: 13px;

    margin: 0 0 20px;

}



/* =========================================================
       RESPONSIVE
    ========================================================= */

@media (max-width: 768px) {

    .page-header {

        align-items: flex-start;

        gap: 15px;

    }



    .page-title {

        font-size: 22px;

    }



    .page-subtitle {

        font-size: 12px;

    }



    .btn-primary-custom {

        padding: 9px 13px;

        font-size: 12px;

    }



    .admin-card {

        border-radius: 14px;

    }



    .admins-table thead th,
    .admins-table tbody td {

        padding: 12px 10px;

    }



    .admin-avatar {

        width: 38px;

        height: 38px;

        min-width: 38px;

    }

}
</style>



<!-- =========================================================
     EN-TÊTE DE PAGE
========================================================= -->

<div class="page-header">

    <div>

        <h1 class="page-title">

            <i class="bi bi-shield-lock"></i>

            Administrateurs

        </h1>

        <p class="page-subtitle">

            Gérez les comptes ayant accès au back-office.

        </p>

    </div>


    <a href="create.php" class="btn btn-primary-custom">

        <i class="bi bi-plus-lg me-1"></i>

        Ajouter un administrateur

    </a>

</div>



<!-- =========================================================
     MESSAGE SUCCÈS
========================================================= -->

<?php if ($success): ?>

<div class="alert alert-success alert-dismissible fade show mb-4" role="alert">

    <i class="bi bi-check-circle me-2"></i>

    <?= htmlspecialchars($success) ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>



<!-- =========================================================
     MESSAGE ERREUR
========================================================= -->

<?php if ($error): ?>

<div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

    <i class="bi bi-exclamation-triangle me-2"></i>

    <?= htmlspecialchars($error) ?>

    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

</div>

<?php endif; ?>



<!-- =========================================================
     TABLEAU ADMINISTRATEURS
========================================================= -->

<div class="admin-card">

    <div class="table-responsive">

        <table class="table admins-table align-middle">

            <thead>

                <tr>

                    <th>Administrateur</th>

                    <th>Email</th>

                    <th>Rôle</th>

                    <th>Statut</th>

                    <th class="text-end">

                        Actions

                    </th>

                </tr>

            </thead>


            <tbody>


                <?php if (empty($admins)): ?>

                <tr>

                    <td colspan="5">

                        <div class="empty-admins">

                            <div class="empty-admins-icon">

                                <i class="bi bi-people"></i>

                            </div>

                            <h5>

                                Aucun administrateur

                            </h5>

                            <p>

                                Aucun compte administrateur n'est disponible.

                            </p>

                            <a href="create.php" class="btn btn-primary-custom">

                                <i class="bi bi-plus-lg me-1"></i>

                                Ajouter un administrateur

                            </a>

                        </div>

                    </td>

                </tr>


                <?php else: ?>


                <?php foreach ($admins as $item): ?>


                <?php

                    $adminId =
                        (int)$item['id_admin'];

                    $currentAdminId =
                        (int)($_SESSION['admin']['id'] ?? 0);

                    $isCurrentAdmin =
                        $adminId === $currentAdminId;

                    $isActive =
                        ($item['statut'] ?? '') === 'actif';

                    $role =
                        $item['role'] ?? 'admin';

                    ?>


                <tr>


                    <!-- ADMINISTRATEUR -->

                    <td>

                        <div class="admin-profile">


                            <div class="admin-avatar">

                                <?php

                                    $prenom =
                                        $item['prenom'] ?? '';

                                    $nom =
                                        $item['nom'] ?? '';

                                    $initiales =
                                        strtoupper(
                                            substr($prenom, 0, 1)
                                            . substr($nom, 0, 1)
                                        );

                                    ?>

                                <?= htmlspecialchars(
                                        $initiales ?: 'A'
                                    ) ?>

                            </div>


                            <div>

                                <div class="admin-fullname">

                                    <?= htmlspecialchars(
                                            $prenom
                                            . ' '
                                            . $nom
                                        ) ?>


                                    <?php if ($isCurrentAdmin): ?>

                                    <span class="current-admin">

                                        <i class="bi bi-person-check"></i>

                                        Vous

                                    </span>

                                    <?php endif; ?>

                                </div>


                                <div class="admin-id">

                                    ID #<?= $adminId ?>

                                </div>

                            </div>

                        </div>

                    </td>



                    <!-- EMAIL -->

                    <td>

                        <span class="admin-email">

                            <i class="bi bi-envelope"></i>

                            <?= htmlspecialchars(
                                    $item['email']
                                ) ?>

                        </span>

                    </td>



                    <!-- RÔLE -->

                    <td>

                        <?php

                            $roleIcon = match ($role) {

                                'super_admin' =>
                                    'bi-shield-fill-check',

                                'gestionnaire' =>
                                    'bi-person-gear',

                                default =>
                                    'bi-person-badge'

                            };

                            $roleLabel = match ($role) {

                                'super_admin' =>
                                    'Super administrateur',

                                'gestionnaire' =>
                                    'Gestionnaire',

                                'admin' =>
                                    'Administrateur',

                                default =>
                                    ucfirst(
                                        str_replace(
                                            '_',
                                            ' ',
                                            $role
                                        )
                                    )

                            };

                            ?>


                        <span class="role-badge">

                            <i class="bi <?= $roleIcon ?>"></i>

                            <?= htmlspecialchars(
                                    $roleLabel
                                ) ?>

                        </span>

                    </td>



                    <!-- STATUT -->

                    <td>

                        <?php if ($isActive): ?>

                        <span class="status-badge status-active">

                            <span class="status-dot"></span>

                            Actif

                        </span>

                        <?php else: ?>

                        <span class="status-badge status-inactive">

                            <span class="status-dot"></span>

                            Inactif

                        </span>

                        <?php endif; ?>

                    </td>



                    <!-- ACTION -->

                    <td class="text-end">


                        <?php if (!$isCurrentAdmin): ?>

                        <a href="index.php?delete=<?= $adminId ?>" class="action-btn btn-delete" title="Supprimer"
                            onclick="return confirm(
                                        'Voulez-vous vraiment supprimer cet administrateur ?'
                                    );">

                            <i class="bi bi-trash"></i>

                        </a>


                        <?php else: ?>

                        <span class="action-disabled" title="Vous ne pouvez pas supprimer votre propre compte">

                            <i class="bi bi-lock"></i>

                        </span>

                        <?php endif; ?>


                    </td>

                </tr>


                <?php endforeach; ?>


                <?php endif; ?>


            </tbody>

        </table>

    </div>

</div>



<?php require_once __DIR__ . "/../includes/footer.php"; ?>