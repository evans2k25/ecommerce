<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Administrateur.php";

$database = new Database();
$db = $database->getConnection();
$adminModel = new Administrateur($db);

$error = '';
$success = '';

if (isset($_GET['delete']) && ctype_digit($_GET['delete'])) {
    $deleteId = (int) $_GET['delete'];

    if ($deleteId === (int) ($_SESSION['admin']['id'] ?? 0)) {
        $error = "Vous ne pouvez pas supprimer votre propre compte.";
    } else {
        try {
            $adminModel->delete($deleteId);
            header("Location: index.php?success=deleted");
            exit;
        } catch (Throwable $e) {
            $error = $e->getMessage();
        }
    }
}

try {
    $admins = $adminModel->getAll();
} catch (Throwable $e) {
    $admins = [];
    $error = $e->getMessage();
}

if (isset($_GET['success'])) {
    $messages = [
        'created' => "Administrateur créé.",
        'deleted' => "Administrateur supprimé."
    ];
    $success = $messages[$_GET['success']] ?? $success;
}

$pageTitle = "Administrateurs";
$adminPage = "admins";

require_once __DIR__ . "/../includes/header.php";

?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <p class="text-muted mb-0">Comptes ayant accès au back-office.</p>
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
                    <th>Nom</th>
                    <th>Email</th>
                    <th>Rôle</th>
                    <th>Statut</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($admins as $item): ?>
                <tr>
                    <td><?= htmlspecialchars($item['prenom'] . ' ' . $item['nom']) ?></td>
                    <td><?= htmlspecialchars($item['email']) ?></td>
                    <td><?= htmlspecialchars($item['role']) ?></td>
                    <td>
                        <span class="badge text-bg-<?= $item['statut'] === 'actif' ? 'success' : 'secondary' ?>">
                            <?= htmlspecialchars($item['statut']) ?>
                        </span>
                    </td>
                    <td class="text-end">
                        <?php if ((int) $item['id_admin'] !== (int) ($_SESSION['admin']['id'] ?? 0)): ?>
                        <a href="index.php?delete=<?= (int) $item['id_admin'] ?>"
                            class="btn btn-sm btn-outline-danger"
                            onclick="return confirm('Supprimer cet administrateur ?');">
                            Supprimer
                        </a>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
