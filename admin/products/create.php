<?php

require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../models/Produit.php';
require_once __DIR__ . '/../../models/Categorie.php';

$pageTitle = 'Ajouter un produit';
$adminPage = 'products';


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

$categories = [];

$imageName = null;
$destination = null;


/*
|--------------------------------------------------------------------------
| Connexion à la base de données
|--------------------------------------------------------------------------
*/

try {

    $database = new Database();

    $db = $database->getConnection();

} catch (Throwable $e) {

    $errors[] =
        "Impossible de se connecter à la base de données : "
        . $e->getMessage();

    $db = null;
}


/*
|--------------------------------------------------------------------------
| Modèles
|--------------------------------------------------------------------------
*/

if ($db) {

    $produitModel = new Produit($db);

    $categorieModel = new Categorie($db);


    /*
    |--------------------------------------------------------------------------
    | Récupérer les catégories
    |--------------------------------------------------------------------------
    */

    try {

        $categories = $categorieModel->getAll();

        if (!is_array($categories)) {

            $categories = [];

        }

    } catch (Throwable $e) {

        $categories = [];

        $errors[] =
            "Impossible de récupérer les catégories : "
            . $e->getMessage();
    }

}


/*
|--------------------------------------------------------------------------
| Traitement du formulaire
|--------------------------------------------------------------------------
*/

if (
    $_SERVER['REQUEST_METHOD'] === 'POST'
    && $db
) {


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

    } elseif (!ctype_digit((string) $stock)) {

        $errors[] =
            "Le stock doit être un nombre entier positif.";
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
    | Vérification de l'image
    |--------------------------------------------------------------------------
    */

    if (
        isset($_FILES['image'])
        &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {


        $image = $_FILES['image'];


        /*
        |--------------------------------------------------------------------------
        | Erreur upload
        |--------------------------------------------------------------------------
        */

        if (
            $image['error'] !== UPLOAD_ERR_OK
        ) {

            $errors[] =
                "Une erreur est survenue lors de l'envoi de l'image.";

        } else {


            /*
            |--------------------------------------------------------------------------
            | Taille maximale : 5 Mo
            |--------------------------------------------------------------------------
            */

            if (
                $image['size'] > 5 * 1024 * 1024
            ) {

                $errors[] =
                    "L'image ne doit pas dépasser 5 Mo.";
            }


            /*
            |--------------------------------------------------------------------------
            | Vérification MIME
            |--------------------------------------------------------------------------
            */

            $finfo =
                new finfo(FILEINFO_MIME_TYPE);

            $mime =
                $finfo->file(
                    $image['tmp_name']
                );


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
            |--------------------------------------------------------------------------
            | Extensions
            |--------------------------------------------------------------------------
            */

            $extensions = [

                'image/jpeg' => 'jpg',
                'image/png'  => 'png',
                'image/webp' => 'webp',
                'image/gif'  => 'gif'

            ];


            /*
            |--------------------------------------------------------------------------
            | Génération du nom
            |--------------------------------------------------------------------------
            */

            if (
                empty($errors)
                &&
                isset($extensions[$mime])
            ) {

                $extension =
                    $extensions[$mime];


                $imageName =
                    'product_'
                    . date('Ymd_His')
                    . '_'
                    . bin2hex(
                        random_bytes(5)
                    )
                    . '.'
                    . $extension;
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
            | Vérifier la catégorie
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
                __DIR__
                . '/../../uploads/products/';


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
                    $uploadDirectory
                    . $imageName;


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
            | Insertion du produit
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

                )

                VALUES (

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
            | Supprimer l'image si erreur
            |--------------------------------------------------------------------------
            */

            if (
                $imageName !== null
                &&
                $destination !== null
                &&
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


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

require_once __DIR__ . '/../includes/header.php';

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
| BODY
|--------------------------------------------------------------------------
*/

body {

    background-color: var(--light);

    color: var(--text);

}


/*
|--------------------------------------------------------------------------
| CONTENU
|--------------------------------------------------------------------------
*/

.admin-content {

    padding: 35px 30px;

}


.page-title {

    color: var(--dark);

    font-size: 28px;

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
| CARTE FORMULAIRE
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
| LABELS
|--------------------------------------------------------------------------
*/

.form-label {

    color: var(--dark);

    font-size: 13px;

    font-weight: 600;

    margin-bottom: 8px;

}


/*
|--------------------------------------------------------------------------
| INPUTS
|--------------------------------------------------------------------------
*/

.form-control,
.form-select {

    border: 1px solid #e5e5e5;

    border-radius: 10px;

    padding: 11px 13px;

    font-size: 13px;

    color: var(--dark);

    box-shadow: none;

}


.form-control:focus,
.form-select:focus {

    border-color: var(--primary);

    box-shadow:
        0 0 0 .2rem rgba(237, 128, 233, .13);

}


/*
|--------------------------------------------------------------------------
| TEXTAREA
|--------------------------------------------------------------------------
*/

textarea.form-control {

    resize: vertical;

    min-height: 140px;

}


/*
|--------------------------------------------------------------------------
| INPUT GROUP
|--------------------------------------------------------------------------
*/

.input-group-text {

    background-color: #fafafa;

    border-color: #e5e5e5;

    color: #777;

    font-size: 13px;

}


/*
|--------------------------------------------------------------------------
| BOUTON PRINCIPAL
|--------------------------------------------------------------------------
*/

.btn-primary-custom {

    background-color: var(--primary);

    border: none;

    color: white;

    padding: 11px 18px;

    border-radius: 10px;

    font-size: 13px;

    font-weight: 600;

    box-shadow:
        0 5px 15px rgba(237, 128, 233, .25);

    transition: all .25s ease;

}


.btn-primary-custom:hover {

    background-color: var(--primary-dark);

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

    gap: 6px;

    padding: 11px 18px;

    border-radius: 10px;

    border: 1px solid #ddd;

    background: white;

    color: #555;

    text-decoration: none;

    font-size: 13px;

    font-weight: 600;

    transition: all .25s ease;

}


.btn-back:hover {

    background: #f5f5f5;

    color: var(--dark);

    transform: translateY(-1px);

}


/*
|--------------------------------------------------------------------------
| ALERTES
|--------------------------------------------------------------------------
*/

.alert {

    border: none;

    border-radius: 12px;

    font-size: 13px;

    box-shadow:
        0 5px 20px rgba(0, 0, 0, .04);

}


/*
|--------------------------------------------------------------------------
| UPLOAD IMAGE
|--------------------------------------------------------------------------
*/

.image-upload {

    border: 2px dashed #e3d0e2;

    border-radius: 16px;

    padding: 30px 20px;

    text-align: center;

    background:
        linear-gradient(180deg,
            #fff,
            #fffaff);

    transition: all .25s ease;

}


.image-upload:hover {

    border-color: var(--primary);

    background-color: #fff8ff;

}


.image-upload-icon {

    font-size: 48px;

    color: var(--primary);

}


.image-upload h6 {

    color: var(--dark);

    font-weight: 700;

}


.image-upload .form-control {

    font-size: 12px;

    background: white;

}


/*
|--------------------------------------------------------------------------
| APERÇU IMAGE
|--------------------------------------------------------------------------
*/

.image-preview {

    display: none;

    width: 100%;

    max-height: 250px;

    object-fit: contain;

    margin-top: 20px;

    border-radius: 12px;

    border: 1px solid #eee;

    background: #fafafa;

    padding: 5px;

}


/*
|--------------------------------------------------------------------------
| RESPONSIVE
|--------------------------------------------------------------------------
*/

@media (max-width: 991px) {

    .admin-content {

        padding: 25px 15px;

    }


    .form-card {

        padding: 20px;

    }

}


@media (max-width: 576px) {

    .page-title {

        font-size: 23px;

    }


    .form-card {

        padding: 18px;

        border-radius: 14px;

    }


    .btn-primary-custom,
    .btn-back {

        padding: 10px 13px;

    }

}
</style>


<!--
|--------------------------------------------------------------------------
| CONTENU
|--------------------------------------------------------------------------
-->

<main class="admin-content">

    <div class="container-fluid">

        <!-- En-tête -->

        <div class="mb-4">

            <h1 class="page-title mb-1">

                <i class="bi bi-plus-circle"></i>

                Ajouter un produit

            </h1>

            <p class="page-subtitle mb-0">

                Ajoutez un nouveau produit à votre boutique.

            </p>

        </div>


        <!--
        |--------------------------------------------------------------------------
        | ERREURS
        |--------------------------------------------------------------------------
        -->

        <?php if (!empty($errors)): ?>

        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">

            <div class="fw-bold mb-2">

                <i class="bi bi-exclamation-triangle me-1"></i>

                Impossible d'ajouter le produit

            </div>


            <ul class="mb-0 ps-3">

                <?php foreach ($errors as $error): ?>

                <li>

                    <?= htmlspecialchars(
                        $error,
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>

                </li>

                <?php endforeach; ?>

            </ul>


            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>

        </div>

        <?php endif; ?>


        <!--
        |--------------------------------------------------------------------------
        | FORMULAIRE
        |--------------------------------------------------------------------------
        -->

        <form method="POST" enctype="multipart/form-data" class="form-card">

            <div class="row g-4">


                <!--
                |--------------------------------------------------------------------------
                | COLONNE PRINCIPALE
                |--------------------------------------------------------------------------
                -->

                <div class="col-lg-8">


                    <!-- Nom -->

                    <div class="mb-4">

                        <label for="nom" class="form-label">

                            Nom du produit

                            <span class="text-danger">*</span>

                        </label>


                        <input type="text" id="nom" name="nom" class="form-control" value="<?= htmlspecialchars(
                                $nom,
                                ENT_QUOTES,
                                'UTF-8'
                            ) ?>" maxlength="150" placeholder="Exemple : iPhone 16 Pro" required>

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
                                $categories
                                as $categorie
                            ): ?>

                            <?php

                                if (
                                    isset(
                                        $categorie['statut']
                                    )
                                    &&
                                    $categorie['statut']
                                    !== 'active'
                                ) {

                                    continue;

                                }

                                ?>


                            <option value="<?= (int)
                                        $categorie['id_categorie']
                                    ?>" <?= (
                                        (int)$idCategorie
                                        ===
                                        (int)$categorie['id_categorie']
                                    )
                                        ? 'selected'
                                        : ''
                                    ?>>

                                <?= htmlspecialchars(
                                        $categorie['nom'],
                                        ENT_QUOTES,
                                        'UTF-8'
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
                            placeholder="Décrivez le produit..."><?= htmlspecialchars(
                            $description,
                            ENT_QUOTES,
                            'UTF-8'
                        ) ?></textarea>

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

                                <input type="number" name="prix" id="prix" class="form-control" value="<?= htmlspecialchars(
                                        $prix,
                                        ENT_QUOTES,
                                        'UTF-8'
                                    ) ?>" min="0" step="0.01" placeholder="0" required>


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


                            <input type="number" name="stock" id="stock" class="form-control" value="<?= htmlspecialchars(
                                    $stock,
                                    ENT_QUOTES,
                                    'UTF-8'
                                ) ?>" min="0" step="1" placeholder="0" required>

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


                <!--
                |--------------------------------------------------------------------------
                | IMAGE
                |--------------------------------------------------------------------------
                -->

                <div class="col-lg-4">

                    <label for="image" class="form-label">

                        Image du produit

                    </label>


                    <div class="image-upload">

                        <i class="bi bi-cloud-arrow-up image-upload-icon"></i>


                        <h6 class="mt-3">

                            Ajouter une image

                        </h6>


                        <p class="text-muted small mb-3">

                            JPG, PNG, WEBP ou GIF

                            <br>

                            Maximum 5 Mo

                        </p>


                        <input type="file" name="image" id="image" class="form-control"
                            accept="image/jpeg,image/png,image/webp,image/gif">


                        <img src="" id="imagePreview" class="image-preview" alt="Aperçu du produit">

                    </div>

                </div>

            </div>


            <!--
            |--------------------------------------------------------------------------
            | BOUTONS
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
| APERÇU IMAGE
|--------------------------------------------------------------------------
-->

<script>
const imageInput =
    document.getElementById('image');

const imagePreview =
    document.getElementById('imagePreview');


if (imageInput && imagePreview) {

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


            /*
            |--------------------------------------------------------------------------
            | Vérification côté client
            |--------------------------------------------------------------------------
            */

            if (
                file.size >
                5 * 1024 * 1024
            ) {

                alert(
                    'L’image ne doit pas dépasser 5 Mo.'
                );

                this.value = '';

                imagePreview.style.display =
                    'none';

                imagePreview.src = '';

                return;

            }


            const allowedTypes = [

                'image/jpeg',
                'image/png',
                'image/webp',
                'image/gif'

            ];


            if (
                !allowedTypes.includes(
                    file.type
                )
            ) {

                alert(
                    'Format d’image non autorisé.'
                );

                this.value = '';

                imagePreview.style.display =
                    'none';

                imagePreview.src = '';

                return;

            }


            /*
            |--------------------------------------------------------------------------
            | Aperçu
            |--------------------------------------------------------------------------
            */

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

}
</script>


<?php

require_once __DIR__ . '/../includes/footer.php';

?>