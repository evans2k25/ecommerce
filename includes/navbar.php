<style>
/* =========================================================
   NAVBAR
========================================================= */

.navbar {
    background: #f5f5f5 !important;
    border-bottom: 1px solid #e6e6e6;
    box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
}

/* =========================================================
   LOGO
========================================================= */

.navbar-brand {
    color: #ED80E9 !important;
    font-weight: 700;
    transition: all 0.25s ease;
    display: flex;
    align-items: center;
    gap: 7px;
}

.navbar-brand:hover {
    color: #C95BC5 !important;
}

.navbar-brand i {
    font-size: 20px;
}


/* =========================================================
   LIENS DE NAVIGATION
========================================================= */

.navbar .nav-link {
    color: #444 !important;
    font-weight: 500;

    display: flex;
    align-items: center;
    gap: 5px;

    padding: 9px 12px;

    border-radius: 7px;

    transition: all 0.25s ease;
}

.navbar .nav-link i {
    font-size: 16px;
    transition: transform 0.25s ease;
}

.navbar .nav-link:hover {
    color: #ED80E9 !important;
    background: rgba(237, 128, 233, 0.08);
}

.navbar .nav-link:hover i {
    transform: translateY(-1px);
}


/* =========================================================
   BOUTONS PRINCIPAUX
========================================================= */

.btn-primary {
    background: #ED80E9 !important;
    border-color: #ED80E9 !important;
    color: #fff !important;
}

.btn-primary:hover {
    background: #C95BC5 !important;
    border-color: #C95BC5 !important;
}


/* =========================================================
   RECHERCHE
========================================================= */

.navbar .form-control {
    background: #ffffff;
    border: 1px solid #dcdcdc;
    color: #333;

    border-radius: 7px;
}

.navbar .form-control::placeholder {
    color: #999;
}

.navbar .form-control:focus {
    border-color: #ED80E9;

    box-shadow:
        0 0 0 0.2rem rgba(237, 128, 233, 0.15);
}


/* =========================================================
   BOUTON RECHERCHE
========================================================= */

.navbar .btn-outline-dark {
    border-color: #777;
    color: #444;

    border-radius: 7px;

    transition: all 0.25s ease;
}

.navbar .btn-outline-dark:hover {
    background: #ED80E9;
    border-color: #ED80E9;
    color: #fff;

    transform: translateY(-1px);
}

.navbar .btn-outline-dark i {
    font-size: 14px;
}


/* =========================================================
   BOUTON PANIER
========================================================= */

.btn-panier {
    display: inline-flex;
    align-items: center;
    justify-content: center;

    gap: 7px;

    background: #ED80E9 !important;
    border: 1px solid #ED80E9 !important;

    color: #fff !important;

    padding: 8px 15px;

    border-radius: 8px;

    font-weight: 600;

    transition: all 0.25s ease;

    text-decoration: none;
}

.btn-panier:hover {
    background: #C95BC5 !important;
    border-color: #C95BC5 !important;

    color: #fff !important;

    transform: translateY(-1px);

    box-shadow:
        0 5px 15px rgba(201, 91, 197, 0.25);
}

.btn-panier i {
    font-size: 17px;
}


/* =========================================================
   BADGE PANIER
========================================================= */

.btn-panier .badge {
    background: #1F1F29 !important;
    color: #fff !important;

    font-size: 10px;

    min-width: 20px;
    height: 20px;

    display: flex;

    align-items: center;
    justify-content: center;

    border: 2px solid #f5f5f5;

    padding: 0;
}


/* =========================================================
   BOUTON MOBILE
========================================================= */

.navbar-toggler {
    border-color: #d0d0d0;

    padding: 6px 9px;

    border-radius: 7px;
}

.navbar-toggler:focus {
    box-shadow:
        0 0 0 0.2rem rgba(237, 128, 233, 0.20);
}

.navbar-toggler-icon {
    filter: brightness(0.6);
}

/* Small logo sizing */
.navbar-logo {
    height: 44px;
    width: auto;
    display: inline-block;
    object-fit: contain;
    border-radius: 6px;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 991px) {

    .navbar .navbar-collapse {

        background: #f5f5f5;

        margin-top: 12px;

        padding: 15px;

        border-radius: 12px;

        border: 1px solid #e5e5e5;

        box-shadow:
            0 5px 15px rgba(0, 0, 0, 0.05);
    }


    .navbar .nav-link {

        padding: 10px;

        margin-bottom: 3px;
    }


    .navbar .nav-link i {

        width: 22px;

        text-align: center;
    }


    .navbar form {

        margin-top: 12px;

        margin-bottom: 12px;

        width: 100%;
    }


    .navbar form .form-control {

        flex: 1;
    }


    .btn-panier {

        margin-top: 5px;

        width: 100%;
    }

}


/* =========================================================
   PETITE ANIMATION DU PANIER
========================================================= */

.btn-panier:hover i {
    animation: cartBounce 0.4s ease;
}

@keyframes cartBounce {

    0% {
        transform: translateY(0);
    }

    40% {
        transform: translateY(-3px);
    }

    70% {
        transform: translateY(2px);
    }

    100% {
        transform: translateY(0);
    }

}
</style>


<nav class="navbar navbar-expand-lg sticky-top">

    <div class="container">

        <!-- =====================================================
             LOGO
        ====================================================== -->

        <a class="navbar-brand" href="<?= $baseUrl ?? '' ?>index.php">

            <img src="<?= ($baseUrl ?? '') . 'assets/images/logo.png' ?>" alt="Logo" class="navbar-logo">

            <span class="brand-text">MUSE MODERNE</span>

        </a>


        <!-- =====================================================
             BOUTON MOBILE
        ====================================================== -->

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar"
            aria-controls="mainNavbar" aria-expanded="false" aria-label="Afficher le menu">

            <span class="navbar-toggler-icon"></span>

        </button>


        <!-- =====================================================
             MENU
        ====================================================== -->

        <div class="collapse navbar-collapse" id="mainNavbar">


            <!-- =================================================
                 NAVIGATION
            ================================================== -->

            <ul class="navbar-nav me-auto mb-2 mb-lg-0">


                <!-- ACCUEIL -->

                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>index.php">

                        <i class="bi bi-house-door"></i>

                        <span>Accueil</span>

                    </a>

                </li>


                <!-- PRODUITS -->

                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>products.php">

                        <i class="bi bi-box-seam"></i>

                        <span>Produits</span>

                    </a>

                </li>


                <!-- CATÉGORIES -->

                <li class="nav-item">

                    <a class="nav-link" href="<?= $baseUrl ?? '' ?>categories.php">

                        <i class="bi bi-grid"></i>

                        <span>Catégories</span>

                    </a>

                </li>

            </ul>


            <!-- =================================================
                 RECHERCHE
            ================================================== -->

            <form class="d-flex me-3" action="<?= $baseUrl ?? '' ?>search.php" method="GET">

                <input type="search" name="q" class="form-control form-control-sm me-2" placeholder="Rechercher..."
                    aria-label="Rechercher">


                <button class="btn btn-outline-dark btn-sm" type="submit" title="Rechercher">

                    <i class="bi bi-search"></i>

                </button>

            </form>


            <!-- =================================================
                 PANIER
            ================================================== -->

            <a href="<?= $baseUrl ?? '' ?>cart.php" class="btn btn-panier position-relative">

                <i class="bi bi-cart3"></i>

                <span>Panier</span>


                <?php if ($cartCount > 0): ?>

                <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill">

                    <?= (int) $cartCount ?>

                </span>

                <?php endif; ?>

            </a>


        </div>

    </div>

</nav>