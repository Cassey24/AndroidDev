<?php 
include 'connect.php';
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['add_product'])) {
    $ProductName = trim($_POST['ProductName']);
    $Brand = trim($_POST['Brand']);
    $Price = trim($_POST['Price']);
    $Quantity = trim($_POST['Quantity']);
    $Description = trim($_POST['Description']);


    $insertQuery = "INSERT INTO products (ProductName, Brand, Price, Quantity, Description) 
                    VALUES (?, ?, ?, ?, ?)";

    $stmt = mysqli_prepare($conn, $insertQuery);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "ssdis", $ProductName, $Brand, $Price, $Quantity, $Description);

        if (mysqli_stmt_execute($stmt)) {
            mysqli_stmt_close($stmt);
            header("Location: view_products.php"); 
            exit();  
        } else {
            echo "Error executing query: " . mysqli_stmt_error($stmt);
        }
    } else {
        echo "Error preparing query: " . mysqli_error($conn);
    }
}
?>