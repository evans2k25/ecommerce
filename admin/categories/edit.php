<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Categorie.php";

$database = new Database();
$db = $database->getConnection();
$categorieModel = new Categorie($db);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$categorie = $categorieModel->getById($id);

if (!$categorie) {
    header("Location: index.php");
    exit;
}

$errors = [];
$nom = $categorie['nom'];
$description = $categorie['description'] ?? '';
$statut = $categorie['statut'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $statut = $_POST['statut'] ?? 'active';

    if ($nom === '') {
        $errors[] = "Le nom est obligatoire.";
    }

    if (!in_array($statut, ['active', 'inactive'], true)) {
        $errors[] = "Statut invalide.";
    }

    if (empty($errors)) {
        try {
            $categorieModel->update($id, [
                'nom' => $nom,
                'description' => $description !== '' ? $description : null,
                'image' => $categorie['image'] ?? null,
                'statut' => $statut
            ]);

            header("Location: index.php?success=updated");
            exit;
        } catch (Throwable $e) {
            $errors[] = "Impossible de modifier la catégorie : " . $e->getMessage();
        }
    }
}

$pageTitle = "Modifier une catégorie";
$adminPage = "categories";

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

<form method="POST" class="admin-card">
    <div class="mb-3">
        <label class="form-label fw-semibold" for="nom">Nom *</label>
        <input type="text" id="nom" name="nom" class="form-control"
            value="<?= htmlspecialchars($nom) ?>" required>
    </div>

    <div class="mb-3">
        <label class="form-label fw-semibold" for="description">Description</label>
        <textarea id="description" name="description" class="form-control" rows="4"><?= htmlspecialchars($description) ?></textarea>
    </div>

    <div class="mb-4">
        <label class="form-label fw-semibold" for="statut">Statut</label>
        <select id="statut" name="statut" class="form-select">
            <option value="active" <?= $statut === 'active' ? 'selected' : '' ?>>Active</option>
            <option value="inactive" <?= $statut === 'inactive' ? 'selected' : '' ?>>Inactive</option>
        </select>
    </div>

    <div class="d-flex justify-content-end gap-2">
        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary-custom">Enregistrer</button>
    </div>
</form>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
