<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Produit.php";
require_once __DIR__ . "/../../models/Categorie.php";

$database = new Database();
$db = $database->getConnection();
$produitModel = new Produit($db);
$categorieModel = new Categorie($db);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php?error=invalid_id");
    exit;
}

$produit = $produitModel->getById($id);

if (!$produit) {
    header("Location: index.php?error=not_found");
    exit;
}

$errors = [];
$nom = $produit['nom'];
$description = $produit['description'] ?? '';
$idCategorie = (int) $produit['id_categorie'];
$prix = $produit['prix'];
$stock = $produit['stock'];
$statut = $produit['statut'];
$imageActuelle = $produit['image'] ?? null;

try {
    $categories = $categorieModel->getAll();
} catch (Throwable $e) {
    $categories = [];
    $errors[] = "Impossible de récupérer les catégories.";
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $idCategorie = (int) ($_POST['id_categorie'] ?? 0);
    $prix = trim($_POST['prix'] ?? '');
    $stock = trim($_POST['stock'] ?? '');
    $statut = $_POST['statut'] ?? 'disponible';

    if ($nom === '') {
        $errors[] = "Le nom du produit est obligatoire.";
    }

    if ($idCategorie <= 0) {
        $errors[] = "Veuillez sélectionner une catégorie.";
    }

    if ($prix === '' || !is_numeric($prix) || (float) $prix < 0) {
        $errors[] = "Le prix est invalide.";
    }

    if ($stock === '' || !ctype_digit((string) $stock)) {
        $errors[] = "Le stock doit être un nombre entier positif.";
    }

    $statutsAutorises = ['disponible', 'indisponible', 'archive'];

    if (!in_array($statut, $statutsAutorises, true)) {
        $errors[] = "Le statut sélectionné est invalide.";
    }

    $imageName = $imageActuelle;

    if (
        isset($_FILES['image']) &&
        $_FILES['image']['error'] !== UPLOAD_ERR_NO_FILE
    ) {
        if ($_FILES['image']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Une erreur est survenue lors de l'envoi de l'image.";
        } else {
            $image = $_FILES['image'];

            if ($image['size'] > 5 * 1024 * 1024) {
                $errors[] = "L'image ne doit pas dépasser 5 Mo.";
            }

            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($image['tmp_name']);

            $extensions = [
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/webp' => 'webp',
                'image/gif' => 'gif'
            ];

            if (!isset($extensions[$mime])) {
                $errors[] = "Format d'image non autorisé.";
            } elseif (empty($errors)) {
                $imageName = 'product_' . date('Ymd_His') . '_'
                    . bin2hex(random_bytes(5)) . '.' . $extensions[$mime];
            }
        }
    }

    if (empty($errors)) {
        try {
            $uploadDirectory = __DIR__ . "/../../uploads/products/";

            if (!is_dir($uploadDirectory)) {
                mkdir($uploadDirectory, 0777, true);
            }

            if ($imageName !== $imageActuelle && $imageName !== null) {
                $destination = $uploadDirectory . $imageName;

                if (!move_uploaded_file($_FILES['image']['tmp_name'], $destination)) {
                    throw new Exception("Impossible d'enregistrer l'image.");
                }
            }

            $produitModel->update($id, [
                'id_categorie' => $idCategorie,
                'nom' => $nom,
                'description' => $description !== '' ? $description : null,
                'prix' => (float) $prix,
                'stock' => (int) $stock,
                'image' => $imageName,
                'statut' => $statut
            ]);

            if (
                $imageName !== $imageActuelle &&
                !empty($imageActuelle)
            ) {
                $oldPath = $uploadDirectory . $imageActuelle;
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }

            header("Location: index.php?success=updated");
            exit;
        } catch (Throwable $e) {
            $errors[] = "Impossible de modifier le produit : " . $e->getMessage();
        }
    }
}

$pageTitle = "Modifier un produit";
$adminPage = "products";

require_once __DIR__ . "/../includes/header.php";

?>

<?php if (!empty($errors)): ?>
<div class="alert alert-danger">
    <ul class="mb-0">
        <?php foreach ($errors as $error): ?>
        <li><?= htmlspecialchars($error) ?></li>
        <?php endforeach; ?>
    </ul>
</div>
<?php endif; ?>

<form method="POST" enctype="multipart/form-data" class="admin-card">
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="mb-3">
                <label class="form-label fw-semibold" for="nom">Nom *</label>
                <input type="text" id="nom" name="nom" class="form-control"
                    value="<?= htmlspecialchars($nom) ?>" required>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="id_categorie">Catégorie *</label>
                <select name="id_categorie" id="id_categorie" class="form-select" required>
                    <option value="">-- Sélectionner --</option>
                    <?php foreach ($categories as $categorie): ?>
                    <option value="<?= (int) $categorie['id_categorie'] ?>"
                        <?= $idCategorie == $categorie['id_categorie'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($categorie['nom']) ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold" for="description">Description</label>
                <textarea name="description" id="description" class="form-control" rows="5"><?= htmlspecialchars($description) ?></textarea>
            </div>

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="prix">Prix *</label>
                    <input type="number" name="prix" id="prix" class="form-control"
                        value="<?= htmlspecialchars((string) $prix) ?>" min="0" step="0.01" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold" for="stock">Stock *</label>
                    <input type="number" name="stock" id="stock" class="form-control"
                        value="<?= htmlspecialchars((string) $stock) ?>" min="0" required>
                </div>
            </div>

            <div class="mt-3">
                <label class="form-label fw-semibold" for="statut">Statut</label>
                <select name="statut" id="statut" class="form-select">
                    <option value="disponible" <?= $statut === 'disponible' ? 'selected' : '' ?>>Disponible</option>
                    <option value="indisponible" <?= $statut === 'indisponible' ? 'selected' : '' ?>>Indisponible</option>
                    <option value="archive" <?= $statut === 'archive' ? 'selected' : '' ?>>Archivé</option>
                </select>
            </div>
        </div>

        <div class="col-lg-4">
            <label class="form-label fw-semibold">Image</label>
            <?php if (!empty($imageActuelle)): ?>
            <img src="../../uploads/products/<?= htmlspecialchars($imageActuelle) ?>"
                class="img-fluid rounded mb-3" alt="Image actuelle">
            <?php endif; ?>
            <input type="file" name="image" class="form-control"
                accept="image/jpeg,image/png,image/webp,image/gif">
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary-custom">Enregistrer</button>
    </div>
</form>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
