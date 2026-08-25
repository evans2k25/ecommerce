<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Categorie.php";

$database = new Database();
$db = $database->getConnection();
$categorieModel = new Categorie($db);

$error = '';
$success = '';

try {
    $categories = $categorieModel->getAllAdmin();
} catch (Throwable $e) {
    $categories = [];
    $error = $e->getMessage();
}

if (isset($_GET['success'])) {
    $messages = [
        'created' => "La catégorie a été créée.",
        'updated' => "La catégorie a été modifiée.",
        'deleted' => "La catégorie a été supprimée."
    ];
    $success = $messages[$_GET['success']] ?? '';
}

if (isset($_GET['error'])) {
    $error = $_GET['error'] === 'has_products'
        ? "Impossible de supprimer une catégorie qui contient encore des produits."
        : htmlspecialchars($_GET['error']);
}

$pageTitle = "Catégories";
$adminPage = "categories";

require_once __DIR__ . "/../includes/header.php";

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Gérez les catégories de la boutique.</p>
    <a href="create.php" class="btn btn-primary-custom">
        <i class="bi bi-plus-lg"></i> Ajouter
    </a>
</div>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nom</th>
                    <th>Produits</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($categories)): ?>
                <tr>
                    <td colspan="5" class="text-center py-5 text-muted">Aucune catégorie.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($categories as $categorie): ?>
                <tr>
                    <td><?= (int) $categorie['id_categorie'] ?></td>
                    <td class="fw-semibold"><?= htmlspecialchars($categorie['nom']) ?></td>
                    <td><?= (int) $categorie['total_products'] ?></td>
                    <td>
                        <span class="badge text-bg-<?= $categorie['statut'] === 'active' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($categorie['statut']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <a href="edit.php?id=<?= (int) $categorie['id_categorie'] ?>" class="btn btn-sm btn-outline-primary">
                            Modifier
                        </a>
                        <a href="delete.php?id=<?= (int) $categorie['id_categorie'] ?>"
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Supprimer cette catégorie ?');">
                            Supprimer
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
