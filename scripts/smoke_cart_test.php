<?php

require_once __DIR__ . "/../models/Panier.php";

// Clear any existing session data
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}

// Start fresh
Panier::init();
Panier::clear();

// Add items
Panier::add(1, 2, 1000.0); // product 1: 2 x 1000
Panier::add(2, 1, 2500.0); // product 2: 1 x 2500

$items = Panier::getItems();
$count = Panier::getCount();
$subtotal = Panier::getSubtotal();

$expectedCount = 3;
$expectedSubtotal = (2 * 1000.0) + (1 * 2500.0);

$ok = true;
$messages = [];

if ($count !== $expectedCount) {
    $ok = false;
    $messages[] = "Count mismatch: got $count expected $expectedCount";
}

if (abs($subtotal - $expectedSubtotal) > 0.0001) {
    $ok = false;
    $messages[] = "Subtotal mismatch: got $subtotal expected $expectedSubtotal";
}

if (!isset($items[1]) || $items[1]['quantity'] !== 2) {
    $ok = false;
    $messages[] = "Item 1 missing or wrong quantity";
}

if (!isset($items[2]) || $items[2]['quantity'] !== 1) {
    $ok = false;
    $messages[] = "Item 2 missing or wrong quantity";
}

if ($ok) {
    echo "SMOKE TEST PASS: Panier works as expected\n";
    echo "Count: $count, Subtotal: $subtotal\n";
    exit(0);
} else {
    echo "SMOKE TEST FAIL:\n" . implode("\n", $messages) . "\n";
    echo "Count: $count, Subtotal: $subtotal\n";
    exit(1);
}
