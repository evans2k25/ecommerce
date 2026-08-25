<?php

require_once __DIR__ . "/../includes/auth.php";
require_once __DIR__ . "/../../config/database.php";
require_once __DIR__ . "/../../models/Commande.php";

$database = new Database();
$db = $database->getConnection();
$commandeModel = new Commande($db);

try {
    $commandes = $commandeModel->getAll();
} catch (Throwable $e) {
    $commandes = [];
    $error = $e->getMessage();
}

function statutCommandeLabel(string $statut): array
{
    $statuts = [
        'en_attente' => ['En attente', 'warning'],
        'confirmee' => ['Confirmée', 'info'],
        'preparee' => ['Préparée', 'primary'],
        'expediee' => ['Expédiée', 'secondary'],
        'livree' => ['Livrée', 'success'],
        'annulee' => ['Annulée', 'danger']
    ];

    return $statuts[$statut] ?? [ucfirst($statut), 'secondary'];
}

$pageTitle = "Commandes";
$adminPage = "orders";

require_once __DIR__ . "/../includes/header.php";

?>

<?php if (!empty($error)): ?>
<div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php endif; ?>

<div class="admin-card p-0">
    <div class="table-responsive">
        <table class="table align-middle mb-0">
            <thead>
                <tr>
                    <th>N°</th>
                    <th>Client</th>
                    <th>Montant</th>
                    <th>Statut</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($commandes)): ?>
                <tr>
                    <td colspan="6" class="text-center py-5 text-muted">Aucune commande.</td>
                </tr>
                <?php else: ?>
                <?php foreach ($commandes as $commande): ?>
                <?php [$label, $class] = statutCommandeLabel($commande['statut']); ?>
                <tr>
                    <td class="fw-semibold">#<?= htmlspecialchars($commande['numero_commande']) ?></td>
                    <td><?= htmlspecialchars($commande['prenom'] . ' ' . $commande['nom']) ?></td>
                    <td><?= number_format((float) $commande['montant_total'], 0, ',', ' ') ?> FCFA</td>
                    <td><span class="badge text-bg-<?= $class ?>"><?= $label ?></span></td>
                    <td><?= date('d/m/Y H:i', strtotime($commande['date_commande'])) ?></td>
                    <td class="text-end">
                        <a href="view.php?id=<?= (int) $commande['id_commande'] ?>" class="btn btn-sm btn-outline-dark">
                            Détails
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
