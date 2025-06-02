<?php
session_start();
include 'connect.php';

// Check if the form was submitted
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_to_cart'])) {
    $productId = $_POST['add_to_cart']; // Get the product ID
    $quantity = isset($_POST['quantity'][$productId]) ? (int)$_POST['quantity'][$productId] : 0; // Ensure it's an integer

    // Debugging: Output the POST data to ensure it's coming through
    error_log("Product ID: " . $productId);
    error_log("Quantity: " . $quantity);

    if ($quantity > 0) {
        // Check if cart already exists in the session
        if (!isset($_SESSION['cart'])) {
            $_SESSION['cart'] = array();
        }

        // Add product to cart or update quantity
        if (isset($_SESSION['cart'][$productId])) {
            $_SESSION['cart'][$productId] += $quantity;
        } else {
            $_SESSION['cart'][$productId] = $quantity;
        }

        // Debugging: Output the cart contents to check the session state
        error_log("Cart contents: " . print_r($_SESSION['cart'], true));

        // Set a session variable for the success message
        $_SESSION['cart_message'] = "Item added to cart!";
    } else {
        $_SESSION['cart_message'] = "Invalid quantity! Please enter a valid quantity greater than 0.";
    }
}

// Redirect back to the product list page
header("Location: user.php");
exit();
?>