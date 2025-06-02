<?php
session_start();
include 'connect.php';

if (isset($_POST['order_id']) && isset($_POST['status'])) {
    $orderId = $_POST['order_id'];
    $status = $_POST['status'];

    // Fetch current status and stock_deducted
    $statusSql = "SELECT status, stock_deducted FROM orders WHERE id = ?";
    $statusStmt = $conn->prepare($statusSql);
    $statusStmt->bind_param("i", $orderId);
    $statusStmt->execute();
    $statusResult = $statusStmt->get_result();

    if ($statusResult->num_rows > 0) {
        $statusRow = $statusResult->fetch_assoc();
        $currentStatus = $statusRow['status'];
        $stockDeducted = $statusRow['stock_deducted'];

        $statusStmt->close();

        // Deduct stock if changing to 'Preparing' and stock hasn't been deducted
        if ($status === "Preparing" && $stockDeducted == 0) {
            $itemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
            $itemsStmt = $conn->prepare($itemsSql);
            $itemsStmt->bind_param("i", $orderId);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();

            while ($item = $itemsResult->fetch_assoc()) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $updateStockSql = "UPDATE products SET Quantity = Quantity - ? WHERE product_id = ?";
                $stockStmt = $conn->prepare($updateStockSql);
                $stockStmt->bind_param("ii", $quantity, $productId);
                $stockStmt->execute();
                $stockStmt->close();
            }

            $itemsStmt->close();

            // Mark that stock has been deducted
            $markDeductedSql = "UPDATE orders SET stock_deducted = 1 WHERE id = ?";
            $markStmt = $conn->prepare($markDeductedSql);
            $markStmt->bind_param("i", $orderId);
            $markStmt->execute();
            $markStmt->close();
        }

        // Add back stock if changing to 'Cancelled' and stock *was* deducted
        if ($status === "Cancelled" && $stockDeducted == 1) {
            $itemsSql = "SELECT product_id, quantity FROM order_items WHERE order_id = ?";
            $itemsStmt = $conn->prepare($itemsSql);
            $itemsStmt->bind_param("i", $orderId);
            $itemsStmt->execute();
            $itemsResult = $itemsStmt->get_result();

            while ($item = $itemsResult->fetch_assoc()) {
                $productId = $item['product_id'];
                $quantity = $item['quantity'];

                $returnStockSql = "UPDATE products SET Quantity = Quantity + ? WHERE product_id = ?";
                $stockStmt = $conn->prepare($returnStockSql);
                $stockStmt->bind_param("ii", $quantity, $productId);
                $stockStmt->execute();
                $stockStmt->close();
            }

            $itemsStmt->close();

            // Update stock_deducted to 0 since stock was returned
            $markReturnedSql = "UPDATE orders SET stock_deducted = 0 WHERE id = ?";
            $markStmt = $conn->prepare($markReturnedSql);
            $markStmt->bind_param("i", $orderId);
            $markStmt->execute();
            $markStmt->close();
        }

        // Update the order status
        $updateSql = "UPDATE orders SET status = ? WHERE id = ?";
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param("si", $status, $orderId);

        if ($stmt->execute()) {
            echo json_encode(["status" => "success", "message" => "Order status updated successfully!", "reload" => true]);
        } else {
            echo json_encode(["status" => "error", "message" => "Error updating order status"]);
        }

        $stmt->close();
    } else {
        echo json_encode(["status" => "error", "message" => "Order not found."]);
    }

    $conn->close();
    exit();
}
?>