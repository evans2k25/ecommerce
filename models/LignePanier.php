<?php

class LignePanier
{
    public static function normalize(array $cart): array
    {
        $normalized = [];

        foreach ($cart as $productId => $item) {
            if (!is_array($item)) {
                continue;
            }

            $quantity = (int) ($item['quantity'] ?? 0);
            if ($quantity <= 0) {
                continue;
            }

            $normalized[(int) $productId] = [
                'quantity' => $quantity,
                'prix' => (float) ($item['prix'] ?? 0),
            ];
        }

        return $normalized;
    }

    public static function count(array $cart): int
    {
        $count = 0;

        foreach (self::normalize($cart) as $item) {
            $count += (int) $item['quantity'];
        }

        return $count;
    }

    public static function subtotal(array $cart): float
    {
        $total = 0.0;

        foreach (self::normalize($cart) as $item) {
            $total += (float) $item['prix'] * (int) $item['quantity'];
        }

        return $total;
    }

    public static function hydrate(array $cart, array $products): array
    {
        $items = [];

        foreach (self::normalize($cart) as $productId => $item) {
            $product = $products[$productId] ?? null;

            if (!$product) {
                continue;
            }

            $quantity = (int) $item['quantity'];
            $price = (float) ($product['prix'] ?? $item['prix']);

            $items[] = [
                'id_produit' => (int) $productId,
                'nom' => $product['nom'] ?? 'Produit',
                'quantity' => $quantity,
                'prix' => $price,
                'subtotal' => $price * $quantity,
            ];
        }

        return $items;
    }
}
