<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$cartCount = 0;

if (isset($_SESSION['cart']) && is_array($_SESSION['cart'])) {

    foreach ($_SESSION['cart'] as $item) {
        $cartCount += (int) ($item['quantity'] ?? 0);
    }
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <meta name="description" content="MuseModerne - Boutique e-commerce moderne">

    <meta name="theme-color" content="#ED80E9">

    <title><?= htmlspecialchars($pageTitle ?? 'MuseModerne') ?></title>

    <!-- Google Font -->
    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap"
        rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- CSS principal -->
    <link rel="stylesheet" href="<?= $baseUrl ?? '' ?>assets/css/style.css?v=<?= time() ?>">

</head>

<body>

    <?php require_once __DIR__ . '/navbar.php'; ?>

    <main class="fade-in">