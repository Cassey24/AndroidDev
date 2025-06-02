<?php
session_start();
include 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    header("Location: connect.php");
    exit();
}

// Check if order_id is provided
if (!isset($_GET['order_id'])) {
    header("Location: index.php");
    exit();
}

$orderId = $_GET['order_id'];
$email = $_SESSION['email'];

// Get user ID from email
$userQuery = $conn->prepare("SELECT id FROM users WHERE email = ?");
$userQuery->bind_param("s", $email);
$userQuery->execute();
$userResult = $userQuery->get_result();
$userRow = $userResult->fetch_assoc();

if (!$userRow) {
    echo "<script>alert('User not found.'); window.location.href='index.php';</script>";
    exit();
}

$userId = $userRow['id'];

// Fetch order details to verify it belongs to the logged-in user
$orderQuery = $conn->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
$orderQuery->bind_param("ii", $orderId, $userId);
$orderQuery->execute();
$orderResult = $orderQuery->get_result();

if ($orderResult->num_rows === 0) {
    echo "<script>alert('Order not found or access denied.'); window.location.href='index.php';</script>";
    exit();
}

$orderData = $orderResult->fetch_assoc();

// Fetch order items
$itemsQuery = $conn->prepare("
    SELECT oi.*, p.ProductName, p.Brand 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$itemsQuery->bind_param("i", $orderId);
$itemsQuery->execute();
$itemsResult = $itemsQuery->get_result();
$orderItems = [];

while ($item = $itemsResult->fetch_assoc()) {
    $orderItems[] = $item;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Order Success</title>
    <style>
    body {
        font-family: Arial, sans-serif;
        background-color: #FFDAB3;
        margin: 0;
        padding: 0;
        color: #574964;
    }
    .container {
        max-width: 800px;
        margin: 0 auto;
        padding: 20px;
        text-align: center;
        background-color: #fff;
        border-radius: 10px;
        box-shadow: 0 0 10px rgba(87, 73, 100, 0.1);
    }
    h1, h2 {
        color: #574964;
    }
    .success-icon {
        color: #4CAF50;
        font-size: 60px;
        margin: 20px 0;
    }
    .order-details {
        margin: 20px 0;
        text-align: left;
        padding: 15px;
        background-color: #f9f9f9;
        border-radius: 5px;
    }
    table {
        width: 100%;
        border-collapse: collapse;
        margin-bottom: 20px;
        background-color: #fff;
    }
    th, td {
        padding: 10px;
        border: 1px solid #C8AAAA;
        text-align: left;
    }
    th {
        background-color: #9F8383;
        color: white;
    }
    .buttons {
        margin-top: 30px;
    }
    .btn {
        padding: 10px 20px;
        margin: 0 10px;
        background-color: #574964;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        text-decoration: none;
        display: inline-block;
        transition: background-color 0.3s ease;
    }
    .btn:hover {
        background-color: #9F8383;
    }
</style>
</head>
<body>
    <div class="container">
        <div class="success-icon">✓</div>
        <h1>Order Placed Successfully!</h1>
        <p>Thank you for your order. Your order has been received and is being processed.</p>
        
        <div class="order-details">
            <h2>Order Summary</h2>
            <p><strong>Order ID:</strong> <?php echo htmlspecialchars($orderId); ?></p>
            <p><strong>Order Date:</strong> <?php echo htmlspecialchars(date('F j, Y, g:i a', strtotime($orderData['order_date']))); ?></p>
            <p><strong>Payment Method:</strong> <?php echo htmlspecialchars($orderData['payment_method']); ?></p>
            <p><strong>Order Status:</strong> <?php echo htmlspecialchars($orderData['status']); ?></p>
            <p><strong>Total Amount:</strong> ₱<?php echo number_format($orderData['total_amount'], 2); ?></p>
        </div>
        
        <h2>Ordered Items</h2>
        <table>
            <tr>
                <th>Product</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($orderItems as $item): ?>
            <tr>
                <td><?php echo htmlspecialchars($item['ProductName']); ?></td>
                <td><?php echo htmlspecialchars($item['Brand']); ?></td>
                <td>₱<?php echo number_format($item['price'], 2); ?></td>
                <td><?php echo htmlspecialchars($item['quantity']); ?></td>
                <td>₱<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>
        
        <div class="buttons">
            <a href="index.php" class="btn">Continue Shopping</a>
            <a href="view_orders.php" class="btn">View My Orders</a>
        </div>
    </div>
</body>
</html>