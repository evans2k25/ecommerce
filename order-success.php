<?php

session_start();

$pageTitle = "Commande confirmée";

$baseUrl = "";

$orderNumber =
    $_SESSION['order_number'] ?? null;

unset($_SESSION['order_number']);

require_once __DIR__ . "/includes/header.php";

?>

<div class="container py-5">

    <div class="row justify-content-center">

        <div class="col-lg-7">

            <div class="card border-0 shadow-sm text-center">

                <div class="card-body p-5">

                    <div class="mb-4">

                        <i class="bi bi-check-circle-fill text-success" style="font-size:80px;"></i>

                    </div>


                    <h1 class="fw-bold">

                        Commande confirmée !

                    </h1>


                    <p class="lead text-muted mt-3">

                        Merci pour votre commande.

                    </p>


                    <?php if ($orderNumber): ?>

                    <div class="alert alert-light border mt-4">

                        <span class="text-muted">
                            Numéro de commande
                        </span>

                        <br>

                        <strong class="fs-4">

                            <?= htmlspecialchars(
                                    $orderNumber
                                ) ?>

                        </strong>

                    </div>

                    <?php endif; ?>


                    <p class="text-muted">

                        Vous recevrez les informations relatives
                        à votre commande sur votre adresse email.

                    </p>


                    <a href="products.php" class="btn btn-dark mt-3">

                        Continuer mes achats

                    </a>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

require_once __DIR__ . "/includes/footer.php";

?>