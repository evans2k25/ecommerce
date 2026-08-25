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

// possible redirect after login (from auth guard)
$error = "";

$email = "";
$redirect = $_GET['redirect'] ?? $_SESSION['after_login_redirect'] ?? '';

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

        // attempt login (no debug logging)


        $admin =
            $adminModel->login(
                $email,
                $motDePasse
            );



        if ($admin) {

            session_regenerate_id(true);

            $_SESSION['admin'] = $admin;

            // success — session set

            // set a flash message for next page
            $_SESSION['flash'] = [
                'type' => 'success',
                'message' => 'Connecté — redirection en cours.'
            ];

            // create a signed admin_auth cookie to restore session if PHPSESSID is lost
            $adminId = (int) ($admin['id_admin'] ?? $admin['id'] ?? 0);
            $appKey = getenv('APP_KEY') ?: 'dev_local_key';
            $payload = $adminId . '|' . time();
            $hmac = hash_hmac('sha256', $payload, $appKey);
            $cookieVal = base64_encode($payload . '|' . $hmac);
            // set cookie for site root, httponly
            setcookie('admin_auth', $cookieVal, 0, '/', '', false, true);

            // determine safe redirect target
            $postRedirect = $_POST['redirect'] ?? $_SESSION['after_login_redirect'] ?? '';
            // clear stored redirect
            unset($_SESSION['after_login_redirect']);

            $target = '';
            if (is_string($postRedirect) && $postRedirect !== '') {
                // allow only internal paths starting with '/'
                $hasScheme = preg_match('#^[a-zA-Z][a-zA-Z0-9+.-]*://#', $postRedirect);
                if (!$hasScheme && str_starts_with($postRedirect, '/') && strpos($postRedirect, '..') === false) {
                    $target = $postRedirect;
                }
            }

            // server-side redirect (flash is already set and will be shown on target)
            $afterTarget = $target ?: '/ecommerce/admin/dashboard.php';
            header('Location: ' . $afterTarget);
            exit;

        }

    }

    // no debug logging

}

?>

<?php require_once __DIR__ . '/includes/auth-header.php'; ?>

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

        <?php if ($redirect): ?>
        <input type="hidden" name="redirect" value="<?= htmlspecialchars($redirect) ?>">
        <?php endif; ?>

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

            <input type="password" name="mot_de_passe" class="form-control" placeholder="Votre mot de passe" required>

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

<?php require_once __DIR__ . '/includes/auth-footer.php'; ?>