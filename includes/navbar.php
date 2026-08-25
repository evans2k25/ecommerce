<style>
.navbar {
    background: #ffffff !important;
}

.navbar-brand {
    color: #ED80E9 !important;
}

.navbar .nav-link:hover {
    color: #ED80E9 !important;
}

.btn-primary {
    background: #ED80E9 !important;
    border-color: #ED80E9 !important;
}

.btn-primary:hover {
    background: #C95BC5 !important;
    border-color: #C95BC5 !important;
}
</style>

<nav class="navbar navbar-expand-lg sticky-top">

    <div class="container">

        <!-- LOGO -->

        <a class="navbar-brand fw-bold" href="<?= $baseUrl ?? '' ?>index.php">

            <i class="bi bi-shop"></i>

            Ecommerce

        </a>


        <!-- BOUTON MOBILE -->

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar">

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- MENU -->

        <div class="collapse navbar-collapse" id="mainNavbar">

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">

                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>index.php">

                        Accueil

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>products.php">

                        Produits

                    </a>

                </li>


                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>categories.php">

                        Catégories

                    </a>

                </li>

            </ul>


            <!-- RECHERCHE -->

            <form class="d-flex me-3" action="<?= $baseUrl ?? '' ?>search.php" method="GET">

                <input type="search" name="q" class="form-control form-control-sm me-2" placeholder="Rechercher..."
                    aria-label="Rechercher">

                <button class="btn btn-outline-dark btn-sm" type="submit">

                    <i class="bi bi-search"></i>

                </button>

            </form>


            <!-- PANIER -->

            <a href="<?= $baseUrl ?? '' ?>cart.php" class="btn btn-panier position-relative">

                <i class="bi bi-cart3"></i>

                <span>Panier</span>

                <?php if ($cartCount > 0): ?>

                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill">

                    <?= $cartCount ?>

                </span>

                <?php endif; ?>

            </a>

        </div>

    </div>

</nav>