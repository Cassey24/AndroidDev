<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connect.php';

// Check if user is logged in
if (!isset($_SESSION['email'])) {
    die("Please log in to view your orders.");
}

$email = $_SESSION['email'];

// Fetch user details to check if they exist
$query = mysqli_prepare($conn, "SELECT user_id, firstName, lastName FROM users WHERE email = ?");
if (!$query) {
    die("Prepare statement failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($query, "s", $email);
if (!mysqli_stmt_execute($query)) {
    die("Execute statement failed: " . mysqli_error($conn));
}

$result = mysqli_stmt_get_result($query);
if (!$result) {
    die("Getting result failed: " . mysqli_error($conn));
}

$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

$userId = $user['user_id'];
$firstName = htmlspecialchars($user['firstName']);
$lastName = htmlspecialchars($user['lastName']);

// Handle order status update to "Order Received"
if (isset($_POST['order_received'])) {
    $orderIdToUpdate = $_POST['order_id'];
    $updateStatusQuery = "UPDATE orders SET status = 'Order Received' WHERE id = ?";
    $updateStmt = mysqli_prepare($conn, $updateStatusQuery);
    if (!$updateStmt) {
        die("Prepare statement failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param($updateStmt, "i", $orderIdToUpdate);
    if (!mysqli_stmt_execute($updateStmt)) {
        die("Execute statement failed: " . mysqli_error($conn));
    }
}

// Fetch the user's orders
$orderQuery = "SELECT * FROM orders WHERE user_id = ? ORDER BY created_at DESC";
$stmt = mysqli_prepare($conn, $orderQuery);
if (!$stmt) {
    die("Prepare statement failed: " . mysqli_error($conn));
}

mysqli_stmt_bind_param($stmt, "i", $userId);
if (!mysqli_stmt_execute($stmt)) {
    die("Execute statement failed: " . mysqli_error($conn));
}

$orderResult = mysqli_stmt_get_result($stmt);
if (!$orderResult) {
    die("Getting orders result failed: " . mysqli_error($conn));
}

if (mysqli_num_rows($orderResult) == 0) {
    echo '<div style="text-align: center; margin-top: 50px;">';
    echo "<h1>Welcome, $firstName $lastName</h1>";
    echo "<h2>Your Orders</h2>";
    echo "<p>No orders found.</p>";
    echo '</div>';
    exit;
}

$ongoingOrders = [];
$historyOrders = [];

while ($order = mysqli_fetch_assoc($orderResult)) {
    if ($order['status'] == "Order Received" || $order['status'] == "Cancelled") {
        $historyOrders[] = $order;
    } else {
        $ongoingOrders[] = $order;
    }
}
?>

<div class="orders-container">
    <div class="orders-column ongoing-orders">
        <h3>Ongoing Orders</h3>
        <?php
        foreach ($ongoingOrders as $order) {
            $orderId = $order['id'];
            $totalPrice = $order['total_price'];
            $paymentMethod = $order['payment_method'];
            $status = $order['status'];
            $createdAt = $order['created_at'];

            echo "<div class='order'>";
            echo "<div class='order-header'>";
            echo "<h4>Order #$orderId</h4>";
            echo "<p><strong>Status:</strong> $status</p>";
            echo "<button class='show-details-btn' onclick='toggleDetails($orderId)'>Show Details</button>";
            echo "</div>";
            echo "<div id='details-$orderId' class='order-details' style='display:none;'>";
            echo "<p><strong>Total Price:</strong> Php " . number_format($totalPrice, 2) . "</p>";
            echo "<p><strong>Payment Method:</strong> $paymentMethod</p>";
            echo "<p><strong>Order Date:</strong> " . date("F j, Y, g:i a", strtotime($createdAt)) . "</p>";

            $orderItemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
            $itemStmt = mysqli_prepare($conn, $orderItemsQuery);
            mysqli_stmt_bind_param($itemStmt, "i", $orderId);
            mysqli_stmt_execute($itemStmt);
            $itemResult = mysqli_stmt_get_result($itemStmt);

            if (mysqli_num_rows($itemResult) > 0) {
                echo "<h5>Order Items:</h5><table><tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
                while ($item = mysqli_fetch_assoc($itemResult)) {
                    echo "<tr><td>" . htmlspecialchars($item['product_name']) . "</td><td>{$item['quantity']}</td><td>Php " . number_format($item['price'], 2) . "</td><td>Php " . number_format($item['total'], 2) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No items found for this order.</p>";
            }

            if ($status == "Out for Delivery" || $status == "Ready for Pickup") {
                echo "<form method='post'><input type='hidden' name='order_id' value='$orderId'><button type='submit' name='order_received' class='mark-order-received-btn'>Mark as Order Received</button></form>";
            }

            echo "</div></div>";
        }
        ?>
    </div>

    <div class="orders-column history-orders">
        <h3>History Orders</h3>
        <?php
        foreach ($historyOrders as $order) {
            $orderId = $order['id'];
            $totalPrice = $order['total_price'];
            $paymentMethod = $order['payment_method'];
            $status = $order['status'];
            $createdAt = $order['created_at'];

            echo "<div class='order history'>";
            echo "<div class='order-header'>";
            echo "<h4>Order #$orderId</h4>";
            echo "<p><strong>Status:</strong> $status</p>";
            echo "<button class='show-details-btn' onclick='toggleDetails($orderId)'>Show Details</button>";
            echo "</div>";
            echo "<div id='details-$orderId' class='order-details' style='display:none;'>";
            echo "<p><strong>Total Price:</strong> Php " . number_format($totalPrice, 2) . "</p>";
            echo "<p><strong>Payment Method:</strong> $paymentMethod</p>";
            echo "<p><strong>Order Date:</strong> " . date("F j, Y, g:i a", strtotime($createdAt)) . "</p>";

            $orderItemsQuery = "SELECT * FROM order_items WHERE order_id = ?";
            $itemStmt = mysqli_prepare($conn, $orderItemsQuery);
            mysqli_stmt_bind_param($itemStmt, "i", $orderId);
            mysqli_stmt_execute($itemStmt);
            $itemResult = mysqli_stmt_get_result($itemStmt);

            if (mysqli_num_rows($itemResult) > 0) {
                echo "<h5>Order Items:</h5><table><tr><th>Product Name</th><th>Quantity</th><th>Price</th><th>Total</th></tr>";
                while ($item = mysqli_fetch_assoc($itemResult)) {
                    echo "<tr><td>" . htmlspecialchars($item['product_name']) . "</td><td>{$item['quantity']}</td><td>Php " . number_format($item['price'], 2) . "</td><td>Php " . number_format($item['total'], 2) . "</td></tr>";
                }
                echo "</table>";
            } else {
                echo "<p>No items found for this order.</p>";
            }

            echo "</div></div>";
        }
        ?>
    </div>
</div>

<!-- Back to User Page Button -->
<div style="text-align: center; margin-top: 40px;">
    <a href="user.php">
        <button style="
            background-color: #574964;
            color: white;
            padding: 10px 20px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        ">Back to Dashboard</button>
    </a>
</div>

<style>
.orders-container {
    display: flex;
    justify-content: space-between;
    gap: 20px;
    margin-top: 20px;
}
.orders-column {
    width: 48%;
    padding: 20px;
    border-radius: 8px;
    background-color: #FFDAB3;
    box-shadow: 0 0 10px rgba(87, 73, 100, 0.1);
}
.orders-column h3 {
    text-align: center;
    margin-bottom: 20px;
    color: #574964;
}
.order {
    margin-bottom: 20px;
    padding: 15px;
    border: 1px solid #C8AAAA;
    border-radius: 8px;
    background-color: #FFF5E1;
}
.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.show-details-btn {
    background-color: #574964;
    color: white;
    padding: 5px 10px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.show-details-btn:hover {
    background-color: #9F8383;
}
.order-details {
    margin-top: 10px;
    padding: 10px;
    background-color: #FFF0D9;
    border-radius: 5px;
    color: #574964;
}
.mark-order-received-btn {
    background-color: #574964;
    color: white;
    padding: 8px 15px;
    border: none;
    border-radius: 5px;
    cursor: pointer;
}
.mark-order-received-btn:hover {
    background-color: #9F8383;
}
.history-orders {
    width: 48%;
}
@media (max-width: 768px) {
    .orders-container {
        flex-direction: column;
    }
    .orders-column {
        width: 100%;
    }
}
</style>

<script>
function toggleDetails(orderId) {
    const details = document.getElementById(`details-${orderId}`);
    details.style.display = details.style.display === 'none' ? 'block' : 'none';
}
</script>
