<?php

require_once __DIR__ . "/../models/Panier.php";

class CartController
{
    /**
     * Ajouter au panier
     */
    public function add(
        int $productId,
        int $quantity,
        float $prix
    ): void {

        Panier::add(
            $productId,
            $quantity,
            $prix
        );
    }


    /**
     * Modifier une quantité
     */
    public function update(
        int $productId,
        int $quantity
    ): void {

        Panier::update(
            $productId,
            $quantity
        );
    }


    /**
     * Supprimer
     */
    public function remove(int $productId): void
    {
        Panier::remove($productId);
    }


    /**
     * Vider
     */
    public function clear(): void
    {
        Panier::clear();
    }
}