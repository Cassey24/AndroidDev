<?php
include 'connect.php';

if (isset($_GET['id'])) { // match the GET parameter used in view_products.php
    $id = intval($_GET['id']); // Sanitize input

    // Prepare the DELETE query
    $sql = "DELETE FROM products WHERE product_id = ?";
    $stmt = $conn->prepare($sql);

    if ($stmt) {
        $stmt->bind_param("i", $id);

        if ($stmt->execute()) {
            // Redirect back to the referring page
            header("Location: view_products.php?deleted=1");
            exit();
        } else {
            echo "<script>alert('Error deleting product.'); window.location.href='view_products.php';</script>";
        }

        $stmt->close();
    } else {
        echo "<script>alert('Statement preparation failed.'); window.location.href='view_products.php';</script>";
    }
} else {
    echo "<script>alert('Invalid request.'); window.location.href='view_products.php';</script>";
}
?>