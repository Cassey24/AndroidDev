<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: connect.php");
    exit();
}

$email = $_SESSION['email']; // Get the logged-in user's email

// Query the database to get user details, including address and contact no
$query = mysqli_prepare($conn, "SELECT firstName, lastName, email, address, contact_no FROM users WHERE email = ?");
mysqli_stmt_bind_param($query, "s", $email);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Error: User not found.";
    exit();
}

$firstName = htmlspecialchars($user['firstName']);
$lastName = htmlspecialchars($user['lastName']);
$email = htmlspecialchars($user['email']);
$address = htmlspecialchars($user['address']);
$contactNo = htmlspecialchars($user['contact_no']);

mysqli_stmt_close($query); // Close the prepared statement

// Fetch the cart items
$cartItems = isset($_SESSION['cart']) ? $_SESSION['cart'] : [];
$orders = [];
$totalAmount = 0;

if (!empty($cartItems)) {
    // Query database for product details in the cart
    foreach ($cartItems as $productId => $quantity) {
        $stmt = $conn->prepare("SELECT ProductName, Brand, Price FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($row = $result->fetch_assoc()) {
            $productName = htmlspecialchars($row['ProductName']);
            $brand = htmlspecialchars($row['Brand']);
            $price = (float) htmlspecialchars($row['Price']);
            $totalPrice = $price * $quantity;
            $totalAmount += $totalPrice;
            
            $orders[] = [
                'Product_id' => $productId,
                'name' => $productName,
                'brand' => $brand,
                'price' => $price,
                'quantity' => $quantity,
                'total' => $totalPrice
            ];
        }
        $stmt->close();
    }
} else {
    echo "<script>alert('Your cart is empty.'); window.location.href='orders.php';</script>";
    exit;
}

// Update user details (billing info) after form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_details'])) {
    $newFirstName = mysqli_real_escape_string($conn, $_POST['firstName']);
    $newLastName = mysqli_real_escape_string($conn, $_POST['lastName']);
    $newAddress = mysqli_real_escape_string($conn, $_POST['address']);
    $newContactNo = mysqli_real_escape_string($conn, $_POST['contact_no']);

    // Update the user details in the database
    $updateQuery = mysqli_prepare($conn, "UPDATE users SET firstName = ?, lastName = ?, address = ?, contact_no = ? WHERE email = ?");
    mysqli_stmt_bind_param($updateQuery, "sssss", $newFirstName, $newLastName, $newAddress, $newContactNo, $email);
    mysqli_stmt_execute($updateQuery);
    mysqli_stmt_close($updateQuery);

    // Refresh the page after updating
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle place order button from this page - redirecting to place_order.php
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['place_order'])) {
    // Store payment method in session to be used in place_order.php
    $_SESSION['payment_method'] = $_POST['payment_method'];
    
    // Make sure the orders session variable is populated with cart items
    $_SESSION['orders'] = $orders;
    $_SESSION['total_amount'] = $totalAmount;
    
    // Redirect will now happen via the form action to place_order.php
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Checkout</title>
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
    .confirm-btn {
        padding: 10px 20px;
        background-color: #574964;
        color: white;
        border: none;
        cursor: pointer;
        border-radius: 5px;
        transition: background-color 0.3s ease;
    }
    .confirm-btn:hover {
        background-color: #9F8383;
    }
    input[type="text"] {
        padding: 8px;
        width: 80%;
        margin-bottom: 10px;
        border: 1px solid #C8AAAA;
        border-radius: 5px;
    }
    label {
        color: #574964;
        font-weight: bold;
    }
    .payment-method {
        margin: 20px 0;
        text-align: left;
    }
    .payment-method label {
        font-weight: normal;
    }
    a {
        display: inline-block;
        margin-top: 15px;
        color: #574964;
        text-decoration: none;
        font-weight: bold;
    }
    a:hover {
        text-decoration: underline;
    }
</style>

</head>
<body>
    <div class="container">
        <h1>Order Confirmation</h1>
        <p>Hello, <?php echo $firstName . ' ' . $lastName; ?> :)</p>

        <!-- Edit user details form -->
        <h2>Update Your Details</h2>
        <form action="" method="POST">
            <label for="firstName">First Name:</label><br>
            <input type="text" name="firstName" value="<?php echo $firstName; ?>"><br>

            <label for="lastName">Last Name:</label><br>
            <input type="text" name="lastName" value="<?php echo $lastName; ?>"><br>

            <label for="address">Address:</label><br>
            <input type="text" name="address" value="<?php echo $address; ?>"><br>

            <label for="contact_no">Contact Number:</label><br>
            <input type="text" name="contact_no" value="<?php echo $contactNo; ?>"><br>

            <button type="submit" name="update_details" class="confirm-btn">Save Changes</button>
        </form>

        <table>
            <tr>
                <th>Product id</th>
                <th>Product Name</th>
                <th>Brand</th>
                <th>Price</th>
                <th>Quantity</th>
                <th>Total</th>
            </tr>
            <?php foreach ($orders as $order): ?>
            <tr>
                <td><?php echo htmlspecialchars($order['Product_id']); ?></td>
                <td><?php echo htmlspecialchars($order['name']); ?></td>
                <td><?php echo htmlspecialchars($order['brand']); ?></td>
                <td><?php echo htmlspecialchars($order['price']); ?></td>
                <td><?php echo htmlspecialchars($order['quantity']); ?></td>
                <td><?php echo htmlspecialchars($order['total']); ?></td>
            </tr>
            <?php endforeach; ?>
        </table>

        <h2>Total Amount: ₱<?php echo number_format($totalAmount, 2); ?></h2>

        <form action="place_order.php" method="POST">
            <div class="payment-method">
                <label for="payment-method">Choose Payment Method:</label><br>
                <input type="radio" name="payment_method" value="COD" id="cod" checked>
                <label for="cod">Cash on Delivery (COD)</label><br>
                <input type="radio" name="payment_method" value="PayAtStore" id="payAtStore">
                <label for="payAtStore">Pay at Store (For Pickup Orders)</label><br>
            </div>
            <button type="submit" name="place_order" class="confirm-btn">Place Order</button>
        </form>

        <a href="orders.php">Back to Cart</a>
    </div>
</body>
</html>