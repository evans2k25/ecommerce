<?php
// Provide safe defaults if a page forgot to include admin/includes/auth.php
$inSubfolder = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/products/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/categories/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/orders/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admins/');

$assetBase = $assetBase ?? ($inSubfolder ? '../../' : '../');
$adminBase = $adminBase ?? ($inSubfolder ? '../' : '');
$adminPage = $adminPage ?? '';
$adminNom = $adminNom ?? '';
$adminRole = $adminRole ?? '';
$admin = $admin ?? [];

?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Administration') ?> - E-Commerce</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= $assetBase ?>assets/css/admin.css">
</head>
<body>
<aside class="sidebar">
    <div class="brand">
        <i class="bi bi-shop"></i>
        E-Commerce
    </div>

    <div class="menu-title">Général</div>
    <a href="<?= $adminBase ?>dashboard.php" class="<?= $adminPage === 'dashboard' ? 'active' : '' ?>">
        <i class="bi bi-speedometer2"></i> Tableau de bord
    </a>

    <div class="menu-title">Catalogue</div>
    <a href="<?= $adminBase ?>products/index.php" class="<?= $adminPage === 'products' ? 'active' : '' ?>">
        <i class="bi bi-box-seam"></i> Produits
    </a>
    <a href="<?= $adminBase ?>categories/index.php" class="<?= $adminPage === 'categories' ? 'active' : '' ?>">
        <i class="bi bi-grid"></i> Catégories
    </a>

    <div class="menu-title">Ventes</div>
    <a href="<?= $adminBase ?>orders/index.php" class="<?= $adminPage === 'orders' ? 'active' : '' ?>">
        <i class="bi bi-cart-check"></i> Commandes
    </a>

    <div class="menu-title">Administration</div>
    <a href="<?= $adminBase ?>admins/index.php" class="<?= $adminPage === 'admins' ? 'active' : '' ?>">
        <i class="bi bi-people"></i> Administrateurs
    </a>
    <a href="<?= $assetBase ?>index.php" target="_blank">
        <i class="bi bi-shop-window"></i> Voir la boutique
    </a>
    <a href="<?= $adminBase ?>logout.php">
        <i class="bi bi-box-arrow-right"></i> Déconnexion
    </a>
</aside>

<main class="main">
    <header class="topbar">
        <h1><?= htmlspecialchars($pageTitle ?? 'Administration') ?></h1>
        <div class="admin-info">
            <div>
                <div class="admin-name"><?= $adminNom ?></div>
                <div class="admin-role"><?= $adminRole ?></div>
            </div>
            <div class="admin-avatar">
                <?= strtoupper(substr($admin['prenom'] ?? 'A', 0, 1)) ?>
            </div>
        </div>
    </header>
    <div class="content">
        <?php
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!empty($_SESSION['flash'])) {
            $f = $_SESSION['flash'];
            unset($_SESSION['flash']);
            $type = htmlspecialchars($f['type'] ?? 'info');
            $msg = htmlspecialchars($f['message'] ?? '');
            echo "<div class=\"container mt-3\"><div class=\"alert alert-$type\">$msg</div></div>";
        }
        ?>
