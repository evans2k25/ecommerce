<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Connexion') ?> - Administration</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
    :root { --primary: #ED80E9; --primary-dark: #C95BC5; --dark: #1F1F29; }
    body { min-height: 100vh; display:flex; align-items:center; justify-content:center; background:linear-gradient(135deg,#f8f8fa,#f5dff3); font-family: "Poppins", sans-serif; }
    .login-card { width:100%; max-width:430px; background:white; border-radius:20px; padding:40px; box-shadow:0 20px 50px rgba(0,0,0,.10); }
    .logo { width:70px; height:70px; margin:auto; display:flex; align-items:center; justify-content:center; border-radius:18px; background:rgba(237,128,233,.15); color:var(--primary); font-size:30px; }
    .btn-login { width:100%; padding:12px; border:none; border-radius:10px; background:var(--primary); color:white; font-weight:600; }
    .btn-login:hover { background:var(--primary-dark); transform:translateY(-2px); }
    .form-control { padding:12px; border-radius:10px; }
    .form-control:focus { border-color:var(--primary); box-shadow:0 0 0 .2rem rgba(237,128,233,.20); }
    </style>
</head>
<body>
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
