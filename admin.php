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

// Fetch the count of products
$product_query = "SELECT COUNT(*) as product_count FROM products";
$product_result = mysqli_query($conn, $product_query);
$product_count = mysqli_fetch_assoc($product_result)['product_count'];

// Fetch the count of users
$user_query = "SELECT COUNT(*) as user_count FROM users";
$user_result = mysqli_query($conn, $user_query);
$user_count = mysqli_fetch_assoc($user_result)['user_count'];

// Fetch the count of orders (if applicable)
$order_query = "SELECT COUNT(*) as order_count FROM orders";  // Modify the table name if different
$order_result = mysqli_query($conn, $order_query);
$order_count = mysqli_fetch_assoc($order_result)['order_count'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>

    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(to right, #C8AAAA, #9F8383);
            text-align: center;
            margin: 0;
            padding: 0;
            color: #574964;
        }

        .welcome-message {
            font-size: 40px;
            font-weight: bold;
            margin-top: 40px;
            color: #574964;
        }

        h2 {
            margin-bottom: 30px;
            color: #574964;
        }

        .container {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 20px;
            padding: 20px;
        }

        .card {
            background-color: #FFDAB3;
            border-radius: 12px;
            padding: 25px;
            width: 260px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            transition: transform 0.3s;
        }

        .card:hover {
            transform: translateY(-5px);
        }

        .card h3 {
            margin-bottom: 10px;
            font-size: 22px;
            color: #574964;
        }

        .card p {
            font-size: 32px;
            margin-bottom: 20px;
            font-weight: bold;
            color: #574964;
        }

        .card a {
            text-decoration: none;
            color: white;
            background-color: #574964;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 16px;
            display: inline-block;
            transition: background-color 0.3s;
        }

        .card a:hover {
            background-color: #9F8383;
        }

        .dashboard {
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    height: 100vh;
}

.logout-btn {
    margin-top: 20px;
    text-decoration: none;
    color: white;
    background-color: #574964;
    padding: 10px 20px;
    border-radius: 6px;
    font-size: 16px;
    transition: background-color 0.3s;
}

.logout-btn:hover {
    background-color: #9F8383;
}


    </style>
</head>
<body>
    <div class="dashboard">
        <h1>Welcome to Admin Dashboard</h1>
    
        <div class="container">
            <div class="card">
                <a href="display_users.php">View Users</a>
            </div> 
            <div class="card">
                <a href="view_products.php">View Products</a>
            </div>
            <div class="card">
                <a href="view_orders.php">View Orders</a>
            </div>
        </div>

        <a class="logout-btn" href="index.php">Logout</a>
        
    </div>
</body>

</html>