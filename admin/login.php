<?php

session_start();

require_once __DIR__ . "/../config/database.php";
require_once __DIR__ . "/../models/Administrateur.php";

if (isset($_SESSION['admin'])) {
    header("Location: dashboard.php");
    exit;
}

$database = new Database();
$db = $database->getConnection();

$adminModel = new Administrateur($db);

$error = "";

$email = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim(
        $_POST['email'] ?? ''
    );

    $motDePasse =
        $_POST['mot_de_passe'] ?? '';


    if ($email === '') {

        $error =
            "L'adresse email est obligatoire.";

    } elseif ($motDePasse === '') {

        $error =
            "Le mot de passe est obligatoire.";

    } else {

        $admin =
            $adminModel->login(
                $email,
                $motDePasse
            );


        if ($admin) {

            session_regenerate_id(true);

            $_SESSION['admin'] = [

                'id' =>
                    $admin['id_administrateur'],

                'nom' =>
                    $admin['nom'],

                'prenom' =>
                    $admin['prenom'],

                'email' =>
                    $admin['email']
            ];


            header(
                "Location: dashboard.php"
            );

            exit;

        } else {

            $error =
                "Email ou mot de passe incorrect.";
        }
    }
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Connexion Administration</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">

    <style>
    :root {

        --primary: #ED80E9;

        --primary-dark: #C95BC5;

        --dark: #1F1F29;

    }


    body {

        min-height: 100vh;

        display: flex;

        align-items: center;

        justify-content: center;

        background:
            linear-gradient(135deg,
                #f8f8fa,
                #f5dff3);

        font-family:
            "Poppins",
            sans-serif;

    }


    .login-card {

        width: 100%;

        max-width: 430px;

        background: white;

        border-radius: 20px;

        padding: 40px;

        box-shadow:
            0 20px 50px rgba(0, 0, 0, .10);

    }


    .logo {

        width: 70px;

        height: 70px;

        margin: auto;

        display: flex;

        align-items: center;

        justify-content: center;

        border-radius: 18px;

        background:
            rgba(237,
                128,
                233,
                .15);

        color: var(--primary);

        font-size: 30px;

    }


    .btn-login {

        width: 100%;

        padding: 12px;

        border: none;

        border-radius: 10px;

        background:
            var(--primary);

        color: white;

        font-weight: 600;

        transition: .3s;

    }


    .btn-login:hover {

        background:
            var(--primary-dark);

        transform:
            translateY(-2px);

    }


    .form-control {

        padding: 12px;

        border-radius: 10px;

    }


    .form-control:focus {

        border-color:
            var(--primary);

        box-shadow:
            0 0 0 .2rem rgba(237,
                128,
                233,
                .20);

    }
    </style>

</head>


<body>

    <div class="login-card">

        <div class="text-center mb-4">

            <div class="logo mb-3">

                <i class="bi bi-shield-lock"></i>

            </div>

            <h3 class="fw-bold">
                Administration
            </h3>

            <p class="text-muted mb-0">
                Connectez-vous à votre espace
            </p>

        </div>


        <?php if ($error): ?>

        <div class="alert alert-danger">

            <i class="bi bi-exclamation-circle"></i>

            <?= htmlspecialchars($error) ?>

        </div>

        <?php endif; ?>


        <form method="POST" action="">

            <div class="mb-3">

                <label class="form-label">
                    Adresse email
                </label>

                <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($email) ?>"
                    placeholder="admin@example.com" required>

            </div>


            <div class="mb-4">

                <label class="form-label">
                    Mot de passe
                </label>

                <input type="password" name="mot_de_passe" class="form-control" placeholder="Votre mot de passe"
                    required>

            </div>


            <button type="submit" class="btn-login">

                <i class="bi bi-box-arrow-in-right"></i>

                Se connecter

            </button>

        </form>


        <div class="text-center mt-4">

            <a href="../index.php" class="text-decoration-none" style="color:#ED80E9">

                <i class="bi bi-arrow-left"></i>

                Retour à la boutique

            </a>

        </div>

    </div>

</body>

</html>