<?php

session_start();

require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Produit.php";
require_once __DIR__ . "/../../models/Categorie.php";


/*
|--------------------------------------------------------------------------
| Vérification administrateur
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION['admin'])) {

    header("Location: ../login.php");
    exit;
}


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

$database = new Database();
$db = $database->getConnection();


/*
|--------------------------------------------------------------------------
| Modèles
|--------------------------------------------------------------------------
*/

$produitModel = new Produit($db);
$categorieModel = new Categorie($db);


/*
|--------------------------------------------------------------------------
| Variables
|--------------------------------------------------------------------------
*/

$errors = [];

$nom = '';
$description = '';
$idCategorie = '';
$prix = '';
$stock = '';
$statut = 'disponible';


/*
|--------------------------------------------------------------------------
| Récupérer les catégories
|--------------------------------------------------------------------------
*/

try {

    $categories = $categorieModel->getAll();

} catch (Throwable $e) {

    $categories = [];

    $errors[] =
        "Impossible de récupérer les catégories : "
        . $e->getMessage();
}


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if ($_SERVER['REQUEST_METHOD'] === 'POST') {


    /*
    |--------------------------------------------------------------------------
    | Récupération des données
    |--------------------------------------------------------------------------
    */

    $nom =
        trim($_POST['nom'] ?? '');

    $description =
        trim($_POST['description'] ?? '');

    $idCategorie =
        (int) ($_POST['id_categorie'] ?? 0);

    $prix =
        trim($_POST['prix'] ?? '');

    $stock =
        trim($_POST['stock'] ?? '');

    $statut =
        $_POST['statut'] ?? 'disponible';


    /*
    |--------------------------------------------------------------------------
    | Validation du nom
    |--------------------------------------------------------------------------
    */

    if ($nom === '') {

        $errors[] =
            "Le nom du produit est obligatoire.";

    } elseif (mb_strlen($nom) > 150) {

        $errors[] =
            "Le nom du produit ne doit pas dépasser 150 caractères.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation catégorie
    |--------------------------------------------------------------------------
    */

    if ($idCategorie <= 0) {

        $errors[] =
            "Veuillez sélectionner une catégorie.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation prix
    |--------------------------------------------------------------------------
    */

    if ($prix === '') {

        $errors[] =
            "Le prix est obligatoire.";

    } elseif (!is_numeric($prix)) {

        $errors[] =
            "Le prix doit être un nombre.";

    } elseif ((float) $prix < 0) {

        $errors[] =
            "Le prix ne peut pas être négatif.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation stock
    |--------------------------------------------------------------------------
    */

    if ($stock === '') {

        $errors[] =
            "Le stock est obligatoire.";

    } elseif (
        !ctype_digit((string) $stock)
    ) {

        $errors[] =
            "Le stock doit être un nombre entier positif.";

    } elseif ((int) $stock < 0) {

        $errors[] =
            "Le stock ne peut pas être négatif.";
    }


    /*
    |--------------------------------------------------------------------------
    | Validation statut
    |--------------------------------------------------------------------------
    */

    $statutsAutorises = [
        'disponible',
        'indisponible',
        'archive'
    ];

    if (
        !in_array(
            $statut,
            $statutsAutorises,
            true
        )
    ) {

        $errors[] =
            "Le statut sélectionné est invalide.";
    }


    /*
    |--------------------------------------------------------------------------
    | Vérifier l'image
    |--------------------------------------------------------------------------
    */

    $imageName = null;

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {

        /*
        | Erreur upload
        */

        if (
            $_FILES['image']['error']
            !== UPLOAD_ERR_OK
        ) {

            $errors[] =
                "Une erreur est survenue lors de l'envoi de l'image.";

        } else {

            $image = $_FILES['image'];


            /*
            | Taille maximale : 5 Mo
            */

            if ($image['size'] > 5 * 1024 * 1024) {

                $errors[] =
                    "L'image ne doit pas dépasser 5 Mo.";
            }


            /*
            | Vérification MIME
            */

            $finfo =
                new finfo(FILEINFO_MIME_TYPE);

            $mime =
                $finfo->file($image['tmp_name']);


            $mimeAutorises = [

                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'

            ];


            if (
                !in_array(
                    $mime,
                    $mimeAutorises,
                    true
                )
            ) {

                $errors[] =
                    "Format d'image non autorisé. "
                    . "Utilisez JPG, PNG, WEBP ou GIF.";
            }


            /*
            | Extension
            */

            $extensions = [

                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'

            ];


            if (
                empty($errors) &&
                isset($extensions[$mime])
            ) {

                $extension =
                    $extensions[$mime];


                /*
                |--------------------------------------------------------------------------
                | Nom unique
                |--------------------------------------------------------------------------
                */

                $imageName =
                    'product_' .
                    date('Ymd_His') .
                    '_' .
                    bin2hex(
                        random_bytes(5)
                    ) .
                    '.' .
                    $extension;
            }
        }
    }


    /*
    |--------------------------------------------------------------------------
    | Création du produit
    |--------------------------------------------------------------------------
    */

    if (empty($errors)) {

        try {


            /*
            |--------------------------------------------------------------------------
            | Vérifier que la catégorie existe
            |--------------------------------------------------------------------------
            */

            $sqlCategorie = "
                SELECT id_categorie
                FROM categories
                WHERE id_categorie = :id_categorie
                AND statut = 'active'
                LIMIT 1
            ";

            $stmtCategorie =
                $db->prepare(
                    $sqlCategorie
                );

            $stmtCategorie->execute([
                'id_categorie' =>
                    $idCategorie
            ]);


            if (!$stmtCategorie->fetch()) {

                throw new Exception(
                    "La catégorie sélectionnée n'existe pas ou est inactive."
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Dossier upload
            |--------------------------------------------------------------------------
            */

            $uploadDirectory =
                __DIR__ .
                "/../../uploads/products/";


            /*
            | Créer le dossier s'il n'existe pas
            */

            if (
                !is_dir(
                    $uploadDirectory
                )
            ) {

                if (
                    !mkdir(
                        $uploadDirectory,
                        0777,
                        true
                    )
                ) {

                    throw new Exception(
                        "Impossible de créer le dossier uploads/products."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Déplacer l'image
            |--------------------------------------------------------------------------
            */

            if ($imageName !== null) {

                $destination =
                    $uploadDirectory .
                    $imageName;


                if (
                    !move_uploaded_file(
                        $_FILES['image']['tmp_name'],
                        $destination
                    )
                ) {

                    throw new Exception(
                        "Impossible d'enregistrer l'image."
                    );
                }
            }


            /*
            |--------------------------------------------------------------------------
            | Création en base
            |--------------------------------------------------------------------------
            */

            $sql = "
                INSERT INTO produits (

                    id_categorie,
                    nom,
                    description,
                    prix,
                    stock,
                    image,
                    statut

                ) VALUES (

                    :id_categorie,
                    :nom,
                    :description,
                    :prix,
                    :stock,
                    :image,
                    :statut

                )
            ";


            $stmt =
                $db->prepare($sql);


            $stmt->execute([

                'id_categorie' =>
                    $idCategorie,

                'nom' =>
                    $nom,

                'description' =>
                    $description !== ''
                        ? $description
                        : null,

                'prix' =>
                    (float) $prix,

                'stock' =>
                    (int) $stock,

                'image' =>
                    $imageName,

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


            /*
            |--------------------------------------------------------------------------
            | Supprimer l'image si la création échoue
            |--------------------------------------------------------------------------
            */

            if (
                $imageName !== null &&
                isset($destination) &&
                file_exists($destination)
            ) {

                unlink($destination);
            }


            $errors[] =
                "Impossible de créer le produit : "
                . $e->getMessage();
        }
    }
}

?>

<!DOCTYPE html>

<html lang="fr">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>
        Ajouter un produit - Administration
    </title>


    <!-- Bootstrap -->

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">


    <!-- Bootstrap Icons -->

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">


    <!-- Google Font -->

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">


    <style>
    :root {

        --primary: #ED80E9;
        --primary-dark: #C95BC5;
        --primary-light: #F5B3F2;

        --dark: #1F1F29;
        --text: #333333;
        --light: #F8F8FA;
        --white: #FFFFFF;

    }


    body {

        font-family: "Poppins", sans-serif;

        background-color: var(--light);

        color: var(--text);

    }


    /* Navbar */

    .admin-navbar {

        background-color: var(--dark);

        min-height: 70px;

    }


    .admin-navbar .navbar-brand {

        color: var(--primary);

        font-weight: 700;

        font-size: 1.3rem;

    }


    .admin-navbar .navbar-brand:hover {

        color: var(--primary-light);

    }


    .admin-name {

        color: white;

        font-size: 14px;

    }


    /* Contenu */

    .admin-content {

        padding: 35px;

    }


    /* Carte */

    .form-card {

        background-color: white;

        border: none;

        border-radius: 18px;

        box-shadow:
            0 5px 25px rgba(0, 0, 0, 0.06);

        padding: 30px;

    }


    /* Titres */

    .page-title {

        color: var(--dark);

        font-weight: 700;

    }


    .page-subtitle {

        color: #777;

        font-size: 14px;

    }


    /* Labels */

    .form-label {

        font-weight: 600;

        color: var(--dark);

        font-size: 14px;

    }


    /* Inputs */

    .form-control,
    .form-select {

        border-radius: 10px;

        border: 1px solid #ddd;

        padding: 11px 14px;

    }


    .form-control:focus,
    .form-select:focus {

        border-color: var(--primary);

        box-shadow:
            0 0 0 0.2rem rgba(237, 128, 233, 0.20);

    }


    /* Bouton principal */

    .btn-primary-custom {

        background-color: var(--primary);

        border-color: var(--primary);

        color: white;

        font-weight: 600;

        border-radius: 10px;

        padding: 11px 22px;

    }


    .btn-primary-custom:hover {

        background-color: var(--primary-dark);

        border-color: var(--primary-dark);

        color: white;

    }


    /* Upload */

    .image-upload {

        border: 2px dashed #ddd;

        border-radius: 14px;

        padding: 30px;

        text-align: center;

        transition: 0.3s;

    }


    .image-upload:hover {

        border-color: var(--primary);

        background-color: #fff8ff;

    }


    .image-upload-icon {

        font-size: 45px;

        color: var(--primary);

    }


    .image-preview {

        max-width: 250px;

        max-height: 250px;

        border-radius: 12px;

        object-fit: cover;

        display: none;

        margin: 15px auto;

    }


    /* Retour */

    .btn-back {

        color: var(--dark);

        border: 1px solid #ddd;

        background-color: white;

        border-radius: 10px;

        padding: 10px 18px;

        text-decoration: none;

    }


    .btn-back:hover {

        background-color: #f5f5f5;

        color: var(--dark);

    }


    /* Responsive */

    @media (max-width: 768px) {

        .admin-content {

            padding: 20px;

        }

    }
    </style>

</head>


<body>


    <!--
|--------------------------------------------------------------------------
| Navbar
|--------------------------------------------------------------------------
-->

    <nav class="navbar admin-navbar">

        <div class="container-fluid px-4">

            <a href="../dashboard.php" class="navbar-brand">

                <i class="bi bi-shop"></i>

                E-Commerce Admin

            </a>


            <div class="d-flex align-items-center gap-3">

                <span class="admin-name">

                    <i class="bi bi-person-circle"></i>

                    <?= htmlspecialchars(
                    $_SESSION['admin']['prenom']
                    ?? $_SESSION['admin']['nom']
                    ?? 'Administrateur'
                ) ?>

                </span>


                <a href="../logout.php" class="btn btn-outline-light btn-sm">

                    <i class="bi bi-box-arrow-right"></i>

                    Déconnexion

                </a>

            </div>

        </div>

    </nav>


    <!--
|--------------------------------------------------------------------------
| Contenu
|--------------------------------------------------------------------------
-->

    <main class="admin-content">

        <div class="container">


            <!-- En-tête -->

            <div class="mb-4">

                <h1 class="page-title mb-1">

                    <i class="bi bi-plus-circle"></i>

                    Ajouter un produit

                </h1>

                <p class="page-subtitle">

                    Ajoutez un nouveau produit à votre boutique.

                </p>

            </div>


            <!--
        |--------------------------------------------------------------------------
        | Erreurs
        |--------------------------------------------------------------------------
        -->

            <?php if (!empty($errors)): ?>

            <div class="alert alert-danger" role="alert">

                <div class="fw-bold mb-2">

                    <i class="bi bi-exclamation-triangle"></i>

                    Impossible d'ajouter le produit

                </div>


                <ul class="mb-0">

                    <?php foreach (
                        $errors as $error
                    ): ?>

                    <li>

                        <?= htmlspecialchars(
                                $error
                            ) ?>

                    </li>

                    <?php endforeach; ?>

                </ul>

            </div>

            <?php endif; ?>


            <!--
        |--------------------------------------------------------------------------
        | Formulaire
        |--------------------------------------------------------------------------
        -->

            <form method="POST" enctype="multipart/form-data" class="form-card">


                <div class="row g-4">


                    <!-- Colonne principale -->

                    <div class="col-lg-8">


                        <!-- Nom -->

                        <div class="mb-4">

                            <label for="nom" class="form-label">

                                Nom du produit
                                <span class="text-danger">*</span>

                            </label>


                            <input type="text" id="nom" name="nom" class="form-control"
                                value="<?= htmlspecialchars($nom) ?>" maxlength="150"
                                placeholder="Exemple : iPhone 16 Pro" required>

                        </div>


                        <!-- Catégorie -->

                        <div class="mb-4">

                            <label for="id_categorie" class="form-label">

                                Catégorie
                                <span class="text-danger">*</span>

                            </label>


                            <select name="id_categorie" id="id_categorie" class="form-select" required>

                                <option value="">

                                    -- Sélectionner une catégorie --

                                </option>


                                <?php foreach (
                                $categories as $categorie
                            ): ?>

                                <?php

                                if (
                                    isset(
                                        $categorie['statut']
                                    ) &&
                                    $categorie['statut']
                                    !== 'active'
                                ) {

                                    continue;
                                }

                                ?>

                                <option value="<?= (int) $categorie['id_categorie'] ?>" <?= (
                                        $idCategorie ==
                                        $categorie['id_categorie']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                    <?= htmlspecialchars(
                                        $categorie['nom']
                                    ) ?>

                                </option>

                                <?php endforeach; ?>

                            </select>

                        </div>


                        <!-- Description -->

                        <div class="mb-4">

                            <label for="description" class="form-label">

                                Description

                            </label>


                            <textarea name="description" id="description" class="form-control" rows="6"
                                placeholder="Décrivez le produit..."><?= htmlspecialchars($description) ?></textarea>

                        </div>


                        <!-- Prix + Stock -->

                        <div class="row g-3">


                            <!-- Prix -->

                            <div class="col-md-6">

                                <label for="prix" class="form-label">

                                    Prix
                                    <span class="text-danger">*</span>

                                </label>


                                <div class="input-group">

                                    <input type="number" name="prix" id="prix" class="form-control"
                                        value="<?= htmlspecialchars($prix) ?>" min="0" step="0.01" placeholder="0"
                                        required>


                                    <span class="input-group-text">

                                        FCFA

                                    </span>

                                </div>

                            </div>


                            <!-- Stock -->

                            <div class="col-md-6">

                                <label for="stock" class="form-label">

                                    Stock
                                    <span class="text-danger">*</span>

                                </label>


                                <input type="number" name="stock" id="stock" class="form-control"
                                    value="<?= htmlspecialchars($stock) ?>" min="0" step="1" placeholder="0" required>

                            </div>

                        </div>


                        <!-- Statut -->

                        <div class="mt-4">

                            <label for="statut" class="form-label">

                                Statut

                            </label>


                            <select name="statut" id="statut" class="form-select">

                                <option value="disponible" <?= $statut === 'disponible'
                                    ? 'selected'
                                    : ''
                                ?>>

                                    Disponible

                                </option>


                                <option value="indisponible" <?= $statut === 'indisponible'
                                    ? 'selected'
                                    : ''
                                ?>>

                                    Indisponible

                                </option>


                                <option value="archive" <?= $statut === 'archive'
                                    ? 'selected'
                                    : ''
                                ?>>

                                    Archivé

                                </option>

                            </select>

                        </div>

                    </div>


                    <!-- Colonne image -->

                    <div class="col-lg-4">

                        <label class="form-label">

                            Image du produit

                        </label>


                        <div class="image-upload">


                            <i class="bi bi-cloud-arrow-up image-upload-icon"></i>


                            <h6 class="mt-3">

                                Ajouter une image

                            </h6>


                            <p class="text-muted small">

                                JPG, PNG, WEBP ou GIF
                                <br>
                                Maximum 5 Mo

                            </p>


                            <input type="file" name="image" id="image" class="form-control"
                                accept="image/jpeg,image/png,image/webp,image/gif">


                            <img src="" id="imagePreview" class="image-preview" alt="Aperçu">

                        </div>

                    </div>

                </div>


                <!--
            |--------------------------------------------------------------------------
            | Boutons
            |--------------------------------------------------------------------------
            -->

                <div class="d-flex justify-content-end gap-2 mt-5 pt-4 border-top">

                    <a href="index.php" class="btn-back">

                        <i class="bi bi-arrow-left"></i>

                        Annuler

                    </a>


                    <button type="submit" class="btn btn-primary-custom">

                        <i class="bi bi-check-lg"></i>

                        Ajouter le produit

                    </button>

                </div>


            </form>

        </div>

    </main>


    <!--
|--------------------------------------------------------------------------
| JavaScript
|--------------------------------------------------------------------------
-->

    <script>
    const imageInput =
        document.getElementById('image');

    const imagePreview =
        document.getElementById('imagePreview');


    imageInput.addEventListener(
        'change',
        function() {

            const file =
                this.files[0];


            if (!file) {

                imagePreview.style.display =
                    'none';

                imagePreview.src = '';

                return;
            }


            const reader =
                new FileReader();


            reader.onload =
                function(event) {

                    imagePreview.src =
                        event.target.result;

                    imagePreview.style.display =
                        'block';

                };


            reader.readAsDataURL(file);

        }
    );
    </script>


</body>

</html>