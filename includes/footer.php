<footer class="bg-dark text-white mt-5">

    <div class="container py-5">

        <div class="row g-4">

            <div class="col-md-4">

                <h5 class="fw-bold">
                    <i class="bi bi-shop"></i>
                    Ecommerce
                </h5>

                <p class="text-secondary">

                    Votre boutique en ligne pour acheter
                    facilement et rapidement.

                </p>

            </div>


            <div class="col-md-4">

                <h5>Navigation</h5>

                <ul class="list-unstyled">

                    <li class="mb-2">

                        <a href="<?= $baseUrl ?? '' ?>index.php" class="text-secondary text-decoration-none">
                            Accueil
                        </a>

                    </li>

                    <li class="mb-2">

                        <a href="<?= $baseUrl ?? '' ?>products.php" class="text-secondary text-decoration-none">
                            Produits
                        </a>

                    </li>

                    <li class="mb-2">

                        <a href="<?= $baseUrl ?? '' ?>categories.php" class="text-secondary text-decoration-none">
                            Catégories
                        </a>

                    </li>

                </ul>

            </div>


            <div class="col-md-4">

                <h5>Contact</h5>

                <p class="text-secondary mb-2">

                    <i class="bi bi-envelope"></i>

                    contact@example.com

                </p>

                <p class="text-secondary">

                    <i class="bi bi-telephone"></i>

                    +225 00 00 00 00 00

                </p>

            </div>

        </div>

        <hr class="border-secondary">

        <div class="text-center text-secondary">

            © <?= date('Y') ?> Ecommerce.
            Tous droits réservés.

        </div>

    </div>

</footer>


<!-- Bootstrap JS -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS du projet -->

<script src="<?= $baseUrl ?? '' ?>assets/js/app.js"></script>

</body>

</html>