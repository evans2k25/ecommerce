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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = trim($_POST['nom'] ?? '');
    $prenom = trim($_POST['prenom'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $motDePasse = $_POST['mot_de_passe'] ?? '';
    $role = $_POST['role'] ?? 'admin';
    $statut = $_POST['statut'] ?? 'actif';

    if ($nom === '' || $prenom === '') {
        $errors[] = "Le nom et le prénom sont obligatoires.";
    }

    if ($email === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "L'adresse email est invalide.";
    } elseif ($adminModel->findByEmail($email)) {
        $errors[] = "Cet email est déjà utilisé.";
    }

    if (strlen($motDePasse) < 6) {
        $errors[] = "Le mot de passe doit contenir au moins 6 caractères.";
    }

    if (!in_array($role, ['super_admin', 'admin', 'gestionnaire'], true)) {
        $errors[] = "Rôle invalide.";
    }

    if (!in_array($statut, ['actif', 'inactif'], true)) {
        $errors[] = "Statut invalide.";
    }

    if (empty($errors)) {
        try {
            $adminModel->create([
                'nom' => $nom,
                'prenom' => $prenom,
                'email' => $email,
                'mot_de_passe' => $motDePasse,
                'role' => $role,
                'statut' => $statut
            ]);

            header("Location: index.php?success=created");
            exit;
        } catch (Throwable $e) {
            $errors[] = $e->getMessage();
        }
    }
}

$pageTitle = "Ajouter un administrateur";
$adminPage = "admins";

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
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="prenom">Prénom *</label>
            <input type="text" id="prenom" name="prenom" class="form-control"
                value="<?= htmlspecialchars($prenom) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="nom">Nom *</label>
            <input type="text" id="nom" name="nom" class="form-control"
                value="<?= htmlspecialchars($nom) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="email">Email *</label>
            <input type="email" id="email" name="email" class="form-control"
                value="<?= htmlspecialchars($email) ?>" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="mot_de_passe">Mot de passe *</label>
            <input type="password" id="mot_de_passe" name="mot_de_passe" class="form-control" required>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="role">Rôle</label>
            <select id="role" name="role" class="form-select">
                <option value="admin" <?= $role === 'admin' ? 'selected' : '' ?>>Admin</option>
                <option value="gestionnaire" <?= $role === 'gestionnaire' ? 'selected' : '' ?>>Gestionnaire</option>
                <option value="super_admin" <?= $role === 'super_admin' ? 'selected' : '' ?>>Super admin</option>
            </select>
        </div>
        <div class="col-md-6">
            <label class="form-label fw-semibold" for="statut">Statut</label>
            <select id="statut" name="statut" class="form-select">
                <option value="actif" <?= $statut === 'actif' ? 'selected' : '' ?>>Actif</option>
                <option value="inactif" <?= $statut === 'inactif' ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
    </div>

    <div class="d-flex justify-content-end gap-2 mt-4">
        <a href="index.php" class="btn btn-outline-secondary">Annuler</a>
        <button type="submit" class="btn btn-primary-custom">Créer</button>
    </div>
</form>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
