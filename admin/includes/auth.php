<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin'])) {
    $loginPath = (str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/products/')
        || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/categories/')
        || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/orders/')
        || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admins/'))
        ? '../login.php'
        : 'login.php';

    header("Location: " . $loginPath);
    exit;
}

$admin = $_SESSION['admin'];
$adminNom = htmlspecialchars(
    ($admin['prenom'] ?? '') . ' ' . ($admin['nom'] ?? '')
);
$adminRole = htmlspecialchars($admin['role'] ?? 'admin');
$adminPage = $adminPage ?? '';

$inSubfolder = str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/products/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/categories/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/orders/')
    || str_contains($_SERVER['SCRIPT_NAME'] ?? '', '/admins/');

$adminBase = $inSubfolder ? '../' : '';
$assetBase = $inSubfolder ? '../../' : '../';
