<?php 
session_start();
include 'connect.php';

if (!isset($_GET['id']) || !filter_var($_GET['id'], FILTER_VALIDATE_INT)) {
    die("Invalid Product ID.");
}

$product_id = intval($_GET['id']);

// Fetch product securely using product_id
$stmt = $conn->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows == 0) {
    die("Product Not Found.");
}

$product = $result->fetch_assoc();
$stmt->close();

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['update'])) {
    // Trim and sanitize input
    $productName = htmlspecialchars(trim($_POST['ProductName']));
    $brand = htmlspecialchars(trim($_POST['Brand']));
    $price = floatval($_POST['Price']);
    $quantity = intval($_POST['Quantity']);
    $description = htmlspecialchars(trim($_POST['Description']));

    // Secure update query using product_id
    $updateStmt = $conn->prepare("UPDATE products SET ProductName = ?, Brand = ?, Price = ?, Quantity = ?, Description = ? WHERE product_id = ?");
    $updateStmt->bind_param("ssdiss", $productName, $brand, $price, $quantity, $description, $product_id);

    if ($updateStmt->execute()) {
        echo "<script>
            alert('Product Updated');
            window.location.href = 'view_products.php';
        </script>";
        exit();
    }
    else {
        echo "Error Updating Product: " . $conn->error;
    }

    $updateStmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product</title>
    <!-- Bootstrap 5 CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #FFDAB3; /* Background color */
            color: #574964; /* Text color */
        }
        .card {
            border: none;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.1);
            background-color: #C8AAAA; /* Card background */
        }
        .card-header {
            background-color: #574964; /* Darker purple */
            color: white;
            text-align: center;
        }
        .btn-primary {
            background-color: #574964; /* Dark purple */
            border: none;
        }
        .btn-primary:hover {
            background-color: #9F8383; /* Lighter purple */
        }
        .btn-secondary {
            background-color: #9F8383; /* Light purple */
            border: none;
        }
        .btn-secondary:hover {
            background-color: #574964; /* Dark purple */
        }
        .form-label {
            font-weight: bold;
        }
    </style>
</head>
<body>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h2>Update Product</h2>
                    </div>
                    <div class="card-body">
                        <form method="post">
                            <div class="mb-3">
                                <label class="form-label">Product Name</label>
                                <input type="text" name="ProductName" class="form-control" value="<?php echo $product['ProductName']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Brand</label>
                                <input type="text" name="Brand" class="form-control" value="<?php echo $product['Brand']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Price</label>
                                <input type="number" step="0.01" name="Price" class="form-control" value="<?php echo $product['Price']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Quantity</label>
                                <input type="number" name="Quantity" class="form-control" value="<?php echo $product['Quantity']; ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <textarea name="Description" class="form-control" rows="3" required><?php echo $product['Description']; ?></textarea>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" name="update" class="btn btn-primary">Update</button>
                                <a href="view_products.php" class="btn btn-secondary">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>