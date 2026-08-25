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

    // try to restore session from signed cookie if available
    if (!empty($_COOKIE['admin_auth'])) {
        $cookie = $_COOKIE['admin_auth'];
        $decoded = base64_decode($cookie, true);
        if ($decoded !== false) {
            $parts = explode('|', $decoded);
            if (count($parts) === 3) {
                [$id, $ts, $sig] = $parts;
                $appKey = getenv('APP_KEY') ?: 'dev_local_key';
                $payload = $id . '|' . $ts;
                $expected = hash_hmac('sha256', $payload, $appKey);
                if (hash_equals($expected, $sig)) {
                    // load admin from DB and restore session
                    require_once __DIR__ . '/../../config/database.php';
                    require_once __DIR__ . '/../../models/Administrateur.php';
                    try {
                        $db = (new Database())->getConnection();
                        $adminModel = new Administrateur($db);
                        $adminData = $adminModel->getById((int)$id);
                        if ($adminData) {
                            $_SESSION['admin'] = $adminData;
                        }
                    } catch (Throwable $e) {
                        // ignore and continue to redirect to login
                    }
                }
            }
        }
    }

    // remember requested page so we can return after successful login
    $currentRequest = $_SERVER['REQUEST_URI'] ?? ($_SERVER['SCRIPT_NAME'] ?? '');
    if (!str_contains($currentRequest, 'login.php')) {
        $_SESSION['after_login_redirect'] = $currentRequest;
    }

    if (isset($_SESSION['admin'])) {
        // session restored, continue
    } else {
        header("Location: " . $loginPath);
        exit;
    }
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
