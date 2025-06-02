<?php
session_start();
include 'connect.php';

if (!isset($_SESSION['email'])) {
    header("Location: connect.php"); 
    exit();
}

$email = $_SESSION['email']; // Get the logged-in user's email

// Query the database to get user details
$query = mysqli_prepare($conn, "SELECT firstName, lastName FROM users WHERE email = ?");
mysqli_stmt_bind_param($query, "s", $email);
mysqli_stmt_execute($query);
$result = mysqli_stmt_get_result($query);

$user = mysqli_fetch_assoc($result);

if (!$user) {
    echo "Error: User not found.";
    exit();
}

$firstName = htmlspecialchars($user['firstName']); // Prevent XSS
$lastName = htmlspecialchars($user['lastName']);

mysqli_stmt_close($query); // Close the prepared statement
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cart</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">
    <style>
    body {
        background-color: #FFDAB3;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
        color: #574964;
    }

    .container {
        max-width: 1000px;
        padding: 2rem;
        margin: 0 auto;
        text-align: center;
    }

    h1 {
        color: #574964;
        font-size: 36px;
        margin-bottom: 10px;
    }

    .tablep {
        width: 100%;
        margin: 30px auto;
        text-align: center;
    }

    .tablep table {
        width: 100%;
        border-collapse: collapse;
        background: #FFF;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
        border-radius: 10px;
        overflow: hidden;
        font-size: 18px;
    }

    th, td {
        padding: 20px;
        border: 1px solid #ddd;
        text-align: left;
    }

    th {
        background-color: #574964;
        color: white;
    }

    tr:nth-child(even) {
        background-color: #FFF1E0;
    }

    .checkout-btn {
        padding: 10px 20px;
        color: white;
        background-color: #574964;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        text-decoration: none;
        font-size: 16px;
        margin: 10px 5px;
        display: inline-block;
    }

    .checkout-btn:hover {
        background-color: #9F8383;
    }

    .message {
        color: #28a745;
        font-size: 18px;
        margin-bottom: 20px;
    }

    p {
        font-size: 20px;
        margin-bottom: 10px;
    }
</style>

</head>
<body>
    <div class="container">
        <h1>Orders</h1>
    
        <?php
        // Check if a message is passed in the URL (e.g., item removed)
        if (isset($_GET['message']) && $_GET['message'] === 'removed') {
            echo '<p class="message">Item has been successfully removed from your cart.</p>';
        }

        // Check if the cart session variable exists and is not empty
        if (isset($_SESSION['cart']) && !empty($_SESSION['cart'])) {
            // Query the database for product details in the cart
            $cartItems = $_SESSION['cart'];
            echo '<div class="tablep">';
            echo '<table>';
            echo '<tr><th>Product ID</th><th>Product Name</th><th>Brand</th><th>Price</th><th>Quantity</th><th>Total</th><th>Action</th></tr>';

            $total = 0;
            foreach ($cartItems as $productId => $quantity) {
                // Get product details
                $sql = "SELECT ProductName, Brand, Price FROM products WHERE product_id = ?";
                $stmt = mysqli_prepare($conn, $sql);
                mysqli_stmt_bind_param($stmt, "i", $productId);
                mysqli_stmt_execute($stmt);
                $result = mysqli_stmt_get_result($stmt);
                $product = mysqli_fetch_assoc($result);
            
                if ($product) {
                    $productName = htmlspecialchars($product['ProductName']);
                    $brand = htmlspecialchars($product['Brand']);
                    $price = (float) htmlspecialchars($product['Price']); 
                    $totalPrice = $price * $quantity;
                    $total += $totalPrice;
            
                    echo '<tr>';
                    echo '<td>' . $productId . '</td>';
                    echo '<td>' . $productName . '</td>';
                    echo '<td>' . $brand . '</td>';
                    echo '<td>' . $price . '</td>';
                    echo '<td>' . $quantity . '</td>';
                    echo '<td>' . $totalPrice . '</td>';
                    echo '<td><a href="remove.php?product_id=' . $productId . '" class="checkout-btn"><i class="fas fa-trash-alt"></i></a></td>';
                    echo '</tr>';
                }
            }

            echo '<tr><td colspan="4" style="text-align:right;"><strong>Total:</strong></td><td>' . $total . '</td></tr>';
            echo '</table>';
            echo '</div>';
        } else {
            echo "<p>Your cart is empty.</p>";
        }
        ?>
        <a href="user.php" class="checkout-btn">Back to Home</a>
        <a href="checkout.php" class="checkout-btn">Proceed to Checkout</a>
    </div>
</body>
</html>