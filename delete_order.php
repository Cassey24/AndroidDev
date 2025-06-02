<?php
include 'connect.php';

if (isset($_GET['orderid'])) {
    $orderid = intval($_GET['orderid']); // Sanitize input

    // Prepare the DELETE query
    $sql = "DELETE FROM orders WHERE orderid = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $orderid); // Bind the orderid to the query

    if ($stmt->execute()) {
        // If the deletion is successful, redirect back to the previous page
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit(); // Ensure script stops after redirect
    } else {
        echo "<script>alert('Error deleting order.'); window.location.reload();</script>";
    }

    $stmt->close(); // Close the statement
} else {
    // If the orderid is not passed, show an alert and reload the page
    echo "<script>alert('Invalid request.'); window.location.reload();</script>";
}
?>