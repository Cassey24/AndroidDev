<?php
session_start();
if (isset($_GET['product_id'])) {
    $productId = $_GET['product_id'];

    // Remove product from cart
    if (isset($_SESSION['cart'][$productId])) {
        unset($_SESSION['cart'][$productId]);
    }

    // Redirect back to cart with a success message
    header("Location: orders.php?message=removed");
    exit();
}
?>