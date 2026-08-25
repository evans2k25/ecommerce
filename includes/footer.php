<style>
/* =========================================================
   FOOTER
========================================================= */

.site-footer {
    background: #1F1F29;
    color: #fff;
    margin-top: 60px;
}


/* =========================================================
   CONTENU
========================================================= */

.site-footer-content {
    padding: 55px 0 30px;
}

.site-footer h5 {
    color: #fff;
    font-size: 16px;
    font-weight: 700;
    margin-bottom: 18px;
}

.site-footer h5 i {
    color: #ED80E9;
    margin-right: 7px;
}


/* =========================================================
   DESCRIPTION
========================================================= */

.footer-description {
    color: #aaa;
    font-size: 13px;
    line-height: 1.8;
    max-width: 330px;
}


/* =========================================================
   LIENS
========================================================= */

.footer-links {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-links li {
    margin-bottom: 11px;
}

.footer-links a {
    display: inline-flex;
    align-items: center;
    gap: 8px;

    color: #aaa;
    text-decoration: none;

    font-size: 13px;

    transition: all .25s ease;
}

.footer-links a i {
    color: #ED80E9;
    font-size: 12px;
}

.footer-links a:hover {
    color: #ED80E9;
    transform: translateX(4px);
}


/* =========================================================
   CONTACT
========================================================= */

.footer-contact {
    list-style: none;
    padding: 0;
    margin: 0;
}

.footer-contact li {
    display: flex;
    align-items: flex-start;
    gap: 11px;

    color: #aaa;

    font-size: 13px;

    margin-bottom: 14px;
}

.footer-contact i {
    color: #ED80E9;
    font-size: 16px;

    min-width: 18px;
}


/* =========================================================
   RÉSEAUX SOCIAUX
========================================================= */

.footer-social {
    display: flex;
    gap: 9px;
    margin-top: 20px;
}

.footer-social a {
    width: 38px;
    height: 38px;

    display: inline-flex;
    align-items: center;
    justify-content: center;

    border-radius: 10px;

    background: rgba(237, 128, 233, .10);

    color: #ED80E9;

    text-decoration: none;

    font-size: 17px;

    transition: all .25s ease;
}

.footer-social a:hover {
    background: #ED80E9;
    color: #fff;

    transform: translateY(-3px);

    box-shadow: 0 7px 18px rgba(237, 128, 233, .25);
}


/* =========================================================
   SÉPARATION
========================================================= */

.footer-divider {
    border: 0;

    border-top: 1px solid rgba(255, 255, 255, .10);

    margin: 30px 0 20px;
}


/* =========================================================
   COPYRIGHT
========================================================= */

.footer-bottom {
    display: flex;
    justify-content: space-between;
    align-items: center;

    gap: 15px;

    color: #888;

    font-size: 12px;
}

.footer-bottom strong {
    color: #ED80E9;
    font-weight: 600;
}


/* =========================================================
   RESPONSIVE
========================================================= */

@media (max-width: 768px) {

    .site-footer-content {
        padding: 40px 0 25px;
    }

    .footer-bottom {
        flex-direction: column;
        text-align: center;
    }

}
</style>


<!-- =========================================================
     FOOTER
========================================================= -->

<footer class="site-footer">

    <div class="container site-footer-content">

        <div class="row g-4">


            <!-- =================================================
                 À PROPOS
            ================================================== -->

            <div class="col-lg-4 col-md-6">

                <h5>

                    <i class="bi bi-shop"></i>

                    Ecommerce

                </h5>

                <p class="footer-description">

                    Votre boutique en ligne pour acheter
                    facilement, rapidement et en toute
                    simplicité.

                </p>


                <!-- Réseaux sociaux -->

                <div class="footer-social">

                    <a href="#" title="Facebook">

                        <i class="bi bi-facebook"></i>

                    </a>


                    <a href="#" title="Instagram">

                        <i class="bi bi-instagram"></i>

                    </a>


                    <a href="#" title="WhatsApp">

                        <i class="bi bi-whatsapp"></i>

                    </a>


                    <a href="#" title="TikTok">

                        <i class="bi bi-tiktok"></i>

                    </a>

                </div>

            </div>


            <!-- =================================================
                 NAVIGATION
            ================================================== -->

            <div class="col-lg-4 col-md-6">

                <h5>

                    <i class="bi bi-compass"></i>

                    Navigation

                </h5>


                <ul class="footer-links">

                    <li>

                        <a href="<?= $baseUrl ?? '' ?>index.php">

                            <i class="bi bi-chevron-right"></i>

                            Accueil

                        </a>

                    </li>


                    <li>

                        <a href="<?= $baseUrl ?? '' ?>products.php">

                            <i class="bi bi-chevron-right"></i>

                            Produits

                        </a>

                    </li>


                    <li>

                        <a href="<?= $baseUrl ?? '' ?>categories.php">

                            <i class="bi bi-chevron-right"></i>

                            Catégories

                        </a>

                    </li>


                    <li>

                        <a href="<?= $baseUrl ?? '' ?>cart.php">

                            <i class="bi bi-chevron-right"></i>

                            Mon panier

                        </a>

                    </li>

                </ul>

            </div>


            <!-- =================================================
                 CONTACT
            ================================================== -->

            <div class="col-lg-4 col-md-6">

                <h5>

                    <i class="bi bi-headset"></i>

                    Contact

                </h5>


                <ul class="footer-contact">

                    <li>

                        <i class="bi bi-envelope"></i>

                        <span>
                            contact@example.com
                        </span>

                    </li>


                    <li>

                        <i class="bi bi-telephone"></i>

                        <span>
                            +225 00 00 00 00 00
                        </span>

                    </li>


                    <li>

                        <i class="bi bi-geo-alt"></i>

                        <span>
                            Abidjan, Côte d'Ivoire
                        </span>

                    </li>


                    <li>

                        <i class="bi bi-clock"></i>

                        <span>
                            Lun - Sam : 08h00 - 18h00
                        </span>

                    </li>

                </ul>

            </div>

        </div>


        <!-- =================================================
             SÉPARATION
        ================================================== -->

        <hr class="footer-divider">


        <!-- =================================================
             COPYRIGHT
        ================================================== -->

        <div class="footer-bottom">

            <div>

                © <?= date('Y') ?>

                <strong>Ecommerce</strong>.

                Tous droits réservés.

            </div>


            <div>

                <i class="bi bi-shield-check me-1"></i>

                Paiement sécurisé

            </div>

        </div>

    </div>

</footer>


<!-- =========================================================
     BOOTSTRAP JS
========================================================= -->

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>


<!-- =========================================================
     JS DU PROJET
========================================================= -->

<script src="<?= $baseUrl ?? '' ?>assets/js/app.js"></script>

</body>

</html>