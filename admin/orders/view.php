<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Commande.php";
require_once __DIR__ . "/../../models/LigneCommande.php";
require_once __DIR__ . "/../../models/Livraison.php";
require_once __DIR__ . "/../../models/Paiement.php";

$database = new Database();
$db = $database->getConnection();

$commandeModel = new Commande($db);
$ligneModel = new LigneCommande($db);
$livraisonModel = new Livraison($db);
$paiementModel = new Paiement($db);

$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);

if (!$id) {
    header("Location: index.php");
    exit;
}

$commande = $commandeModel->getById($id);

if (!$commande) {
    header("Location: index.php");
    exit;
}

$success = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    try {
        if ($action === 'update_commande') {
            $commandeModel->updateStatus($id, $_POST['statut'] ?? '');
            $success = "Le statut de la commande a été mis à jour.";
        }

        if ($action === 'update_livraison') {
            $livraison = $livraisonModel->getByCommande($id);
            if ($livraison) {
                $livraisonModel->updateStatus(
                    (int) $livraison['id_livraison'],
                    $_POST['statut'] ?? ''
                );
                $success = "Le statut de la livraison a été mis à jour.";
            }
        }

        if ($action === 'update_paiement') {
            $paiement = $paiementModel->getByCommande($id);
            if ($paiement) {
                $paiementModel->updateStatus(
                    (int) $paiement['id_paiement'],
                    $_POST['statut'] ?? ''
                );
                $success = "Le statut du paiement a été mis à jour.";
            }
        }

        $commande = $commandeModel->getById($id);
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}

$lignes = $ligneModel->getByCommande($id);
$livraison = $livraisonModel->getByCommande($id);
$paiement = $paiementModel->getByCommande($id);

$pageTitle = "Commande " . $commande['numero_commande'];
$adminPage = "orders";

require_once __DIR__ . "/../includes/header.php";

?>

<a href="index.php" class="btn btn-outline-secondary mb-4">
    <i class="bi bi-arrow-left"></i> Retour
</a>

<?php if ($success): ?>
<div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="admin-card mb-4">
            <h5 class="fw-bold mb-3">Client</h5>
            <p class="mb-1">
                <strong><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></strong>
            </p>
            <p class="mb-1"><?= htmlspecialchars($commande['email']) ?></p>
            <p class="mb-0"><?= htmlspecialchars($commande['telephone']) ?></p>
        </div>

        <div class="admin-card">
            <h5 class="fw-bold mb-3">Produits</h5>
            <table class="table">
                <thead>
                    <tr>
                        <th>Produit</th>
                        <th>Qté</th>
                        <th>Prix</th>
                        <th>Sous-total</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($lignes as $ligne): ?>
                    <tr>
                        <td><?= htmlspecialchars($ligne['produit_nom']) ?></td>
                        <td><?= (int) $ligne['quantite'] ?></td>
                        <td><?= number_format((float) $ligne['prix_unitaire'], 0, ',', ' ') ?> FCFA</td>
                        <td><?= number_format((float) $ligne['sous_total'], 0, ',', ' ') ?> FCFA</td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <div class="text-end fw-bold fs-5">
                Total : <?= number_format((float) $commande['montant_total'], 0, ',', ' ') ?> FCFA
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="admin-card mb-4">
            <h5 class="fw-bold mb-3">Commande</h5>
            <p>Référence : <?= htmlspecialchars($commande['reference']) ?></p>
            <form method="POST">
                <input type="hidden" name="action" value="update_commande">
                <select name="statut" class="form-select mb-2">
                    <?php foreach (['en_attente','confirmee','preparee','expediee','livree','annulee'] as $s): ?>
                    <option value="<?= $s ?>" <?= $commande['statut'] === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-primary-custom w-100" type="submit">Mettre à jour</button>
            </form>
        </div>

        <div class="admin-card mb-4">
            <h5 class="fw-bold mb-3">Livraison</h5>
            <?php if ($livraison): ?>
            <p class="mb-1"><?= htmlspecialchars($livraison['adresse_livraison']) ?></p>
            <p class="mb-1"><?= htmlspecialchars($livraison['commune'] . ', ' . $livraison['ville']) ?></p>
            <p class="mb-3"><?= htmlspecialchars($livraison['telephone']) ?></p>
            <form method="POST">
                <input type="hidden" name="action" value="update_livraison">
                <select name="statut" class="form-select mb-2">
                    <?php foreach (['en_attente','en_preparation','expediee','livree','annulee'] as $s): ?>
                    <option value="<?= $s ?>" <?= $livraison['statut'] === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-dark w-100" type="submit">Mettre à jour</button>
            </form>
            <?php else: ?>
            <p class="text-muted mb-0">Aucune livraison.</p>
            <?php endif; ?>
        </div>

        <div class="admin-card">
            <h5 class="fw-bold mb-3">Paiement</h5>
            <?php if ($paiement): ?>
            <p class="mb-1">Mode : <?= htmlspecialchars($paiement['mode_paiement']) ?></p>
            <p class="mb-3">Montant : <?= number_format((float) $paiement['montant'], 0, ',', ' ') ?> FCFA</p>
            <form method="POST">
                <input type="hidden" name="action" value="update_paiement">
                <select name="statut" class="form-select mb-2">
                    <?php foreach (['en_attente','accepte','refuse','rembourse'] as $s): ?>
                    <option value="<?= $s ?>" <?= $paiement['statut'] === $s ? 'selected' : '' ?>>
                        <?= $s ?>
                    </option>
                    <?php endforeach; ?>
                </select>
                <button class="btn btn-outline-dark w-100" type="submit">Mettre à jour</button>
            </form>
            <?php else: ?>
            <p class="text-muted mb-0">Aucun paiement.</p>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . "/../includes/footer.php"; ?>
