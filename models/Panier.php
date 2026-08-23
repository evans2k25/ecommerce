<?php

class Panier
{
    /**
     * Initialiser le panier
     */
    public static function init(): void
    {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = [];
        }
    }


    /**
     * Ajouter un produit au panier
     */
    public static function add(
        int $productId,
        int $quantity,
        float $price
    ): void {

        self::init();

        if ($quantity < 1) {
            $quantity = 1;
        }

        if (isset($_SESSION['cart'][$productId])) {

            $_SESSION['cart'][$productId]['quantity'] += $quantity;

        } else {

            $_SESSION['cart'][$productId] = [
                'quantity' => $quantity,
                'price' => $price
            ];
        }
    }


    /**
     * Modifier la quantité
     */
    public static function update(
        int $productId,
        int $quantity
    ): void {

        self::init();

        if (!isset($_SESSION['cart'][$productId])) {
            return;
        }

        if ($quantity <= 0) {

            self::remove($productId);

            return;
        }

        $_SESSION['cart'][$productId]['quantity'] = $quantity;
    }


    /**
     * Supprimer un produit
     */
    public static function remove(int $productId): void
    {
        self::init();

        unset($_SESSION['cart'][$productId]);
    }


    /**
     * Vider le panier
     */
    public static function clear(): void
    {
        self::init();

        $_SESSION['cart'] = [];
    }


    /**
     * Nombre total de produits
     */
    public static function getCount(): int
    {
        self::init();

        $count = 0;

        foreach ($_SESSION['cart'] as $item) {

            $count += (int) $item['quantity'];
        }

        return $count;
    }


    /**
     * Sous-total du panier
     */
    public static function getSubtotal(): float
    {
        self::init();

        $subtotal = 0;

        foreach ($_SESSION['cart'] as $item) {

            $subtotal +=
                $item['price'] * $item['quantity'];
        }

        return $subtotal;
    }


    /**
     * Récupérer le panier
     */
    public static function getItems(): array
    {
        self::init();

        return $_SESSION['cart'];
    }


    /**
     * Vérifier si le panier est vide
     */
    public static function isEmpty(): bool
    {
        self::init();

        return empty($_SESSION['cart']);
    }
}