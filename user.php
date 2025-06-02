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

// Fetch products from the database
$sql = "SELECT product_id, ProductName, Brand, Price, Quantity, Description FROM products";
$result = $conn->query($sql);

// Get cart count
$cartCount = isset($_SESSION['cart']) ? array_sum($_SESSION['cart']) : 0;
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products List</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css">

    <style>
    body {
        background-color: #FFDAB3;
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        margin: 0;
        padding: 0;
    }

    h1 {
        text-align: center;
        color: #574964;
    }

    .container {
        max-width: 1000px;
        padding: 2rem;
        margin: 0 auto;
        text-align: center;
    }

    .input-box {
        max-width: 500px;
        margin: 0 auto;
        padding: 1rem;
        border-radius: .5rem;
        background-color: #FFF;
        box-shadow: 0 4px 8px rgba(0, 0, 0, 0.2);
    }

    .input-box form .box {
        width: 80%;
        height: 40px;
        border-radius: .5rem;
        padding: 1rem;
        font-size: 16px;
        margin: 0.5rem 0;
        border: 1px solid #C8AAAA;
    }

    .btn {
        width: 100%;
        cursor: pointer;
        text-align: center;
        border-radius: 0.5rem;
        margin-top: 1rem;
        font-size: 16px;
        padding: 10px;
        background-color: #574964;
        color: white;
        border: none;
    }

    .btn:hover {
        background-color: #9F8383;
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
        background-color: #FFF6EB;
    }

    .quantity-buttons {
        display: flex;
        gap: 10px;
    }

    .quantity-btn {
        font-size: 16px;
        padding: 5px 10px;
        background-color: #FFDAB3;
        border: 1px solid #C8AAAA;
        cursor: pointer;
        border-radius: 5px;
        color: #574964;
    }

    .quantity-buttons .quantity-btn:nth-child(1) {
        background-color: #C8AAAA;
        color: white;
    }

    .quantity-buttons .quantity-btn:nth-child(3) {
        background-color: #9F8383;
        color: white;
    }

    .quantity-btn:hover {
        background-color: #9F8383;
        color: white;
    }

    .quantity-buttons input {
        width: 80px;
        text-align: center;
        border: 1px solid #C8AAAA;
        border-radius: 5px;
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
    }

    .checkout-btn:hover {
        background-color: #9F8383;
    }

    p {
        color: #574964;
    }
</style>

</head>
<body>
    <div class="container">
        <p style="font-size:50px; font-weight:bold;">
            Hello <?php echo $firstName . ' ' . $lastName; ?> :)
        </p>
        <h1>Add your Order</h1>

        <?php if (isset($_SESSION['cart_message'])): ?>
            <div style="color: green; font-size: 18px; text-align: center;">
                <?php echo $_SESSION['cart_message']; unset($_SESSION['cart_message']); ?>
            </div>
        <?php endif; ?>

        <div class="tablep">
            <form action="add_order.php" method="POST">
                <table>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Brand</th>
                        <th>Price</th>
                        <th>Description</th>
                        <th>Available Stock</th>
                        <th>Quantity</th>
                        <th>Action</th>
                    </tr>
                    <?php
                    if ($result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td>" . htmlspecialchars($row['product_id']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['ProductName']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Brand']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Price']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Description']) . "</td>";
                            echo "<td>" . htmlspecialchars($row['Quantity']) . "</td>";
                            echo "<td>
                                 <div class='quantity-buttons'>
                                    <button type='button' class='quantity-btn' onclick='decreaseQuantity(" . $row['product_id'] . ")'>
                                        <i class='fas fa-minus'></i>
                                    </button>
                                    <input type='number' value='1' name='quantity[" . $row['product_id'] . "]' class='box' id='quantity" . $row['product_id'] . "' min='1'>
                                    <button type='button' class='quantity-btn' onclick='increaseQuantity(" . $row['product_id'] . ")'>
                                        <i class='fas fa-plus'></i>
                                    </button>
                                </div>
                                  </td>";
                            echo "<td><button type='submit' name='add_to_cart' value='" . $row['product_id'] . "' class='btn'><i class='fas fa-cart-plus'></i></button></td>";
                            echo "</tr>";
                        }
                    } else {
                        echo "<tr><td colspan='8' style='text-align:center;'>No products found.</td></tr>";
                    }
                    ?>
                </table>
            </form>
        </div>
        <a href="orders.php" class="checkout-btn">Cart</a>
        <a href="users_order.php" class="checkout-btn">Order</a>
        <a href="logout.php" class="checkout-btn">Logout</a>

    </div>

    <script>
        function increaseQuantity(productId) {
            var quantityInput = document.getElementById('quantity' + productId);
            quantityInput.value = parseInt(quantityInput.value) + 1;
        }

        function decreaseQuantity(productId) {
            var quantityInput = document.getElementById('quantity' + productId);
            if (quantityInput.value > 1) {  // Ensure quantity never goes below 1
                quantityInput.value = parseInt(quantityInput.value) - 1;
            }
        }
    </script>
</body>
</html>