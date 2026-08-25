<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Administrateur.php";

$database = new Database();
$db = $database->getConnection();

$adminModel = new Administrateur($db);

$errors = [];

$nom = '';
$prenom = '';
$email = '';
$role = 'admin';
$statut = 'actif';


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $confirmationMotDePasse = $_POST['confirmation_mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    $statut = $_POST['statut'] ?? 'actif';


    /*
    |--------------------------------------------------------------------------
    | Validation nom et prénom
    |--------------------------------------------------------------------------
    */

    if ($nom === '') {

        $errors[] = "Le nom est obligatoire.";

    } elseif (mb_strlen($nom) > 100) {

        $errors[] = "Le nom ne doit pas dépasser 100 caractères.";
    }


    if ($prenom === '') {

        $errors[] = "Le prénom est obligatoire.";

    } elseif (mb_strlen($prenom) > 100) {

        $errors[] = "Le prénom ne doit pas dépasser 100 caractères.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation email
    |--------------------------------------------------------------------------
    */

    if ($email === '') {

        $errors[] = "L'adresse email est obligatoire.";

    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {

        $errors[] = "L'adresse email est invalide.";

    } else {

        try {

            if ($adminModel->findByEmail($email)) {

                $errors[] = "Cet email est déjà utilisé.";

            }

        } catch (Throwable $e) {

            $errors[] =
                "Impossible de vérifier l'adresse email.";
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Validation mot de passe
    |--------------------------------------------------------------------------
    */

    if ($motDePasse === '') {

        $errors[] = "Le mot de passe est obligatoire.";

    } elseif (strlen($motDePasse) < 6) {

        $errors[] =
            "Le mot de passe doit contenir au moins 6 caractères.";
    }


    /*
    |--------------------------------------------------------------------------
    | Confirmation mot de passe
    |--------------------------------------------------------------------------
    */

    if ($confirmationMotDePasse === '') {

        $errors[] =
            "Veuillez confirmer le mot de passe.";

    } elseif ($motDePasse !== $confirmationMotDePasse) {

        $errors[] =
            "Les mots de passe ne correspondent pas.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation rôle
    |--------------------------------------------------------------------------
    */

    $rolesAutorises = [
        'super_admin',
        'admin',
        'gestionnaire'
    ];

    if (!in_array($role, $rolesAutorises, true)) {

        $errors[] = "Le rôle sélectionné est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation statut
    |--------------------------------------------------------------------------
    */

    $statutsAutorises = [
        'actif',
        'inactif'
    ];

    if (!in_array($statut, $statutsAutorises, true)) {

        $errors[] = "Le statut sélectionné est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Création du compte
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {

            $adminModel->create([

                'nom' =>
                    $nom,

                'prenom' =>
                    $prenom,

                'email' =>
                    $email,

                'mot_de_passe' =>
                    $motDePasse,

                'role' =>
                    $role,

                'statut' =>
                    $statut

            ]);


            /*
            |--------------------------------------------------------------------------
            | Redirection
            |--------------------------------------------------------------------------
            */

            header(
                "Location: index.php?success=created"
            );

            exit;


        } catch (Throwable $e) {

            $errors[] =
                "Impossible de créer le compte : "
                . $e->getMessage();
        }
    }
}


$pageTitle = "Ajouter un administrateur";
$adminPage = "admins";

require_once __DIR__ . "/../includes/header.php";

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

}


/*
|--------------------------------------------------------------------------
| PAGE
|--------------------------------------------------------------------------
*/

.admin-form-container {

    max-width: 950px;

    margin: 0 auto;

}


/*
|--------------------------------------------------------------------------
| EN-TÊTE
|--------------------------------------------------------------------------
*/

.page-header {

    margin-bottom: 25px;

}

.page-title {

    color: var(--dark);

    font-size: 27px;

    font-weight: 700;

}

.page-title i {

    color: var(--primary);

    margin-right: 8px;

}

.page-subtitle {

    color: #888;

    font-size: 14px;

}


/*
|--------------------------------------------------------------------------
| CARTE
|--------------------------------------------------------------------------
*/

.form-card {

    background: var(--white);

    border-radius: 18px;

    padding: 30px;

    border: 1px solid rgba(0, 0, 0, .04);

    box-shadow:
        0 8px 30px rgba(0, 0, 0, .05);

}


/*
|--------------------------------------------------------------------------
| SECTION
|--------------------------------------------------------------------------
*/

.form-section-title {

    color: var(--dark);

    font-size: 17px;

    font-weight: 700;

    margin-bottom: 20px;

}

.form-section-title i {

    color: var(--primary);

    margin-right: 7px;

}


/*
|--------------------------------------------------------------------------
| LABELS
|--------------------------------------------------------------------------
*/

.form-label {

    color: var(--dark);

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 7px;

}


/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

.form-control,
.form-select {

    min-height: 45px;

    border: 1px solid #e5e5e5;

    border-radius: 9px;

    font-size: 13px;

    color: var(--dark);

    box-shadow: none;

    transition: all .2s ease;

}

.form-control:focus,
.form-select:focus {

    border-color: var(--primary);

    box-shadow:
        0 0 0 .2rem rgba(237, 128, 233, .13);

}


/*
|--------------------------------------------------------------------------
| INPUT GROUP
|--------------------------------------------------------------------------
*/

.input-group-text {

    background: #fafafa;

    border-color: #e5e5e5;

    color: #888;

}


/*
|--------------------------------------------------------------------------
| ROLE CARD
|--------------------------------------------------------------------------
*/

.role-option {

    border: 1px solid #e5e5e5;

    border-radius: 12px;

    padding: 15px;

    cursor: pointer;

    transition: all .2s ease;

    height: 100%;

}

.role-option:hover {

    border-color: var(--primary);

    background: #fff8ff;

    transform: translateY(-2px);

}

.role-option i {

    font-size: 25px;

    color: var(--primary);

}

.role-option-title {

    font-weight: 700;

    font-size: 13px;

    color: var(--dark);

}

.role-option-text {

    font-size: 11px;

    color: #888;

}


/*
|--------------------------------------------------------------------------
| ALERT
|--------------------------------------------------------------------------
*/

.alert {

    border: none;

    border-radius: 12px;

    font-size: 13px;

    box-shadow:
        0 5px 15px rgba(0, 0, 0, .04);

}


/*
|--------------------------------------------------------------------------
| BOUTON PRINCIPAL
|--------------------------------------------------------------------------
*/

.btn-primary-custom {

    background: var(--primary);

    border: none;

    color: white;

    padding: 11px 20px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(237, 128, 233, .25);

    transition: all .25s ease;

}

.btn-primary-custom:hover {

    background: var(--primary-dark);

    color: white;

    transform: translateY(-2px);

    box-shadow:
        0 8px 20px rgba(201, 91, 197, .30);

}


/*
|--------------------------------------------------------------------------
| BOUTON RETOUR
|--------------------------------------------------------------------------
*/

.btn-back {

    display: inline-flex;

    align-items: center;

    gap: 7px;

    padding: 10px 17px;

    border-radius: 10px;

    border: 1px solid #ddd;

    background: white;

    color: #555;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: all .2s ease;

}

.btn-back:hover {

    background: #f5f5f5;

    color: var(--dark);

}


/*
|--------------------------------------------------------------------------
| SÉPARATEUR
|--------------------------------------------------------------------------
*/

.form-divider {

    border-top: 1px solid #eee;

    margin: 30px 0;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 768px) {

    .admin-form-container {

        max-width: 100%;

    }

    .form-card {

        padding: 20px;

        border-radius: 14px;

    }

    .page-title {

        font-size: 22px;

    }

}
</style>


<main class="admin-content">

    <div class="container-fluid">

        <div class="admin-form-container">


            <!-- =====================================================
                 EN-TÊTE
            ====================================================== -->

            <div class="page-header">

                <h1 class="page-title mb-1">

                    <i class="bi bi-person-plus-fill"></i>

                    Ajouter un utilisateur

                </h1>

                <p class="page-subtitle mb-0">

                    Créez un nouveau compte ayant accès au back-office.

                </p>

            </div>


            <!-- =====================================================
                 ERREURS
            ====================================================== -->

            <?php if (!empty($errors)): ?>

            <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

                <div class="fw-bold mb-2">

                    <i class="bi bi-exclamation-triangle-fill me-1"></i>

                    Impossible de créer le compte

                </div>

                <ul class="mb-0 ps-3">

                    <?php foreach ($errors as $error): ?>

                    <li>

                        <?= htmlspecialchars($error) ?>

                    </li>

                    <?php endforeach; ?>

                </ul>

                <button type="button" class="btn-close" data-bs-dismiss="alert">
                </button>

            </div>

            <?php endif; ?>


            <!-- =====================================================
                 FORMULAIRE
            ====================================================== -->

            <form method="POST" class="form-card">


                <!-- =================================================
                     INFORMATIONS PERSONNELLES
                ================================================== -->

                <h5 class="form-section-title">

                    <i class="bi bi-person-vcard"></i>

                    Informations personnelles

                </h5>


                <div class="row g-4">


                    <!-- NOM -->

                    <div class="col-md-6">

                        <label for="nom" class="form-label">

                            Nom

                            <span class="text-danger">*</span>

                        </label>

                        <input type="text" name="nom" id="nom" class="form-control"
                            value="<?= htmlspecialchars($nom) ?>" placeholder="Ex : Goly" maxlength="100" required>

                    </div>


                    <!-- PRÉNOM -->

                    <div class="col-md-6">

                        <label for="prenom" class="form-label">

                            Prénom

                            <span class="text-danger">*</span>

                        </label>

                        <input type="text" name="prenom" id="prenom" class="form-control"
                            value="<?= htmlspecialchars($prenom) ?>" placeholder="Ex : Eric" maxlength="100" required>

                    </div>


                    <!-- EMAIL -->

                    <div class="col-12">

                        <label for="email" class="form-label">

                            Adresse email

                            <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-envelope"></i>

                            </span>

                            <input type="email" name="email" id="email" class="form-control"
                                value="<?= htmlspecialchars($email) ?>" placeholder="exemple@email.com" maxlength="150"
                                required>

                        </div>

                    </div>

                </div>


                <div class="form-divider"></div>


                <!-- =================================================
                     MOT DE PASSE
                ================================================== -->

                <h5 class="form-section-title">

                    <i class="bi bi-shield-lock"></i>

                    Sécurité du compte

                </h5>


                <div class="row g-4">


                    <!-- MOT DE PASSE -->

                    <div class="col-md-6">

                        <label for="mot_de_passe" class="form-label">

                            Mot de passe

                            <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-lock"></i>

                            </span>

                            <input type="password" name="mot_de_passe" id="mot_de_passe" class="form-control"
                                minlength="6" placeholder="Minimum 6 caractères" required>

                            <button type="button" class="btn btn-outline-secondary" id="togglePassword">

                                <i class="bi bi-eye"></i>

                            </button>

                        </div>

                        <small class="text-muted">

                            Minimum 6 caractères.

                        </small>

                    </div>


                    <!-- CONFIRMATION -->

                    <div class="col-md-6">

                        <label for="confirmation_mot_de_passe" class="form-label">

                            Confirmer le mot de passe

                            <span class="text-danger">*</span>

                        </label>

                        <div class="input-group">

                            <span class="input-group-text">

                                <i class="bi bi-lock-fill"></i>

                            </span>

                            <input type="password" name="confirmation_mot_de_passe" id="confirmation_mot_de_passe"
                                class="form-control" minlength="6" placeholder="Répétez le mot de passe" required>

                        </div>

                    </div>

                </div>


                <div class="form-divider"></div>


                <!-- =================================================
                     DROITS D'ACCÈS
                ================================================== -->

                <h5 class="form-section-title">

                    <i class="bi bi-shield-check"></i>

                    Droits d'accès

                </h5>


                <div class="row g-3 mb-4">


                    <!-- SUPER ADMIN -->

                    <div class="col-md-4">

                        <label class="role-option d-block">

                            <div class="form-check">

                                <input class="form-check-input" type="radio" name="role" value="super_admin" <?= $role === 'super_admin'
                                        ? 'checked'
                                        : '' ?>>

                                <i class="bi bi-shield-fill-check ms-2"></i>

                            </div>

                            <div class="role-option-title mt-2">

                                Super administrateur

                            </div>

                            <div class="role-option-text">

                                Accès complet au système.

                            </div>

                        </label>

                    </div>


                    <!-- ADMIN -->

                    <div class="col-md-4">

                        <label class="role-option d-block">

                            <div class="form-check">

                                <input class="form-check-input" type="radio" name="role" value="admin" <?= $role === 'admin'
                                        ? 'checked'
                                        : '' ?>>

                                <i class="bi bi-person-badge ms-2"></i>

                            </div>

                            <div class="role-option-title mt-2">

                                Administrateur

                            </div>

                            <div class="role-option-text">

                                Gestion générale de la boutique.

                            </div>

                        </label>

                    </div>


                    <!-- GESTIONNAIRE -->

                    <div class="col-md-4">

                        <label class="role-option d-block">

                            <div class="form-check">

                                <input class="form-check-input" type="radio" name="role" value="gestionnaire" <?= $role === 'gestionnaire'
                                        ? 'checked'
                                        : '' ?>>

                                <i class="bi bi-person-gear ms-2"></i>

                            </div>

                            <div class="role-option-title mt-2">

                                Gestionnaire

                            </div>

                            <div class="role-option-text">

                                Gestion des produits et commandes.

                            </div>

                        </label>

                    </div>

                </div>


                <!-- STATUT -->

                <div class="row">

                    <div class="col-md-6">

                        <label for="statut" class="form-label">

                            Statut du compte

                        </label>

                        <select name="statut" id="statut" class="form-select">

                            <option value="actif" <?= $statut === 'actif'
                                    ? 'selected'
                                    : '' ?>>

                                Actif

                            </option>

                            <option value="inactif" <?= $statut === 'inactif'
                                    ? 'selected'
                                    : '' ?>>

                                Inactif

                            </option>

                        </select>

                    </div>

                </div>


                <!-- =================================================
                     BOUTONS
                ================================================== -->

                <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">


                    <a href="index.php" class="btn-back">

                        <i class="bi bi-arrow-left"></i>

                        Annuler

                    </a>


                    <button type="submit" class="btn btn-primary-custom">

                        <i class="bi bi-person-plus-fill me-1"></i>

                        Créer l'utilisateur

                    </button>

                </div>


            </form>

        </div>

    </div>

</main>


<script>
/*
|--------------------------------------------------------------------------
| Afficher / masquer le mot de passe
|--------------------------------------------------------------------------
*/

const togglePassword =
    document.getElementById('togglePassword');

const password =
    document.getElementById('mot_de_passe');

if (togglePassword && password) {

    togglePassword.addEventListener(
        'click',
        function() {

            const type =
                password.getAttribute('type') ===
                'password' ?
                'text' :
                'password';

            password.setAttribute(
                'type',
                type
            );

            const icon =
                this.querySelector('i');

            if (type === 'password') {

                icon.className =
                    'bi bi-eye';

            } else {

                icon.className =
                    'bi bi-eye-slash';

            }

        }
    );

}


/*
|--------------------------------------------------------------------------
| Vérification confirmation mot de passe
|--------------------------------------------------------------------------
*/

const confirmPassword =
    document.getElementById(
        'confirmation_mot_de_passe'
    );

if (confirmPassword && password) {

    confirmPassword.addEventListener(
        'input',
        function() {

            if (
                this.value !== '' &&
                this.value !== password.value
            ) {

                this.classList.add(
                    'is-invalid'
                );

            } else {

                this.classList.remove(
                    'is-invalid'
                );

            }

        }
    );

}
</script>


<?php require_once __DIR__ . "/../includes/footer.php"; ?>