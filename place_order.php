<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();
include 'connect.php';

// ✅ Step 1: Check if the cart exists and has products
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    die("No items in the cart. Go back and add products.");
}

// ✅ Step 2: Fetch user data
$email = $_SESSION['email'] ?? '';
if (empty($email)) {
    die("User is not logged in.");
}

$query = mysqli_prepare($conn, "SELECT user_id, firstName, lastName, address, contact_no FROM users WHERE email = ?");
if (!$query) {
    die("User lookup prepare failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($query, "s", $email);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);
$user = mysqli_fetch_assoc($result);

if (!$user) {
    die("User not found.");
}

$firstName = htmlspecialchars($user['firstName']);
$lastName = htmlspecialchars($user['lastName']);
$address = htmlspecialchars($user['address']);
$contactNo = htmlspecialchars($user['contact_no']);
$userId = $user['user_id'];

// ✅ Step 3: Calculate total price and prepare orders array
$totalPrice = 0;
$orders = [];

foreach ($_SESSION['cart'] as $productId => $quantity) {
    $stmt = mysqli_prepare($conn, "SELECT product_id, ProductName, Price FROM products WHERE product_id = ?");
    if (!$stmt) {
        die("Product lookup prepare failed: " . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmt, "i", $productId);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $product = mysqli_fetch_assoc($result);

    if ($product) {
        $price = (float)$product['Price'];
        $total = $price * $quantity;

        $orders[] = [
            'product_id' => $product['product_id'],
            'name'       => $product['ProductName'],
            'price'      => $price,
            'quantity'   => $quantity,
            'total'      => $total
        ];

        $totalPrice += $total;
    } else {
        die("Product with ID $productId not found in database.");
    }
}

if (empty($orders)) {
    die("No valid products found in cart.");
}

// ✅ Step 4: Get selected payment method
$paymentMethod = $_POST['payment_method'] ?? 'COD';

// ✅ Step 5: Insert into `orders` table
$insertQuery = "INSERT INTO orders (user_id, first_name, last_name, email, address, contact_no, payment_method, total_price, created_at, status, stock_deducted) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), 'Pending', 'No')";
$stmt = mysqli_prepare($conn, $insertQuery);
if (!$stmt) {
    die("Order insert prepare failed: " . mysqli_error($conn));
}
mysqli_stmt_bind_param($stmt, "sssssssd", $userId, $firstName, $lastName, $email, $address, $contactNo, $paymentMethod, $totalPrice);
mysqli_stmt_execute($stmt);

// ✅ Step 6: Get the order ID
$orderId = mysqli_insert_id($conn);
if (!$orderId) {
    die("Failed to create order.");
}

// ✅ Step 7: Insert order items (🚫 Removed stock deduction)
foreach ($orders as $item) {
    $insertItem = "INSERT INTO order_items (order_id, product_id, product_name, quantity, price, total) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conn, $insertItem);
    if (!$stmt) {
        die("Order item insert prepare failed: " . mysqli_error($conn));
    }
    mysqli_stmt_bind_param(
        $stmt,
        "iisidd",
        $orderId,
        $item['product_id'],
        $item['name'],
        $item['quantity'],
        $item['price'],
        $item['total']
    );
    if (!mysqli_stmt_execute($stmt)) {
        die("Failed to insert order item: " . mysqli_error($conn));
    }
}

// ✅ Step 8: Clear cart and redirect
unset($_SESSION['cart']);
$_SESSION['order_placed'] = true;

echo "<script>
        alert('Order placed successfully!');
        window.location.href='user.php';
      </script>";
exit;
?>